<?php
//sanskrit/mwquery/mwquery_monierMulti.php
//ejf 2010-01-28
//ejf Oct 1, 2012  Adapated for sanskrit1d
$dbg=false;
dbgprint($dbg,"query_multi.php: BEGIN\n");
include("../utilities/transcoder.php");
dbgprint($dbg,"query_multi.php: #before loading disp.php\n");
include("../webtc/disp.php");
$dbg=false;
dbgprint($dbg,"query_multi.php: #after loading disp.php\n");
$data = $_POST['data'];
$utilchoice = $_POST['utilchoice'];
$accent = $_POST['accent'];
if (!$accent) {$accent='no';}
$filter0 = $_POST['filter'];
$filter = transcoder_standardize_filter($filter0);


//****************
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
//****************
dbgprint($dbg,"query_multi.php: #1\n");
dbgprint($dbg,"data=\n$data\n");
$nxmlnew=0;
$key;
$prevkey="";
$n = -1;
$matches = array();
$noLit="off";
$noParen="off";
$ntab = 0;
// remove escaped \".  For some reason, these are being inserted by
//  the 'print' statement in mwquery_gatherMW.php
$data = preg_replace('/\\\\"/','"',$data);
// the '/s' is to skip over \n.
preg_match_all('/<H.*?>.*?<key1>(.*?)<\/key1>.*?<\/H.*?>/s',$data,$matchall);
$matches0=$matchall[0];
$matches1=$matchall[1];
$nmatches0=count($matches0);
for ($imatch=0;$imatch<$nmatches0;$imatch++) {
 $xmlnew = $matches0[$imatch];
 $xmlnew = trim($xmlnew);
 $key = $matches1[$imatch];
 if ($prevkey == '') {
  // first record
  $prevkey = $key;
  $n=-1;
 }
 if ($key == $prevkey) {
  $n++;
  $matches[$n]=$xmlnew;
 }else {
  $ntab++;
  dbgprint($dbg,"query_multi.php call print_table #1\n");
  print_table($filter,$prevkey,$ntab,$n+1,$matches,$accent);
  dbgprint($dbg,"query_multi.php returned from print_table #1\n");
  $prevkey = $key;
  $n=0;
  $matches=array();
  $matches[$n]=$xmlnew;
 }
}
if ($n != -1) {
 $ntab++;
 dbgprint($dbg,"query_multi.php call print_table #2\n");
 print_table($filter,$prevkey,$ntab,$n+1,$matches,$accent);
 dbgprint($dbg,"query_multi.php returned from print_table #2\n");
}
exit;
function print_table($filter,$key,$ntab,$nmatchesin,$matchesin,$accent) {
 $dbg=false;
 $table0 = "<span class='key' id='record_$ntab' /></span>\n";
 $matches=array();
 for($i=0;$i<$nmatchesin;$i++) {
  $matches[$i]=$matchesin[$i];
 }
 basicDisplaySetAccent($accent);
 dbgprint($dbg,"query_multi.php.print_table: Before basicDisplay\n");
 $table = basicDisplay($key,$matches,$filter);
 dbgprint($dbg,"query_multi.php.print_table: After basicDisplay\n");
 $table1 = transcoder_processElements($table,"slp1",$filter,"SA");
 echo $table0;
 echo $table1;
}
function dbgprint($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbg_query_multi.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}
?>
