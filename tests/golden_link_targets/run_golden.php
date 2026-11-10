<?php
/* run_golden.php — H7948 golden parity runner for the basicadjust.php
 * abbreviation->href link-target refactor (ls_callback_pwg_href).
 *
 * Usage: php run_golden.php old|new
 *   old — loads basicadjust_pre_H7948.php in THIS directory: a byte-exact
 *         copy of v02/makotemplates/web/webtc/basicadjust.php as of base
 *         commit d090a1076bcd019e595e1cb9f70571f047d6ce04 (verify with
 *         run_golden.sh --verify-fixture).
 *   new — loads the refactored v02/makotemplates/web/webtc/basicadjust.php
 *         (+ webtc/link_targets.php) from this checkout.
 *
 * Each corpus case is run through BasicAdjust::ls_callback_pwg_href via
 * ReflectionClass::newInstanceWithoutConstructor (the href chain touches
 * only the public $dict / $key properties — no Dal, no database), and one
 * "id<TAB>result" line is printed. The two runs are validated by
 * run_golden.sh via check_divergence.php: byte-identical everywhere except
 * the declared roman rows (intentional_divergences.tsv, H7962).
 *
 * Usage: php run_golden.php old|new [corpus.tsv]  (default corpus.tsv;
 * pass a gen_fuzz.php dump for the breadth layer).
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "cli only\n"); exit(2); }
$mode = isset($argv[1]) ? $argv[1] : '';
if ($mode !== 'old' && $mode !== 'new') {
  fwrite(STDERR, "usage: php run_golden.php old|new [corpus.tsv]\n");
  exit(2);
}
$corpus_file = isset($argv[2]) ? $argv[2] : __DIR__ . '/corpus.tsv';
$here = __DIR__;
$repo = realpath($here . '/../..');
$webtc = $repo . '/v02/makotemplates/web/webtc';
// stubs/ FIRST: the real dal.php pulls the Mako template dictinfo.php (not
// loadable raw PHP); the href chain never touches Dal (see stubs/dal.php).
set_include_path($here . '/stubs' . PATH_SEPARATOR . $webtc . PATH_SEPARATOR . get_include_path());
if ($mode === 'old') {
  require $here . '/basicadjust_pre_H7948.php';
} else {
  require $webtc . '/basicadjust.php';
}
$rc = new ReflectionClass('BasicAdjust');
$corpus = file($corpus_file, FILE_IGNORE_NEW_LINES);
if ($corpus === false) { fwrite(STDERR, "$corpus_file unreadable\n"); exit(2); }
foreach ($corpus as $lineno => $line) {
  if ($line === '' || $line[0] === '#') continue;
  $parts = explode("\t", $line);
  if (count($parts) !== 5) {
    fwrite(STDERR, 'corpus.tsv line ' . ($lineno + 1) . ": want 5 tab-separated fields\n");
    exit(2);
  }
  list($id, $dict, $key, $code, $data) = $parts;
  $ba = $rc->newInstanceWithoutConstructor();
  $ba->dict = $dict;
  $ba->key = $key;
  $href = $ba->ls_callback_pwg_href($code, $data);
  echo $id . "\t" . ($href === null ? '(null)' : $href) . "\n";
}
