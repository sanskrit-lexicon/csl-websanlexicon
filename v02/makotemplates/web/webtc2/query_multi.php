<?php
//sanskrit/mwquery/mwquery_monierMulti.php
//ejf 2010-01-28
//ejf Oct 1, 2012  Adapated for sanskrit1d
// 06-30-2018
// 09-07-2018
//require_once("../webtc/dictcode.php");
//$dict = $dictcode;
require_once("../webtc/parm.php");
$getParms = new Parm();  
$dict = $getParms->$dict;
$filter = $getParms->filter;
require_once('../webtc/basicdisplay.php'); // BasicDisplay
$data = $_POST['data'];
if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
 header("Access-Control-Allow-Origin: *");
}
$meta = '<meta charset="UTF-8">'; 
echo $meta;  // Why?


$nxmlnew=0;
$key;
$prevkey="";
$n = -1;
$matches = array();
$ntab = 0;
// remove escaped \".  For some reason, these are being inserted by
//  the 'print' statement in mwquery_gatherMW.php
//  don't do this 06-30-2018
//$data = preg_replace('/\\\\"/','"',$data);
// the '/s' is to skip over \n.

$lines = explode("\n",$data);
$dbg=false;
dbgprint($dbg,"query_multi. # lines=" . count($lines) . "\n");
$nx=0;
foreach($lines as $line) {
 dbgprint($dbg," nx=$nx, line=$line\n");
 $nx++;
 if (! preg_match("/^key=(.*?),(.*)$/",$line,$matches0)) {
  // some internal problem
  continue;
 }
 $key = $matches0[1];
 $xmlnew = $matches0[2];
 $xmlnew = trim($xmlnew);
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
  print_table($filter,$prevkey,$ntab,$n+1,$matches,$dict);
  $prevkey = $key;
  $n=0;
  $matches=array();
  $matches[$n]=$xmlnew;
 }
}
if ($n != -1) {
 $ntab++;
 print_table($filter,$prevkey,$ntab,$n+1,$matches,$dict);
}
exit;
function print_table($filter,$key,$ntab,$nmatchesin,$matchesin,$dict) {
 #dbgprint(true,"query_multi: print_table: $key, $nmatchesin\n");
 $table0 = "<span class='key' id='record_$ntab' /></span>\n";
 $matches=array();
 for($i=0;$i<$nmatchesin;$i++) {
  $matches[$i]=$matchesin[$i];
 }
 $display = new BasicDisplay($key,$matches,$filter,$dict);
 $table = $display->table;
 $table1 = transcoder_processElements($table,"slp1",$filter,"SA");
 echo $table0;
 echo $table1;
}

?>
