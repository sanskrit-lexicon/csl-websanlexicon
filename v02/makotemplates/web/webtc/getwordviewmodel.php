<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php 
/*getwordviewmodel.php
GetwordViewModel class  Takes a parameter object
   (of Parm class or an extension of Parm class)
   and constructs an html string for one 'key'
*/
require_once('dal.php');
require_once('basicadjust.php');
require_once('basicdisplay.php');
require_once('dispitem.php');

class GetwordViewModel {
 public $table, $table1, $status;
 public $getParms, $matches;
 public function __construct() { // remove $getParms
  $getParms = new Parm();
  $this->getParms = $getParms;
  $dict = $getParms->dict;
  $key = $getParms->key;
  $dal = new Dal($dict);
  // xmlmatches is array of records rec, where
  // rec is array with 3 items:
  //  0: key0  the headword of the record (usu. but not always same as key)
  //  1: lnum0 The Cologne id
  //  2: data:  xml string from xxx.xml
  $xmlmatches = $dal->get1_mwalt($key); 
  $dal->close();
  $xmldata = [];
  for ($i=0;$i<count($xmlmatches);$i++) {
   $xmlmatch = $xmlmatches[$i];
   list($key0,$lnum0,$xmldata0) = $xmlmatch;
   $xmldata[] = $xmldata0;
  }
  $adjxml = new BasicAdjust($getParms,$xmldata);
  $adjmatches = $adjxml->adjxmlrecs;

  // make this like csl-apidev/getword_data.php as much as possible
  $htmlmatches=[];
  // There is inefficiency in opening/closing data-base handles in
  // the next loop.
  //foreach($xmlmatches as $xmlmatch){
  for($i=0;$i<count($xmlmatches);$i++) {
   $xmlmatch = $xmlmatches[$i];
   list($key0,$lnum0,$xmldata0) = $xmlmatch;
   $adjxmldata0 = $adjmatches[$i];
   $html = $this->getword_data_html_adapter($key0,$lnum0,$adjxmldata0,$dict,$getParms);
   $htmlmatches[] = array($key0,$lnum0,$html);
  }
  // At this point, we proceed as in csl-apidev/getwordClass.php
  // Only difference is that we pass $htmlmatches as parameter
  $this->table1 = $this->getword_html($getParms,$htmlmatches);
  $nxml = count($xmlmatches);
  if ($nxml == 0) {
   $this->status = false;
  }else {
   $this->status = true;
  }

 } // __construct

 public function getword_html($getParms,$htmlmatches) {
  $dbg=false;
  $nmatches = count($htmlmatches);
  $key = $getParms->key;
  $keyin = $getParms->keyin1;
  if ($nmatches == 0) {
   $table1 = '';
   $table1 .= "<h2>not found: '$keyin' (slp1 = $key)</h2>\n";
  }else {
   $table = $this->getwordDisplay($getParms,$htmlmatches);
   dbgprint($dbg,"getword_html\n$table\n\n");
   $filter = $getParms->filter;
   $table1 = transcoder_processElements($table,"slp1",$filter,"SA");
  }
  return $table1;
 }

 public function getwordDisplay($parms,$matches) {
 // June 4, 2015 -- assume $matches is filled with records of form:
 //   $matches[$i] == array(key,lnum,rec) -
 //   rec = <info>pg</info><body>html</body>
 // June 14, 2015 for MW, info = pg:Hcode:key2a:hom
 // July 11, 2015  Use 'Parm' object for calling sequence
 // Aug 17, 2015 Remove use of _GET['options']. Always use $options='2'
 $dbg=false;
 $key = $parms->key;
 $dict = strtoupper($parms->dict);
 if(isset($_REQUEST['dispopt'])) {
  $temp = $_REQUEST['dispopt'];
  if (in_array($temp,array('1','2','3'))) {
   $options = $temp;
  }else {
   $options = '2';
  }
 }else { # dispopt not set
  $options = '2'; // $parms->options;
 }

 //$options = '1';  // trial
 /* 
    Sep 2, 2018. output link to basic.css depending on $parms->dispcss.
    Aug 4, 2020.  For webtc, never put out basic.css
 */
 $dictinfo = $parms->dictinfo;
 $webpath =  $dictinfo->get_webPath();
 
 if (isset($parms->dispcss) && ($parms->dispcss == 'no')) {
  $linkcss = "";
 }else {
  $linkcss = "<link rel='stylesheet' type='text/css' href='css/basic.css' />";
 }
 $linkcss = "";
if ($options == '3') {
 $output = '';
}else {
 $output = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
$linkcss
</head>
<body>
EOT;
}
 $english = $parms->english; 
/*
 $temp = $english ? 'true' : 'false';
 dbgprint($dbg,"basicDisplay fcn:  webpath=$webpath, linkcss=$linkcss, options=$options, english=$temp\n");
*/
/* use of 'CologneBasic' is coordinated with basic.css
  So basic.css won't interfere with the user page.  This
  assumes that the id 'CologneBasic' is unused on user page.
*/
 if (($options == '1')||($options == '2')) {
  $table = "<div id='CologneBasic'>\n";
  if ($english) {
   $table = "<div id='CologneBasic'>\n<h1>&nbsp;$key</h1>\n";
  } else {
   $filter = $parms->filter;
   if ($filter == 'deva') {
    $class = 'sdata_siddhanta';
   }else {
    $class = 'sdata';
   }
   $table = "<div id='CologneBasic'>\n<h1>&nbsp;<span class='$class'><SA>$key</SA></span></h1>\n";
  }
 }else if ($options == '3') {
  $table = "<div id='CologneBasic'>\n";  
 }else {
  $table = "<div id='CologneBasic'>\n";  
/*
  if ($english) {
   $table = "<div id='CologneBasic'>\n<h1>&nbsp;$key</h1>\n";
  } else {
   $table = "<div id='CologneBasic'>\n<h1>&nbsp;<SA>$key</SA></h1>\n";
  }
*/
 }
 $table .= "<table class='display'>\n";
 $ntot = count($matches);
 $dispItems=array();
 $dbg=false;
 for($i=0;$i<$ntot;$i++) {
  $dbrec = $matches[$i];
  dbgprint($dbg,"disp.php. matches[$i] = \n");
  for ($j=0;$j<count($dbrec);$j++) {
   dbgprint($dbg,"  [$j] = {$dbrec[$j]}\n");
  }
  $dispItem = new DispItem($dict,$dbrec);
  if ($dispItem->err) {
   $keyin = $parms->keyin;
   return "<p>Could not find headword $keyin in dictionary $dict</p>";
  }
  $dispItems[] = $dispItem;
 }  
 // modify dispitem->keyshow, (when to show the key)
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  if ($i==0) {//show if first item
  }else if ($dispItem->hom) { // show if a homonym
  }else if (strlen($dispItem->hcode) == 2) { // show; Only restrictive for MW
  }else if (($i>0) and ($dispItem->key== $dispItems[$i-1]->key)){ // don't show
   $dispItem->keyshow = ''; 
  }
 }
 // In the 'alt' version of MW,  not all of the keys shown are the same.
 // In this case, try adding css (shading?) to distinguish the keys that are
 // NOT the same as $parms->key.
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  if ($dispItem->key != $parms->key) {
   $dispItem->cssshade=true;
  }
 } 
 // Aug 15, 2015. Set firstHom instance variable to True where needed
 $found=False;
 // First, set firstHom always false
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  $dispItem->firstHom=False;
 }
 // Next, set it True on first record with hom
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  if ($dispItem->hom ) {
    $dispItem->firstHom=true;
    break;  // 
  }
 } 
 
 // Generate output
 $dispItemPrev=null;
 for($i=0;$i<$ntot;$i++) {
  $dispItem = $dispItems[$i];
  if ($options == '1') {
   $table .= $dispItem->basicDisplayRecord1($dispItemPrev);
  }else if ($options == '2') {
   $table .= $dispItem->basicDisplayRecord2($dispItemPrev);
  }else{
   $table .= $dispItem->basicDisplayRecordDefault($dispItemPrev);
  }
  $dispItemPrev=$dispItem;
 }
 $table .= "</table>\n";
 $output .= $table;
 $output .= "</div> \n";
 return $output;
 }

/* ------------------------------
  getword_data_html_adapter and related functions
*/
public function getword_data_html_adapter($key,$lnum,$adjxml,$dict,$getParms)
{
 // 08-07-2020.  This is the only place where BasicAdjust and
 // BasicDisplay are called.
 // We don't need to have arrays of strings, but only one string
 //  ($data is a string, one record  from xxx.xml)
 // BasicDisplay is written to allow a string for the second argument.
 /*
 $matches1=array($data);
 $adjxml = new BasicAdjust($getParms,$matches1);
 $matches = $adjxml->adjxmlrecs;
 */
 $filter = $getParms->filter;
 $display = new BasicDisplay($key,array($adjxml),$filter,$dict);
 $row1 = $display->row1;
 $row1x = $display->row1x; 
 $row = $display->row;
 $info = $row1;
 if ($row1x == '') { // True except for some mw verbs
  $body = "$row";
 } else {
  $body = "$row1x<br>$row";
 }
 $dbg=false;
 dbgprint($dbg,"adapter\n");
 dbgprint($dbg,"info = $info\n");
 dbgprint($dbg,"body = $body\n");

 # adjust body
 $body = preg_replace('|<td.*?>|','',$body);
 $body = preg_replace('|</td></tr>|','',$body);
 if ($dict == 'mw') {
  // in case of MW, we remove [ID=...]</span>
  $body = preg_replace('|<span class=\'lnum\'.*?\[ID=.*?\]</span>|','',$body);
 }
 # adjust $info - keep only the displayed page
 if ($dict == 'mw') {
  if(!preg_match('|>([^<]*?)</a>,(.*?)\]|',$info,$matches)) {
   dbgprint(true,"html ERROR 2: \n" . $info . "\n");
   exit(1);
  }
  $page=$matches[1];
  $col = $matches[2];
  $pageref = "$page,$col";
 }else {
  if(!preg_match('|>([^<]*?)</a>|',$info,$matches)) {
   dbgprint(true,"html ERROR 2: \n" . $info . "\n");
   exit(1);
  }
  $pageref=$matches[1];
 }
 if ($dict == 'mw') {
  list($hcode,$key2,$hom) = $this->adjust_info_mw($adjxml); 
  # construct return value as colon-separated values
  $infoval = "$pageref:$hcode:$key2:$hom";
  $ans = "<info>$infoval</info><body>$body</body>";
 }else {
  # construct return value
  $ans = "<info>$pageref</info><body>$body</body>";
 }
 return $ans;
}
public function adjust_info_mw($data) {
 # In case of MW, also retrieve Hcode and hom from head of $data
 $hom='';
 if (preg_match('|</key2><hom>(.*?)</hom>|',$data,$matches)) {
  $hom = $matches[1];
 }
 $hcode='';
 if (preg_match('|^<(H.*?)>|',$data,$matches)) { // always matches
  $hcode=$matches[1];
 }
 $key2='';
 if (preg_match('|<key2>(.*?)</key2>|',$data,$matches)) {
  $key2 = $matches[1];
 }
 $key2a = $this->adjust_key2_mw($key2);
 return array($hcode,$key2a,$hom);
}
public function adjust_key2_mw($key2) {
 $ans = preg_replace('|--+|','-',$key2);  // only 1 dash
 $ans = preg_replace('|<sr1?/>|','~',$ans); # ~ not in key1 for MW (?)
 $ans = preg_replace('|<srs1?/>|','@',$ans); # @ not in SLP1
 // Leave some xml in place:
 // <root>kf</root>
 // <root/>daMh
 // dA<hom>1</hom>
 // <shortlong/>
 $ans1 = preg_replace('|</?root/?>|','',$ans);
 $ans1 = preg_replace('|</?hom>|','',$ans1);
 $ans1 = preg_replace('|<shortlong/>|','',$ans1);
 if (preg_match('|<|',$ans1)) {
  dbgprint(true,"adjust_key2: $ans1\n");
  exit(1);
 }
 return $ans;
 $ans = preg_replace('||','',$ans);
 $ans = preg_replace('||','',$ans);
 return $ans;
}


public function unused_getword_data_html_adapter($key,$lnum,$data,$dict,$getParms)
{
 $matches1=array($data);
 $adjxml = new BasicAdjust($getParms,$matches1);
 $matches = $adjxml->adjxmlrecs;
 $filter = $getParms->filter;
 $display = new BasicDisplay($key,$matches,$filter,$dict);
 $table = $display->table;
 $tablines = explode("\n",$table); 
 $ntablines = count($tablines);
 /* $table is a string with 6 lines, or 7 lines when dict==mw
  Only indices 2,3,4 of $tablines are used here.
  The exact structure of these lines is complicated.
  STRUCTURE FOR MW
  $idx  $tablines[$idx] description
   0    <h1 class='$sdata'>&nbsp;<SA>$key2</SA></h1>
   1   <table class='display'>
   2  <tr><td>Hx and link to scan<br> (but for Hxy cases no Hxy)
   a) When there are Whitney links or Westergaard links,
   3   The Whitney/Westergaard links<br>
   4   html for the body of the entry
   5   </td><td>spaces</td></tr></table>
   6   empty line
   b) When there are no links
   3   html for the body of the entry
   4   </td><td>spaces</td></tr></table>
   5   empty line
  STRUCTURE FOR non-mw, and non-English headwords (i.e., not ae,mwe, bor, mw)
   0    <h1 class='$sdata'>&nbsp;<SA>$key2</SA></h1>
   1   <table class='display'>
   2  <tr><td>{KEY} {link to scan}  (but for Hxy cases no Hxy)
   3  <br> html for the body of the entry
   4  </td><td>spaces</td></tr></table>
   5   empty line

  STRUCTURE FOR  ae,mwe, bor,
   0    <h1>&nbsp;$key2</h1>
   1   <table class='display'>
   2  <tr><td>{KEY} {link to scan}  (but for Hxy cases no Hxy)
   3  <br> html for the body of the entry
   4  </td><td>spaces</td></tr></table>
   5   empty line
 */
  $dbg=true;
  for ($i=0;$i<$ntablines;$i++) {
   dbgprint($dbg,"tablines[$i]=" .$tablines[$i]."\n");
  }

 if (($ntablines != 6)&& ($ntablines != 7)){
  dbgprint(true,"html ERROR 1: actual # lines in table = $ntablines\n");
  for ($i=0;$i<$ntablines;$i++) {
   dbgprint(true,"tablines[$i]=" .$tablines[$i]."\n");
  }
  exit(1);
 }

 $info = $tablines[2];
 if ($ntablines == 6) {
  $body = $tablines[3];
 }else {  //$ntablines == 7
  $body = $tablines[3] . $tablines[4];
 }
 $dbg=true;
 dbgprint($dbg,"previous adapter\n");
 dbgprint($dbg,"info = $info\n");
 dbgprint($dbg,"body = $body\n");

 # adjust body
 $body = preg_replace('|<td.*?>|','',$body);
 $body = preg_replace('|</td></tr>|','',$body);
 if ($dict == 'mw') {
  // in case of MW, we remove [ID=...]</span>
  $body = preg_replace('|<span class=\'lnum\'.*?\[ID=.*?\]</span>|','',$body);
 }
 # adjust $info - keep only the displayed page
 if ($dict == 'mw') {
  if(!preg_match('|>([^<]*?)</a>,(.*?)\]|',$info,$matches)) {
   dbgprint(true,"html ERROR 2: \n" . $info . "\n");
   exit(1);
  }
  $page=$matches[1];
  $col = $matches[2];
  $pageref = "$page,$col";
 }else {
  if(!preg_match('|>([^<]*?)</a>|',$info,$matches)) {
   dbgprint(true,"html ERROR 2: \n" . $info . "\n");
   exit(1);
  }
  $pageref=$matches[1];
 }
 if ($dict == 'mw') {
  list($hcode,$key2,$hom) = $this->adjust_info_mw($data); 
  # construct return value as colon-separated values
  $infoval = "$pageref:$hcode:$key2:$hom";
  $ans = "<info>$infoval</info><body>$body</body>";
 }else {
  # construct return value
  $ans = "<info>$pageref</info><body>$body</body>";
 }
 return $ans;
 }
} // end of class
?>
