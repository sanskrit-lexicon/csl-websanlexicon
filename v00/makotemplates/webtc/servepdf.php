<?php
error_reporting(E_ALL & ~E_NOTICE );
?>
<?php
/* servepdf.php  Apr 27, 2015 Multidictionary display of scanned images
  Similar to servepdf for the dictionaries
Parameters:
 dict: one of the dictionary codes (case insensitive)
 page: a specific page of the dictionary.  In the form of the contents
       of a <pc> element
 key: a headword, in SLP1.  
  Only one of 'page' and 'key' should be used.  If both are present, then
  'key' parameter is ignored and 'page' parameter prevails.
 July 11, 2018. Modify to work with 'raw' data or html data
*/
require_once('dictcode.php');
require_once('dbgprint.php');
require_once('parm.php');
require_once('dictinfo.php');
$dict = $dictcode;
$getParms = new Parm($dict);
$page = $_REQUEST['page'];
$dbg=false;
dbgprint($dbg,"servepdf: page=$page\n");
$dictinfo = new DictInfo($dict);
$year = $dictinfo->get_year();
$webpath = $dictinfo->get_webPath();
$webparent = $dictinfo->webparent;
$pdffiles_filename = "$webparent/web/webtc/pdffiles.txt";
$dictupper = $dictinfo->dictupper;

list($filename,$pageprev,$pagenext)=getfiles($pdffiles_filename,$page,$dictupper);
// 04-17-2018. Use The cologne images
// 04-20-2018. Use relative path if $pdf exists
/*
$dir = $webpath . "/pdfpages"; 
$pdffile = $dir . "/$filename";
$ds = DIRECTORY_SEPARATOR;
$pdfpages = dirname(__FILE__,2) . $ds . 'pdfpages';
$pdffile = dirname(__FILE__,2) . $ds . 'pdfpages' . $ds . "{$filename}";
*/
$pdffile = "../pdfpages/$filename";
if(file_exists($pdffile)) {
 $pdf = $pdffile;
}else { // Use the cologne images
 $dir = "{$dictinfo->get_cologne_webPath()}/pdfpages";
 $pdf = "$dir/$filename";
}
?>
<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8" />
<title><?= $dictupper?> Cologne Scan</title>
<link rel='stylesheet' type='text/css' href='serveimg.css' />
<!--
<link rel='stylesheet' type='text/css' href='//www.sanskrit-lexicon.uni-koeln.de/scans/awork/apidev/css/serveimg.css' />
-->
</head>
<body>
<?php  
$imageParms = array(
 'WIL' => "width ='1000' height='1500'",
 'PW'  => "width ='1600' height='2300'",
 'CCS' => "width ='1400' height='2000'",
 'MD'  => "width ='1000' height='1370'",
);
$imageParm = $imageParms[$dictinfo->dictupper];
?>
<?php if ($imageParm){?>
<img src='<?=$pdf?>' <?=$imageParm?> />
<?php }else{?>
<object id='servepdf' type='application/pdf' data='<?=$pdf?>'
  style="width: 98%; height:98%"></object>
<?php }?>

<div id='pagenav'>
<a href="servepdf.php?dict=<?=$dict?>&page=<?=$pageprev?>" 
   class='nppage'><span class='nppage1'>&lt;</span>&nbsp;</a>
<a href="servepdf.php?dict=<?=$dict?>&page=<?=$pagenext?>" 
   class='nppage'><span class='nppage1'>&gt;</span>&nbsp;</a>
</div>
</body>
</html>
<?php
function getfiles($pdffiles_filename,$pagestr_in0,$dictupper) { 
 // Next line for MW, where pagestr_in0 may start with 'Page', which we remove
 $pagestr_in0 = preg_replace('|^[^0-9]+|','',$pagestr_in0);
 // Recognize two basic cases: vol-page or page.
 // The pdffiles cases are usually one of the two
 // For these, we remove characters (such as column designations) 
 // that may be present if pagestr_in0 comes from the <pc> elt of the dictionary
 // as when the 'key' input GET parameter.
 if (preg_match('|^([1-9]-[0-9]+)|',$pagestr_in0,$matches)) {
  $pagestr_in = $matches[1];
 }elseif (preg_match('|^([0-9]+)|',$pagestr_in0,$matches)) {
  $pagestr_in = $matches[1];
 }else {
  // not sure if this case ever obtains
  $pagestr_in = $pagestr_in0;
 }

 $pagestr_in = preg_replace('/^0+/','',$pagestr_in);
 // echo "{$pagestr_in0}  -> {$pagestr_in}\n";
 //exit(1);
 $filename=$pdffiles_filename;
 $lines = file($filename);
 $pagearr=array(); //sequential
 $pagehash=array(); // hash
 $n=0;
 foreach($lines as $line) {
  $line = trim($line);  // 08-21-2018 Removes end of line chars, and white spc
  list($pagestr,$pagefile,$pagetitle) = preg_split('|:|',$line);
  # pagetitle currently unused, and may be absent, eg. in Wilson
  $n++;
  //$pagehash[$pagestr]=$n;
  $pagestr_trim = preg_replace('/^0+/','',$pagestr);
  $pagehash[$pagestr_trim]=$n;
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
 if ((!$ncur) && ($dictupper == 'PWG')) {
  $lnum = $pagestr_in;
  list($vol,$page) =  preg_split('/[,-]/',$lnum);
  $pagestr=$lnum;
  $ipage = intval($page);
  if (($ipage % 2) == 0) {
   $ipage = $ipage - 1;
   $pagestr = sprintf('%s-%04d',$vol,$ipage);
   $ncur = $pagehash[$pagestr]; 
  }
 }
 if ((!$ncur) && ($dictupper == 'GRA')) {
  $page= $pagestr_in;
  $pagestr=$page;
  $ipage = intval($page);
  if (($ipage % 2) == 0) {
   $ipage = $ipage - 1;
   $pagestr = sprintf('%d',$ipage);
   $ncur = $pagehash[$pagestr]; 
  }
  //echo "check GRA: $pagestr_in, $ipage, $ncur<br/>\n";
  //exit(1);
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
 //echo "($filecur,$pageprev,$pagenext)";
 //exit(1);
 return array($filecur,$pageprev,$pagenext);
}

?>
