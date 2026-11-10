<?php
/* check_divergence.php — H7962 divergence gate for the golden parity run
 * (tests/golden_link_targets). Old-vs-new parity is no longer "diff empty":
 * the ṚV. PRĀT./ṚV. PRĀTIŚ. roman-rule fix (H7962, href_rvprat_roman
 * delimiter-only fix) deliberately changes exactly the roman rows, and those
 * changes are declared in intentional_divergences.tsv. This checker proves:
 *
 *   1. the ids that differ old-vs-new (named + fuzz corpora) are EXACTLY the
 *      declared ids — nothing more (byte-identical everywhere else), nothing
 *      less (no declared row silently re-converged);
 *   2. every declared row differs from (null) to its pinned new value;
 *   3. every pinned new value equals an INDEPENDENT oracle re-derivation
 *      (romanToInt on the regex-captured numeral -> rvps/app2/?<ipage>);
 *   4. BIJECTION: a corpus row is declared iff its data matches the fixed
 *      rule's match surface ^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+).
 *
 * Usage: php check_divergence.php <named_old> <named_new> <named_corpus.tsv> \
 *                                 <fuzz_old> <fuzz_new> <fuzz_corpus.tsv> \
 *                                 <intentional_divergences.tsv>
 * Exit 0 = ONLY the declared roman-row divergences old-vs-new.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "cli only\n"); exit(2); }
if ($argc !== 8) {
  fwrite(STDERR, "usage: php check_divergence.php named_old named_new named_corpus fuzz_old fuzz_new fuzz_corpus divergences.tsv\n");
  exit(2);
}
list(, $named_old_f, $named_new_f, $named_corpus_f, $fuzz_old_f, $fuzz_new_f, $fuzz_corpus_f, $div_f) = $argv;

function read_outputs($file) {
  $map = array();
  foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
    if ($line === '') continue;
    $parts = explode("\t", $line);
    if (count($parts) !== 2) { fwrite(STDERR, "$file: bad output line: $line\n"); exit(2); }
    if (isset($map[$parts[0]])) { fwrite(STDERR, "$file: duplicate id {$parts[0]}\n"); exit(2); }
    $map[$parts[0]] = $parts[1];
  }
  return $map;
}
function read_corpus($file) {
  $map = array();
  foreach (file($file, FILE_IGNORE_NEW_LINES) as $lineno => $line) {
    if ($line === '' || $line[0] === '#') continue;
    $parts = explode("\t", $line);
    if (count($parts) !== 5) { fwrite(STDERR, "$file line " . ($lineno + 1) . ": want 5 fields\n"); exit(2); }
    if (isset($map[$parts[0]])) { fwrite(STDERR, "$file: duplicate id {$parts[0]}\n"); exit(2); }
    $map[$parts[0]] = $parts[4]; // data
  }
  return $map;
}
/* Independent oracle: the fixed rule's match surface + romanToInt + the
 * rvps/app2 href template, re-implemented here (NOT via the production chain). */
function oracle($data) {
  if (!preg_match('~^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+)~u', $data, $m)) return null;
  $s = strtoupper($m[2]);
  $romans = array('I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000);
  $result = 0;
  for ($i = 0; $i < strlen($s); $i++) {
    $ri = $romans[$s[$i]];
    if ($i > 0 && $ri > $romans[$s[$i - 1]]) { $result += $ri - 2 * $romans[$s[$i - 1]]; }
    else { $result += $ri; }
  }
  return "https://sanskrit-lexicon-scans.github.io/rvps/app2/?$result";
}

$fail = 0;
$pairs = array(
  'named' => array($named_old_f, $named_new_f, $named_corpus_f),
  'fuzz'  => array($fuzz_old_f, $fuzz_new_f, $fuzz_corpus_f),
);
$changed = array();      // id => [old, new] where they differ
$corpus_data = array();  // id => data (both corpora)
$corpus_counts = array();
foreach ($pairs as $label => $p) {
  list($old_f, $new_f, $corpus_f) = $p;
  $old = read_outputs($old_f);
  $new = read_outputs($new_f);
  $corpus = read_corpus($corpus_f);
  $corpus_counts[$label] = count($corpus);
  // every corpus row must have exactly one output line on both sides
  foreach (array('old' => $old, 'new' => $new) as $side => $map) {
    if (count($map) !== count($corpus)) {
      fwrite(STDERR, "FAIL($label/$side): output lines (" . count($map) . ") != corpus rows (" . count($corpus) . ")\n");
      $fail = 1;
    }
    foreach ($corpus as $id => $unused) {
      if (!isset($map[$id])) { fwrite(STDERR, "FAIL($label/$side): corpus id $id missing from output\n"); $fail = 1; }
    }
    foreach ($map as $id => $unused) {
      if (!isset($corpus[$id])) { fwrite(STDERR, "FAIL($label/$side): output id $id not in corpus\n"); $fail = 1; }
    }
  }
  foreach ($corpus as $id => $data) {
    if (isset($corpus_data[$id])) { fwrite(STDERR, "FAIL: id $id in both corpora\n"); $fail = 1; }
    $corpus_data[$id] = $data;
    if ($old[$id] !== $new[$id]) { $changed[$id] = array($old[$id], $new[$id]); }
  }
}

// declared list
$declared = array();
foreach (file($div_f, FILE_IGNORE_NEW_LINES) as $lineno => $line) {
  if ($line === '' || $line[0] === '#') continue;
  $parts = explode("\t", $line);
  if (count($parts) !== 3) { fwrite(STDERR, "$div_f line " . ($lineno + 1) . ": want 3 fields (id<TAB>old<TAB>new)\n"); exit(2); }
  if (isset($declared[$parts[0]])) { fwrite(STDERR, "$div_f: duplicate id {$parts[0]}\n"); exit(2); }
  $declared[$parts[0]] = array($parts[1], $parts[2]);
}

// 1. set equality: changed == declared
$extra = array_diff(array_keys($changed), array_keys($declared));
$missing = array_diff(array_keys($declared), array_keys($changed));
foreach ($extra as $id) {
  fwrite(STDERR, "FAIL: undeclared divergence $id: {$changed[$id][0]} -> {$changed[$id][1]}\n");
  $fail = 1;
}
foreach ($missing as $id) {
  fwrite(STDERR, "FAIL: declared id $id did not diverge (declared {$declared[$id][0]} -> {$declared[$id][1]})\n");
  $fail = 1;
}

// 2+3. per declared row: old==(null), new==pinned, pinned==oracle(data)
foreach ($declared as $id => $dv) {
  list($d_old, $d_new) = $dv;
  if (!isset($corpus_data[$id])) {
    fwrite(STDERR, "FAIL: declared id $id not present in any corpus (stale declaration)\n");
    $fail = 1;
    continue;
  }
  if ($d_old !== '(null)') {
    fwrite(STDERR, "FAIL: $id declared old is '$d_old', want '(null)' (the historic rule never matched)\n");
    $fail = 1;
  }
  if (isset($changed[$id])) {
    if ($changed[$id][0] !== $d_old) {
      fwrite(STDERR, "FAIL: $id old is {$changed[$id][0]}, want $d_old\n"); $fail = 1;
    }
    if ($changed[$id][1] !== $d_new) {
      fwrite(STDERR, "FAIL: $id new is {$changed[$id][1]}, want $d_new\n"); $fail = 1;
    }
  }
  $orc = oracle($corpus_data[$id]);
  if ($orc === null) {
    fwrite(STDERR, "FAIL: $id data does not match the fixed rule's match surface: {$corpus_data[$id]}\n");
    $fail = 1;
  } elseif ($orc !== $d_new) {
    fwrite(STDERR, "FAIL: $id oracle says $orc, declared $d_new\n");
    $fail = 1;
  }
}

// 4. bijection: declared iff data matches the roman match surface
foreach ($corpus_data as $id => $data) {
  $is_roman = (oracle($data) !== null);
  $is_declared = isset($declared[$id]);
  if ($is_roman && !$is_declared) {
    fwrite(STDERR, "FAIL: $id matches the roman rule but is not declared (would diverge silently)\n");
    $fail = 1;
  }
  if (!$is_roman && $is_declared) {
    fwrite(STDERR, "FAIL: $id is declared but its data does not match the roman rule\n");
    $fail = 1;
  }
}

echo "corpus rows: named={$corpus_counts['named']} fuzz={$corpus_counts['fuzz']}\n";
echo "declared divergences: " . count($declared) . " (changed: " . count($changed) . ")\n";
echo "intentional divergence list (the review artifact):\n";
foreach ($declared as $id => $dv) {
  echo "  $id: {$dv[0]} -> {$dv[1]}\n";
}
if ($fail) {
  echo "DIVERGENCE GATE FAIL\n";
  exit(1);
}
echo "DIVERGENCE GATE OK — only the declared roman rows differ old-vs-new; every other named + fuzz case byte-identical\n";
exit(0);
