<?php
///web/webtc1/disphier.php
$dir = dirname(__FILE__); //directory containing this php file
require_once('../utilities/transcoder.php');
require_once('../webtc/dal_sqlite.php');
include("../webtc/disp.php");
// use relative pathnames for the sqlite databases (see $db=.. below)
// interpret GET parameters.
// 'key'
$keyin = $_GET['key'];
if (! $keyin) {$keyin='a';};
// new style
 list($filter ,$filterin ) = getParameters_keyboard();

$keyin1 = preprocess_unicode_input($keyin,$filterin);
$key = transcoder_processString($keyin1,$filterin,"slp1");

$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
//echo "<p>DBG: keyin=$keyin, filter=$filter, filterin=$filterin, keyin1=$keyin1, key=$key</p>";

$more = True;
$origkey = $key;
while ($more) {
 $results = dal_cae1($key); 
 $matches=array();
 $nmatches=0;
  foreach($results as $line) {
  list($key1,$lnum1,$data1) = $line;
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
 echo "<h2>not found: $keyin</h2>\n";
 //echo "<h3> dbg: key = $key</h3>\n";

  $out1 = "<SA>$key</SA>";
  $out = transcoder_processElements($out1,"slp1",$filter,"SA");
 echo "<h1>&nbsp;$out</h1>\n";
 exit;
}

$table = basicDisplay($key,$matches,$filter );
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
function getParameters_keyboard() {
 $phoneticInput = $_GET['phoneticInput'];
 $serverOptions = $_GET['serverOptions'];
 $viewAs = $_GET['viewAs'];
 // deduce filter  and filterin  from the above
 $filterin = getParameters_keyboard_helper($viewAs,$phoneticInput);
 $filter = getParameters_keyboard_helper($serverOptions,$phoneticInput);
 return array($filter ,$filterin );
}
function getParameters_keyboard_helper($type,$phoneticInput) {
 if ($type == 'deva') {return $type;}
 if ($type == 'roman') {return $type;}
 if ($type == 'phonetic') {
  if ($phoneticInput == 'slp1') {return $phoneticInput;}
  if ($phoneticInput == 'hk') {return $phoneticInput;}
  if ($phoneticInput == 'it') {return 'itrans';}
  if ($phoneticInput == 'wx') {return $phoneticInput;}
 }
 // default: 
 return "slp1";
}
?>
