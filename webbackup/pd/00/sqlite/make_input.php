<?php
/* Revised June 24, 2015.  Program got a memory-overflow errar when
   reading the entire pd.xml into memory: $lines = file($filein1);
   Changed to read one line at a time
*/
 $filein1 = $argv[1]; //"X.xml";
 $fileout = $argv[2]; //"input.txt";

 $fp = fopen($filein1,"r") or die("Cannot open $filein1\n");
 //$lines = file($filein1);
 $fpout = fopen($fileout,"w") or die("Cannot open $fileout\n");
 $lnum=0;
 //foreach($lines as $line){
 while(!feof($fp)) {
  $line = fgets($fp);
  $line = trim($line);
  if (!preg_match('|^<H1>|',$line)) {continue;}
  // construct output
  $lnum = $lnum + 1;
  if(!preg_match('|<key1>(.*?)</key1>.*<L>(.*?)</L>|',$line,$matches)) {
   echo "ERROR: Could not find key1,lnum from line: $line\n";
   exit(1);
  }
  $key1 = $matches[1];
  $lnum = $matches[2];
  $data = $line;
  $out = "$key1\t$lnum\t$data";
  fwrite($fpout,"$out\n");
 }
 fclose($fpout);
 fclose($fp);
exit(0);

?>