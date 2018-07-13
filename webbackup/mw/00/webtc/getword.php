<?php
//sanskrit/monier/disp2/monier.php
//ejf 11-04-2009
//ejf 11-09-2010
// ejf 11-26-2011 added nolit
//ejf 09-24-2012  modified for sanskrit1d
// ejf 10-31-2012 Use 'dal' access routine to mysql
// ejf 11-04-2013  Use dal_sqlite
// 07-08-2014  accents
$dir = dirname(__FILE__); //directory containing this php file
// $dirdocs = preg_replace('/docs\/.*$/','docs/',$dir);
// Note: $dir does not end in '/'
$dir = "$dir/";
$dir1 = $dir . '../utilities/';
$dirphp = realpath($dir1);
$pathutil = $dirphp ."/". 'transcoder.php';
require_once($pathutil); // initializes transcoder
require_once("dal_sqlite.php");
include("disp.php");

$filter0 = $_GET['filter'];
$filter = transcoder_standardize_filter($filter0);
 $filterin0 = $_GET['transLit']; // transLit
$filterin = transcoder_standardize_filter($filterin0);
//echo "filterin0 = $filterin0, filterin = $filterin <br>\n";
$accent = $_GET['accent'];
$keyin = $_GET['key'];
if (! $keyin) {$keyin = $argv[1];}
if (! $keyin) {$keyin='a';};
$keyin = trim($keyin);
// noLit: on or off
$noLit = $_GET['noLit'];
if(!$noLit) {
  $noLit = 'off';
};
monierSetNoLit($noLit);

//$key = transcoder_processString($keyin,$filterin,"slp1");
$keyin1 = preprocess_unicode_input($keyin,$filterin);
$key = transcoder_processString($keyin1,$filterin,"slp1");
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";
echo "$meta\n";
echo "<!--filter=$filter<br>,filterin=$filterin-->\n";
$dbg=False;
dbgprintdisp($dbg,"getword calling dal_mw1: $key\n");
$results = dal_mw1($key); 
dbgprintdisp($dbg,"getword returns from dal_mw1 " . count($results) . "\n");
$matches=array();
$nmatches=0;
 foreach($results as $line) {
 list($key1,$lnum1,$data1) = $line;
 $matches[$nmatches]=$data1;
 $nmatches++;
}

if ($nmatches == 0) {
 //echo "DBG: cmd1 = $cmd1\n";
 echo "<h2>not found: $keyin</h2>\n";
 echo "<h3> dbg: key = $key</h3>\n";
  $out1 = "<SA>$key</SA>";
  $out = transcoder_processElements($out1,"slp1",$filter,"SA");
 echo "<h1>&nbsp;$out</h1>\n";
 exit;
}

basicDisplaySetAccent($accent);
$table = basicDisplay($key,$matches,$filter);
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
