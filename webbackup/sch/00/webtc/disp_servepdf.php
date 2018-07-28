<?php
/* disp_servepdf.php 
 sch variant.
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
 //$lnums = preg_split('/[,-]/',$data);
 $lnums = preg_split('/[,]/',$data);  //sch
 $serve = "../webtc/servepdf.php";
 foreach($lnums as $lnum) {
  list($page,$col) =  preg_split('/[-]/',$lnum);
  $lnumref=$lnum;
  //Dec 19, 2014  $page may have an alphabetic subscript.
  // Remove it.
  $page = preg_replace('/[^0-9]/','',$page);

  if ($ans == "") {
   //$args = "page=$lnumref";
   $args = "page=$page";
   $ans = "<a href='$serve?$args' target='_Blank'>$lnum</a>";
  }else {
   $ans .= ",$lnum";
  }
 }
 return $ans;
}

?>
