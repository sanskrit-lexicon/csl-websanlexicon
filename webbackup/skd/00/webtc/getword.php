<?php
//scans/SKDScan/2013/web/webtc/getword.php
//ejf 07-02-2013
// 10-20-2016 Revised to handle alternate headwords
$dir = dirname(__FILE__); //directory containing this php file
// Note: $dir does not end in '/'
$dir = "$dir/";
$dir1 = $dir . '../utilities/';
$dirphp = realpath($dir1);
$pathutil = $dirphp ."/". 'transcoder.php';
require_once($pathutil); // initializes transcoder
require_once("dal_sqlite.php");  
include("disp.php");
require_once("getAlter.php");

$filter0 = $_GET['filter'];
$filter = transcoder_standardize_filter($filter0);
 $filterin0 = $_GET['transLit']; // transLit
$filterin = transcoder_standardize_filter($filterin0);
//echo "filterin0 = $filterin0, filterin = $filterin <br>\n";
$keyin = $_GET['key'];
if (! $keyin) {$keyin=$argv[1];}
if (! $keyin) {$keyin='a';};

$keyin1 = preprocess_unicode_input($keyin,$filterin);
$key = transcoder_processString($keyin1,$filterin,"slp1");
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
$more = True;
$origkey = $key;
while ($more) {
 $results = dal_skd1($key); 
 $matches=array();
 $nmatches=0;
  foreach($results as $line) {
  list($key1,$lnum1,$data1) = $line;
  /*  10-20-2016. In case of an 'extra' or 'alternate' headword,
    we have to recompute data1 (namely, the body of data1)
  */
  $data1 = getAlter($data1);
  $matches[$nmatches]=$data1;
  $nmatches++;
 }
 if($nmatches > 0) {$more=False;break;}
 // try next shorter key
 $n = strlen($key);
 if ($n > 1) {
  $key = substr($key,0,-1); // remove last character
 } else {
  $more=False;
 }
}
if ($nmatches == 0) {
 //echo "DBG: cmd1 = $cmd1\n";
 echo "<h2>not found: $keyin</h2>\n";
 //echo "<h3> dbg: key = $key</h3>\n";
  $out1 = "<SA>$key</SA>";
  $out = transcoder_processElements($out1,"slp1",$filter,"SA");
 echo "<h1>&nbsp;$out</h1>\n";
 exit;
}

$table = basicDisplay($key,$matches,$filter); // from disp.php
$table1 = transcoder_processElements($table,"slp1",$filter,"SA");
echo $table1;
exit;
function preprocess_unicode_input($x,$filterin) {
 // when a unicode form is input in the citation field, for instance
 // rAma (where the unicode roman for 'A' is used), then,
 // the value present as 'keyin' is 'r%u0101ma' (a string with 9 characters!).
 // The transcoder functions assume a true unicode string, so keyin must be
 // altered.  This is what this function aims to accomplish.
 $hex = "0123456789abcdefABCDEF";
 $x1 = $x;
 if ($filterin == 'roman') {
  $x1 = preg_replace("/\xf1/","%u00f1",$x);
 }
 $ans = preg_replace_callback("/(%u)([$hex][$hex][$hex][$hex])/",
     "preprocess_unicode_callback_hex",$x1);
 return $ans;
}
function preprocess_unicode_callback_hex($matches) {
 $x = $matches[2]; // 4 hex digits
 $y = unichr(hexdec($x));
 return $y;
}

?>
