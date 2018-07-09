<?php
/* disp_servepdf.php
 getHrefPage generates markup for the link to a program which displays a pdf, as
 specified by the  input argument '$data'.
 In this implementation, the program which serves the pdf is
 $serve = ../webtc/servepdf.php.
 $data is assumed to be a string with a comma-delimited list of page numbers,
 only the first of which is used to generate a link.
 The markup returned for a given $lnum in the list $data is
   <a href='$serve?page=$lnum' target='_Blank'>$lnum</a>
 It is up to $serve to associate $lnum with a file.

*/
function getHrefPage($data) {
 $ans="";
 $lnums = preg_split('/,/',$data);
 $serve = "../webtc/servepdf.php";
 foreach($lnums as $lnum) {
  if ($ans == "") {
   //$hrefcur = getHrefPage_helper1($lnum);
   //$args="page=$hrefcur";
   $args = "page=$lnum";
   $ans = "<a href='$serve?$args' target='_Blank'>$lnum</a>";
  }else {
   $ans .= ",$lnum";
  }
 }
 return $ans;
}
/*
function getHrefPage_helper1($lnum) {
    $dir = dirname(__FILE__); //directory containing this php file
    //$dirdocs = preg_replace('/docs\/.*$/','docs/',$dir);
    $dirdocs = "$dir/";
    $filename=$dirdocs . "stcfiles.txt";
    $fp = fopen($filename,"r");
    if (!$fp) {
        //echo "getHrefPage_helper1: filename=$filename\n";
	//return "badfile=$filename"; // dbg
	return "$lnum";
    } 
    $line = getHrefPage_helper2($fp,$lnum);
    fclose($fp);
    if ($line) {
     $href0 = "/scans/STCScan/STCScanjpg"; // Cologne
     $anscur = "$href0/$line.jpg";
      return $anscur;
     }
     return "$lnum";
}
function getHrefPage_helper2($fp,$lnum) {
    $lnum1 = sprintf('%03d',$lnum);
    $regexp = "stchou-$lnum1-";
    $line=fgets($fp);
    while (!($line===FALSE)) {
	$line = trim($line);
	if (preg_match("/$regexp/",$line)) {
	    return $line;
	}
	$line=fgets($fp);
    }
    return FALSE;
}
*/
?>
