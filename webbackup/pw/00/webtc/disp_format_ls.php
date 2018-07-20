<?php
/* disp_format_ls.php
 10-15-2017  factored out of disp.php. Applicable to pw and pwg dictionaries.
 Assumes transcoder and dbgprintdisp already in environment.
 This is version 2. It aims to improve the size of the numerical sections
*/
function format_ls($data) {
 // idea suggested by Marcis Gasyun. May 10, 2014
 //return $data; // dbg - turn this off
 // This logic assumes that $data is in AS (number-letter) coding
 $ansarr = array();
 $dbg=false;
 dbgprintdisp($dbg,"format_ls: data=$data\n");
 $parts = preg_split("|[.]|",$data);
 for($i=0;$i<count($parts);$i++) {
  $part = $parts[$i];
  #dbgprintdisp($dbg,"format_ls: part $i = $part\n");
  if (preg_match("|^[A-Z]|",$part)) { 
   //assume this is the ls name
   $part1 = ucfirst(strtolower($part));
   $ans1 = transcoder_processString($part1,"as","roman1");
   #dbgprintdisp($dbg,"format_ls:(a) part $i result=$ans1\n");
   $ansarr[] = $ans1;
  }else if (preg_match("|^[a-z]|",$part)){
   // Non-numerical text
   $ans1 = transcoder_processString($part,"as","roman1");
   $ans1 = "<span class='display'>$ans1</span>";
   #dbgprintdisp($dbg,"format_ls:(b) part $i result=$ans1\n");
   $ansarr[] = $ans1;
   }else if (preg_match("|^[0-9]|",$part)) {
    // assume $part is a sequence of 1 or more subrefs, separated by comma
    $subparts = preg_split("|,|",$part);
    $sizes = array("120","100","80");
    $subansarr = array();
    for($j=0;$j<count($subparts);$j++) {
     $subpart = $subparts[$j];
     if ($j<3) {
      $size = $sizes[$j];
     }else {
      $size = $sizes[2];
     }
     $subansarr[] = "<span style='font-size:$size%'>$subpart</span>";
   }
   $subans = join(',',$subansarr);
   $ansarr[] = $subans;
   // echo "\n"; // commented out Dec 16, 2015
  }else { // not sure which this is
   $ansarr[]= $part; //"<span class='display'>$part</span>";
  }
  dbgprintdisp($dbg,"format_ls: part $i:  $part -> " . $ansarr[$i]. "\n");
 }

 $ans1 = join('.',$ansarr);
 dbgprintdisp($dbg,"format_ls: Returns $ans1\n");
 return $ans1;
}

?>
