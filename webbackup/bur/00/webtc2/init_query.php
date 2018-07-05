<?php
// create query_dump.txt from xml file
 $filein = $argv[1]; // e.g., stc.xml
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
   //# if prevkey is empty, start a new $keydata
   //# else if a new key, output keydata
   //# else append data1 to keydata
   if ($prevkey == "") {
     $prevkey = $key;
     $keydata = $data1;
   }else if ($prevkey == $key) {
     $keydata .= " :: $data1"; 
   }else {
     fwrite($fpout,"$prevkey\t$keydata\n");
      $nfound1++;
      $prevkey = $key;
      $keydata = $data1;
   }
  }
 }
 // print last one
 fwrite($fpout,"$prevkey\t$keydata\n");
 fclose($fp);
 fclose($fpout);
 echo "$n records read from $filein<br/>\n";
 echo "$nfound1 records created in $fileout<br/>\n";
 exit;
function query_line($x) {
 // see construction in make_xml.php for some details
 // (a1) remove certain extended ascii
  $x = preg_replace("|<xb0/>|","",$x); // &deg;
  $x = preg_replace("|<xa6/>|","",$x); // broken bar
  $x = preg_replace("|<xa7/>|","",$x); // section sign
 // (a2) replace certain extended ascii
  $x = preg_replace("|<xc7/>|","S4",$x); // captial letter c with cedilla, S4
  $x = preg_replace("|<xe7/>|","s4",$x); // small letter c with cedilla, s4

 // (b) Remove  Sanskrit
 $x = preg_replace('|\{%.*?%\}|','',$x);
 $x = preg_replace('|\{@.*?@\}|','',$x);
 $x = preg_replace('|\{#.*?#\}|','',$x);

 // (c) Remove markup
 $x = preg_replace('/<.*?>/',' ',$x);
 $x = preg_replace('|\{^.*?^\}|','',$x);
 
 // (d) Remove punctuation
 $x = preg_replace('|\[Page.*?\]|','',$x);
 $x = preg_replace('/\|/','',$x); 
 $x = preg_replace('/[~_;.,$ ?()]+/',' ',$x);

 // (e) downcase
 $x = strtolower($x);
 
 // (f) replace AS codes (remove the number)
 $x = preg_replace("|s4|","c",$x); // small letter c with cedilla, s4
 $x = preg_replace("|[0-9]|","",$x);
 return $x;
}
?>
