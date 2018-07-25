<?php
// create query_dump.txt from xml file (generic)
// modified to included embedded sanskrit, which is converted to slp
// modified for bor.  The headwords are English. The text contains
// embedded Sanskrit (<s>xx</s>) and English.
$dir = dirname(__FILE__); //directory containing this php file
// $dirdocs = preg_replace('/docs\/.*$/','docs/',$dir);
// Note: $dir does not end in '/'
$dir = "$dir/";
$dir1 = $dir . '../utilities/';
$dirphp = realpath($dir1);
$pathutil = $dirphp ."/". 'transcoder.php';
require_once($pathutil); // initializes transcoder

 $filein = $argv[1]; // e.g., xxx.xml
 $fileout = $argv[2]; // e.g., query_dump.txt
 $fp = fopen($filein,"r");
 if (!$fp) {
  echo "ERROR: Could not open $filein<br/>\n";
  exit;
 }

 $fpout = fopen($fileout,"w");
 if (!$fpout) {
  echo "ERROR: Could not open $fileout<br/>\n";
  exit;
 }
$n=0;
$prevkey='';
$lnum1=0;
$nfound=0;
$nfound1=0;
$prevkey="";
$key='';
$keydata="";

 while (!feof($fp)) {
  $line = fgets($fp);
  $line = trim($line);
  if (preg_match('|^<H.*?<key1>(.*?)</key1>.*<body>(.*?)</body>.*<L>(.*?)</L>|',$line,$matches)){
   $n++;
   $key=$matches[1];
   $body = $matches[2];
   $L=$matches[3];
   $data1 = query_line($body);
   $data2 = query_sanskrit($body);
   //# if prevkey is empty, start a new $keydata
   //# else if a new key, output keydata
   //# else append data1 to keydata
   if ($prevkey == "") {
     $prevkey = $key;
     $keydata_arr = array($data1);
     $keysanskrit_arr = array($data2);
   }else if ($prevkey == $key) {
     $keydata_arr[] = $data1;
     $keysanskrit_arr[] = $data2;
   }else {
     $keysanskrit = join(' ',$keysanskrit_arr);
     $keydata = join(' ',$keydata_arr);
     fwrite($fpout,"$prevkey\t$keysanskrit\t$keydata\n");
     $nfound1++;
     $prevkey = $key;
     $keydata_arr = array($data1);
     $keysanskrit_arr = array($data2);
   }
  }
 }
 // print last one
 $keysanskrit = join(' ',$keysanskrit_arr);
 $keydata = join(' ',$keydata_arr);
 fwrite($fpout,"$prevkey\t$keysanskrit\t$keydata\n");
 fclose($fp);
 fclose($fpout);
 echo "$n records read from $filein<br/>\n";
 echo "$nfound1 records created in $fileout<br/>\n";
 exit;
function query_line($x) {
 // see construction in make_xml.php for some details
 // (a1) remove extended ascii
  $x = preg_replace("|&#x....;|","",$x); // 

 // (b) English can appear in italics
 $x = preg_replace('|\{%.*?%\}|','',$x);
 //$x = preg_replace('|\{@.*?@\}|','',$x);

 // (c) Remove Sanskrit
 $x = preg_replace('|<s>.*?</s>|','',$x);
 // (c1) Remove other markup
 $x = preg_replace('/<.*?>/',' ',$x);
 //$x = preg_replace('|\{#.*?#\}|','',$x); // A few sanskrit letters coded as HK
 
 // (d) Remove punctuation
 $x = preg_replace('|\[Page.*?\]|','',$x);
 $x = preg_replace('/[~_;.,$ ?():]+/',' ',$x); // note '-' kept at this point

 // (e) downcase
 $x = strtolower($x);
 
 // (f) replace codes (remove the number)
 $x = preg_replace("|[0-9]|","",$x);
 // split $x into space-delimited parts. 
 $xparts = preg_split('/ +/',$x);
 // remove 1-character parts
 $yparts = array();
 foreach ($xparts as $y){
  $y = preg_replace('/-$/','',$y);
  if (strlen($y) <= 1) {continue;}
  $yparts[]=$y;
 }
 $y = join(' ',$yparts);
 return $y;
}
function query_sanskrit_helper($matches) {
 global $sanskrit_arr;
 $sanskrit_arr[] = $matches[1];
 return ""; // unimportant
}
function query_sanskrit($x) {
 // (a0) Gather the text in <s>xxx</s>
 global $sanskrit_arr;
 $sanskrit_arr=array();
 $temp = preg_replace_callback('|<s>(.*?)</s>|',"query_sanskrit_helper",$x);
 $x = join(' ',$sanskrit_arr);

 // see construction in make_xml.php for some details
 // (a1) remove extended ascii
  $x = preg_replace("|&#x....;|","",$x); // 
 // (b) Remove markup
 $x = preg_replace('/<.*?>/',' ',$x);
 // (d) Remove punctuation
 //$x = preg_replace('|\[Page.*?\]|','',$x);
 //$x = preg_replace('/\|/','',$x); 
 // Note this leaves '-'
 $x = preg_replace('/[~_;.,$ ?()]+/',' ',$x);

 return $x;
}
?>
