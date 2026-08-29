<?php
// run_parity.php — H3633 (G3) webtc2 search-index parity + timing harness.
//
// Runs a query matrix against ONE generated dictionary tree
// (<dictdir>/web/webtc2) using a selectable querymodel.php variant, and
// emits NDJSON: one line per (query, page) with the exact answer set
// (keys in order, matchwords, lastLnum) plus wall-clock ms.
//
// Usage:
//   php run_parity.php <dictdir> <queries.json> <querymodel.php> [--maxrep=N] [--hide-index]
//
//   --hide-index  temporarily rename query_dump.sqlite3 away so the same
//                 querymodel runs its legacy flat-file fallback path.
//
// Comparison of the NDJSON across phases (old-scan vs new-index vs
// new-fallback) is done by compare_parity.py / jq in the driver.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if ($argc < 4) {
 fwrite(STDERR, "usage: php run_parity.php <dictdir> <queries.json> <querymodel.php> [--maxrep=N] [--hide-index]\n");
 exit(2);
}
$dictdir = rtrim($argv[1], '/');
$queriesfile = $argv[2];
$qmpath = $argv[3];
$maxrep = 3;
$hideindex = false;
for ($i = 4; $i < $argc; $i++) {
 if (preg_match('/^--maxrep=(\d+)$/', $argv[$i], $m)) { $maxrep = intval($m[1]); }
 else if ($argv[$i] == '--hide-index') { $hideindex = true; }
}

$webtc2 = $dictdir . '/web/webtc2';
$indexfile = $webtc2 . '/query_dump.sqlite3';
$indexbak = $indexfile . '.parity-bak';
if ($hideindex) {
 if (!file_exists($indexbak)) { rename($indexfile, $indexbak); }
}
$restore = function () use ($indexfile, $indexbak, $hideindex) {
 if ($hideindex && file_exists($indexbak)) { rename($indexbak, $indexfile); }
};

chdir($webtc2);
require_once('../webtc/dictcode.php');
require_once($qmpath); // defines class QueryModel (absolute/relative path arg)
require_once('queryparm.php');

$queries = json_decode(file_get_contents($queriesfile), true);
if (!is_array($queries)) { fwrite(STDERR, "bad queries.json\n"); exit(2); }

function run_one($q) {
 $_REQUEST = array();
 $_REQUEST['dictionary'] = 'query_dump.txt';
 $_REQUEST['lastLnum'] = isset($q['lastLnum']) ? $q['lastLnum'] : 0;
 $_REQUEST['max'] = isset($q['max']) ? $q['max'] : 100;
 $_REQUEST['transLit'] = isset($q['transLit']) ? $q['transLit'] : 'slp1';
 $_REQUEST['sword'] = isset($q['sword']) ? $q['sword'] : '';
 $_REQUEST['sregexp'] = isset($q['sregexp']) ? $q['sregexp'] : 'exact';
 $_REQUEST['swordhw'] = isset($q['swordhw']) ? $q['swordhw'] : 'hwonly';
 $_REQUEST['word'] = isset($q['word']) ? $q['word'] : '';
 $_REQUEST['regexp'] = isset($q['regexp']) ? $q['regexp'] : 'exact';
 $_REQUEST['scase'] = '';
 $_REQUEST['outopt'] = 'outopt4';
 $getParms = new QueryParm($GLOBALS['dictcode']);
 $t0 = hrtime(true);
 $model = new QueryModel($getParms);
 $ms = (hrtime(true) - $t0) / 1e6;
 $keys = array();
 $mws = array();
 foreach ($model->querymatches as $m) {
  $keys[] = $m['key'];
  $mws[] = $m['matchword'];
 }
 return array(
  'keys' => $keys,
  'matchwords' => $mws,
  'lastLnum' => $model->lastLnum,
  'status' => $model->status,
  'ms' => round($ms, 3),
 );
}

$out = array();
foreach ($queries as $q) {
 $qid = isset($q['id']) ? $q['id'] : json_encode($q);
 $best = null;
 for ($rep = 0; $rep < $maxrep; $rep++) {
  $r = run_one($q);
  if ($best === null || $r['ms'] < $best['ms']) { $best = $r; }
 }
 $rec = array(
  'id' => $qid,
  'page' => 1,
  'n' => count($best['keys']),
  'lastLnum' => $best['lastLnum'],
  'status' => $best['status'],
  'ms' => $best['ms'],
  'result' => $best['keys'],
  'matchwords' => $best['matchwords'],
 );
 $out[] = $rec;
 // page 2 (pagination parity), only when a follow-up page is advertised
 if (is_numeric($best['lastLnum']) && $best['lastLnum'] > 0 && $best['lastLnum'] != -1) {
  $q2 = $q;
  $q2['lastLnum'] = $best['lastLnum'];
  $best2 = null;
  for ($rep = 0; $rep < $maxrep; $rep++) {
   $r2 = run_one($q2);
   if ($best2 === null || $r2['ms'] < $best2['ms']) { $best2 = $r2; }
  }
  $out[] = array(
   'id' => $qid,
   'page' => 2,
   'n' => count($best2['keys']),
   'lastLnum' => $best2['lastLnum'],
   'status' => $best2['status'],
   'ms' => $best2['ms'],
   'result' => $best2['keys'],
   'matchwords' => $best2['matchwords'],
  );
 }
}
$restore();
foreach ($out as $rec) {
 echo json_encode($rec), "\n";
}
