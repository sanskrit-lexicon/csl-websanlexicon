<?php
///afs/rrz.uni-koeln.de/vol/www/projekt/sanskrit-lexicon/http/docs/scans/MWEScan/2013/web/webtc2/query_gather.php
include('../utilities/transcoder.php');
require_once('../webtc/dal_sqlite.php');
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
$data = $_POST['data'];
$utilchoice = $_POST['utilchoice'];
$filter0 = $_POST['filter'];
$filter = transcoder_standardize_filter($filter0);
$data1 = preg_replace('/<\/key1>/','',$data);
$keyar = preg_split('/<key1>/',$data1);
$matches=array();
$nmatches=0;
$dbg = FALSE;

foreach($keyar as $key) {
 if ($dbg) {
  echo "<p>gather_query: key = $key</p>\n";
  //$key = preg_replace('|[^a-zA-Z -]|','',$key);
 }
 $results = dal_mwe1($key);
 foreach($results as $line) {
  list($key1,$lnum1,$data2) = $line;
  $matches[$nmatches]=trim($data2);
  $nmatches++;
 }
 if (count($results) == 0) {
  $data1 = "<Hx><h><key1>$key1</key1></h><body>" .
		"no data for key1=$key1</body><tail></tail></Hx>";
  $matches[$nmatches]=trim($data1);
  $nmatches++;
 }
}
$table1 = join('\n',$matches);
print $table1;

exit;
?>
