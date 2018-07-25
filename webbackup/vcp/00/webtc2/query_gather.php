<?php
///afs/rrz.uni-koeln.de/vol/www/projekt/sanskrit-lexicon/http/docs/scans/VCPScan/2013/web/webtc2/query_gather.php
include('../utilities/transcoder.php');
require_once('../webtc/dal_sqlite.php');
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
$data = $_POST['data'];
$utilchoice = $_POST['utilchoice'];
$filter0 = $_POST['filter'];
$dbg=False;
if ($dbg) { 
$fp = fopen("querygatherdbg.txt","w");
fwrite($fp,"utilchoice = $utilchoice, filter0 = $filter0\n");
fwrite($fp,"data=\n$data\n");
fclose($fp);
}

$filter = transcoder_standardize_filter($filter0);
$data1 = preg_replace('/<\/key1>/','',$data);
$keyar = preg_split('/<key1>/',$data1);
$matches=array();
$nmatches=0;
foreach($keyar as $key) {
 if (trim($key) == '') {continue;} // artifact of splitting
 $results = dal_vcp1($key);
if ($dbg) { 
$fp = fopen("querygatherdbg.txt","a");
 $ntemp = count($results);
fwrite($fp,"key = $key, #results = $ntemp\n");
fclose($fp);
}
 $nresults=0;
 foreach($results as $line) {
  list($key1,$lnum1,$data2) = $line;
  $data2 = trim($data2);
  $matches[]=$data2;
  $nmatches++;
  $nresults++;
if ($dbg) { 
$fp = fopen("querygatherdbg.txt","a");
fwrite($fp,"key1=$key1, lnum1=$lnum1, nresults=$nresults, data2=$data2\n");
fclose($fp);
}
 }
if ($dbg) { 
$fp = fopen("querygatherdbg.txt","a");
fwrite($fp,"now nresults=$nresults\n");
fclose($fp);
}
 if ($nresults == 0) {
  $data1 = "<Hx><h><key1>$key1</key1></h><body>" .
		"no data for key1=$key1</body><tail></tail></Hx>";
  $matches[]=trim($data1);
  $nmatches++;
 }
}
//$table1 = join('\n',$matches);
$table1 = join("\n",$matches);
print $table1;

if (False) {
$f = fopen('query_gather_dbg.txt','w');
$rawtable1 = join("\n",$rawmatches);
fwrite($f,"rawdata\n");
fwrite($f,"$rawtable1\n");
fwrite($f,"adjusted data\n");
fwrite($f,$table1);
fclose($f);
}
exit;
function unused_to_html_entities($x) {
 $y = preg_replace("/%u([0-9A-F]*)/","&#x\\1;",$x);
 $zarr = array();
 for ($i=0;$i<strlen($y);$i++) {
  $c = $y[$i];
  $ic = ord($c);
  if ($ic <= 127) {
   $zarr[]=$c;
  }else {
   $z = sprintf("&#x%04x;*",$ic);
   $zarr[]=$z;
  }
 }
 return join('',$zarr);
// return $x1;
 $y = htmlentities($x1,ENT_IGNORE,"UTF-8");
 $y = preg_replace('|&lt;|','<',$y);
 $y = preg_replace('|&gt;|','>',$y);
 return $y;
}

?>
