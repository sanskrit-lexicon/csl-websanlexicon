<?php
/* Jan 17, 2017
 servepdf_getfiles.php
*/
function getfiles($pagestr_in) {
 $dir = dirname(__FILE__); //directory containing this php file
 $filename="$dir/pdffiles.txt";
 $lines = file($filename);
 $pagearr=array(); //sequential
 $pagehash=array(); // hash
 $n=0;
 foreach($lines as $line) {
  list($pagestr,$pagefile,$pagetitle) = preg_split('|:|',$line);
  # pagetitle currently unused
  $n++;
  $pagehash[$pagestr]=$n;
  $pagearr[$n]=array($pagestr,$pagefile);
 }
 $ncur = $pagehash[$pagestr_in];
 if (!$ncur) {
  $pagenum = intval($pagestr_in); // result is 0 if not a string of digits
  if (($pagenum % 2) == 1) {
   $pagenum = $pagenum - 1;
  }
  $pagestr = "$pagenum";
  $ncur = $pagehash[$pagestr];
 }
 if(!$ncur) {
  $ncur=1;
 }
 list($pagestrcur,$filecur) = $pagearr[$ncur];
 $nnext = $ncur + 1;
 if ($nnext > $n) {$nnext = 1;}
 $nprev = $ncur - 1;
 if ($nprev < 1) {$nprev = $n;}
 //echo "nprev,ncur,nnext = $nprev,$ncur,$nnext\n";
 list($pagenext,$dummy) = $pagearr[$nnext];
 list($pageprev,$dummy) = $pagearr[$nprev];
 return array($filecur,$pageprev,$pagenext);
}
?>
