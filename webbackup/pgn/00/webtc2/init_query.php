<?php
// create query_dump.txt from xml file (generic)
// modified to included embedded sanskrit, which is converted to slp
$dir = dirname(__FILE__); //directory containing this php file
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
   //$data2 = ""; // currently, no good way to distinguish Sanskrit words.
   //# if prevkey is empty, start a new $keydata
   //# else if a new key, output keydata
   //# else append data1 to keydata
   if ($prevkey == "") {
     $prevkey = $key;
     $keydata = $data1;
     $keysanskrit = $data2;
   }else if ($prevkey == $key) {
     $keydata .= " :: $data1";
     $keysanskrit .= " :: $data2";
   }else {
     fwrite($fpout,"$prevkey :: $keysanskrit\t$keydata\n");
     $nfound1++;
     $prevkey = $key;
     $keydata = $data1;
     $keysanskrit = $data2;
   }
  }
  //if ($n > 100) {break;}  // dbg
 }
 // print last one
 fwrite($fpout,"$prevkey :: $keysanskrit\t$keydata\n");
 fclose($fp);
 fclose($fpout);
 echo "$n records read from $filein<br/>\n";
 echo "$nfound1 records created in $fileout<br/>\n";
 exit;
function query_line($x) {
 // see construction in make_xml.php for some details
 // (a0) revert German characters from &#xHHHH; to (unaccented) characters
 $german_hex=array(
  array("&#x00C4;","A"), // LATIN CAPITAL LETTER A WITH DIAERESIS
  array("&#x00C7;","C"), // LATIN CAPITAL LETTER C WITH CEDILLA
  array("&#x00D6;","O"), // LATIN CAPITAL LETTER O WITH DIAERESIS
  array("&#x00DC;","U"), // LATIN CAPITAL LETTER U WITH DIAERESIS
  array("&#x00E0;","a"), // LATIN SMALL LETTER A WITH GRAVE (not german)
  array("&#x00E4;","a"), // LATIN SMALL LETTER A WITH DIAERESIS
  array("&#x00E9;","e"), // LATIN SMALL LETTER E WITH ACUTE (not german)
  array("&#x00EB;","e"), // LATIN SMALL LETTER E WITH DIAERESIS
  array("&#x00F6;","o"), // LATIN SMALL LETTER O WITH DIAERESIS
  array("&#x00FC;","u") // LATIN SMALL LETTER U WITH DIAERESIS
 );
 foreach($german_hex as $a) {
  list($hex,$c) = $a;
  $x = preg_replace("|$hex|",$c,$x);
 }
 // (a1) remove remaining extended ascii
  $x = preg_replace("|&#x....;|","",$x); // 

 // (b) English can appear in italics
 //$x = preg_replace('|\{%.*?%\}|','',$x);
 //$x = preg_replace('|\{@.*?@\}|','',$x);

 // (c) Remove markup
 $x = preg_replace('|<s>.*?</s>|','',$x); // remove embedded SLP sanskrit
 $x = preg_replace('/<.*?>/',' ',$x);
 $x = preg_replace('|\{#.*?#\}|','',$x); // A few sanskrit letters coded as HK
 

 // (d) Remove punctuation
 $x = preg_replace('|\[Page.*?\]|','',$x);
 $x = preg_replace('/[~_;.,$ ?()\[\]]+/',' ',$x);
 // (e) downcase
 $x = strtolower($x);
 
 // (f) replace AS codes (remove the number)
 $x = preg_replace("|[0-9]|","",$x);
 return $x;
}
function query_sanskrit($x) {
 global $sanwords;
 $sanwords = array();
 // Get all the <s>x</s> words
 // The subroutine modifies $sanwords
 preg_replace_callback('|<s>(.*?)</s>|',"query_sanskrit_helper1",$x);
 $ans = join(' ',$sanwords);
 return $ans;
}
function query_sanskrit_helper1($matches) {
 global $sanwords;
 $s = $matches[1];  
 // remove xml markup
 $s = preg_replace('|<([^> ]*).*?>.*?</\1>|',' ',$s);
 $s = preg_replace('|<.*?>|',' ',$s);
 // remove extended ascii, which is coded as html entity: &...;
 $s = preg_replace('|&.*?;|',' ',$s);
 // remove slp accent chars, if present
 $s = preg_replace('|[/\\~^]|',' ',$s);
 $words = preg_split("/[^a-zA-Z|']/",$s);
 foreach ($words as $w) {
  $sanwords[]=$w;
 }
 return ""; // return value not important.
}
?>
