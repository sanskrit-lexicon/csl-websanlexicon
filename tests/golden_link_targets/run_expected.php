<?php
/* run_expected.php — H7962 expected-value runner for the roman-rule fix
 * (tests/golden_link_targets/roman_expected.tsv). Each row pins what the NEW
 * chain must return for a roman input, and that the OLD chain returned (null)
 * (the historic |^-delimited pattern never matched — "Unknown modifier 'T'").
 *
 * Usage: php run_expected.php                 — full check (spawns 2 children)
 *        php run_expected.php --side old|new  — internal: one side, id<TAB>value
 * Exit 0 = every row: old == (null) and new == expected href.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "cli only\n"); exit(2); }

$here = __DIR__;
$repo = realpath($here . '/../..');
$webtc = $repo . '/v02/makotemplates/web/webtc';
$expected_file = $here . '/roman_expected.tsv';

function load_side($mode, $here, $webtc) {
  set_include_path($here . '/stubs' . PATH_SEPARATOR . $webtc . PATH_SEPARATOR . get_include_path());
  if ($mode === 'old') {
    require $here . '/basicadjust_pre_H7948.php';
  } else {
    require $webtc . '/basicadjust.php';
  }
}

if (isset($argv[1]) && $argv[1] === '--side') {
  // internal mode: run one side over roman_expected.tsv, print id<TAB>value
  $mode = isset($argv[2]) ? $argv[2] : '';
  if ($mode !== 'old' && $mode !== 'new') { fwrite(STDERR, "usage: php run_expected.php --side old|new\n"); exit(2); }
  load_side($mode, $here, $webtc);
  $rc = new ReflectionClass('BasicAdjust');
  foreach (file($expected_file, FILE_IGNORE_NEW_LINES) as $lineno => $line) {
    if ($line === '' || $line[0] === '#') continue;
    $parts = explode("\t", $line);
    if (count($parts) !== 6) {
      fwrite(STDERR, 'roman_expected.tsv line ' . ($lineno + 1) . ": want 6 tab-separated fields\n");
      exit(2);
    }
    list($id, $dict, $key, $code, $data, $expected) = $parts;
    $ba = $rc->newInstanceWithoutConstructor();
    $ba->dict = $dict;
    $ba->key = $key;
    $href = $ba->ls_callback_pwg_href($code, $data);
    echo $id . "\t" . ($href === null ? '(null)' : $href) . "\n";
  }
  exit(0);
}

// full check: one child per side (both define class BasicAdjust)
function run_side($mode, $here) {
  $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($here . '/run_expected.php') . ' --side ' . $mode;
  $out = shell_exec($cmd);
  if ($out === null) { fwrite(STDERR, "cannot spawn --side $mode child\n"); exit(2); }
  $map = array();
  foreach (explode("\n", trim($out)) as $line) {
    if ($line === '') continue;
    $parts = explode("\t", $line);
    if (count($parts) !== 2) { fwrite(STDERR, "bad child line: $line\n"); exit(2); }
    $map[$parts[0]] = $parts[1];
  }
  return $map;
}

$old_map = run_side('old', $here);
$new_map = run_side('new', $here);
$fail = 0; $rows = 0;
foreach (file($expected_file, FILE_IGNORE_NEW_LINES) as $lineno => $line) {
  if ($line === '' || $line[0] === '#') continue;
  $parts = explode("\t", $line);
  if (count($parts) !== 6) {
    fwrite(STDERR, 'roman_expected.tsv line ' . ($lineno + 1) . ": want 6 fields\n");
    exit(2);
  }
  list($id, $dict, $key, $code, $data, $expected) = $parts;
  $rows++;
  if (!isset($old_map[$id]) || !isset($new_map[$id])) {
    echo "FAIL $id: missing from child output\n"; $fail = 1; continue;
  }
  $ok_old = ($old_map[$id] === '(null)');
  $ok_new = ($new_map[$id] === $expected);
  if ($ok_old && $ok_new) {
    echo "PASS $id: old=(null) new=$expected\n";
  } else {
    if (!$ok_old) { echo "FAIL $id: old={$old_map[$id]}, want (null)\n"; }
    if (!$ok_new) { echo "FAIL $id: new={$new_map[$id]}, want $expected\n"; }
    $fail = 1;
  }
}
echo "roman_expected rows checked: $rows\n";
if ($fail) { echo "EXPECTED GATE FAIL\n"; exit(1); }
echo "EXPECTED GATE OK — old (null) everywhere, new == pinned hrefs (I–L coverage)\n";
exit(0);
