<?php
// Exclude WARNING messages also, to solve Peter Scharf Mac version.
// 12-01-2022. Disable exclusions of error_reporting
//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
?>
<?php
require_once('dbgprint.php');
require_once('parm.php');  // loads transcoder
require_once('getword_data.php');
require_once('dispitem.php');

class GetwordClass {
 public $getParms,$matches,$table1,$status,$basicOption;
 public $xmlmatches;
 public function __construct($basicOption = true) {
  $this->getParms = new Parm();
  // $this->basicOption = $this->getParms->basicOption;
  $this->basicOption = $basicOption; // 06-19-2024 Refer webtc1/
  $temp = new Getword_data($basicOption);
  $this->matches = $temp->matches; 
  $this->table1 = $this->getword_html();
  $this->xmlmatches = $temp->xmlmatches;
  $nmatches = count($this->matches);
  if ($nmatches == 0) {
   $this->status = false;
  }else {
   $this->status = true;
  }
 }

 public function getword_html() {
  $getParms = $this->getParms;
  $matches  = $this->matches;
  $dbg=false;
  $nmatches = count($matches);
  $key = $getParms->key;
  $keyin = $getParms->keyin1;
  if ($nmatches == 0) {
   $table1 = '';
   $table1 .= "<h2>not found: '$keyin' (slp1 = $key)</h2>\n";
  }else {
   //$dbg = true;
   $table = $this->getwordDisplay($getParms,$matches);
   dbgprint($dbg,"getword table\n$table\n\n");
   $filter = $getParms->filter;
   $dict = strtoupper($getParms->dict);
   $accent = $getParms->accent;
   //dbgprint(true,"getwordClass before: dict=$dict, accent=$accent, filter=$filter\n");
   if (in_array($dict,array('PWG','PW','PWKVN')) &&
               ($filter == 'deva') && ($accent == 'yes')) {
    // Causes display of udatta accent to be superscript Devanagari 'u'
    // As occurs in the print of these dictionaries. So slp1_deva1.xml is
    // used as the transcoder file.
    $filter = 'deva1';
   } else if (in_array($dict,array('MW')) &&
                       ($filter == 'deva') && ($accent == 'yes')) {
    // deva2 is like deva1, except udAtta accent not displayed
    $filter = 'deva2';
   }
   $table1 = transcoder_processElements($table,"slp1",$filter,"SA");
   dbgprint($dbg,"getword table1\n$table1\n\n");

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
 // Aug 17, 2024 for MW, info = pg:Hcode:key2a:hom:hui
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

 /* 
    Sep 2, 2018. output link to basic.css depending on $parms->dispcss.
    Aug 4, 2020.  For webtc, never put out basic.css
    Jun 19, 2024. Always $lincss = "": Not sure what this is for!
       Reason: "css/basic.css" is not available and generates error
        message in console.
 */
 $dictinfo = $parms->dictinfo;
 $webpath =  $dictinfo->get_webPath();

 if (isset($parms->dispcss) && ($parms->dispcss == 'no')) {
  $linkcss = "";
 }else {
  $linkcss = "<link rel='stylesheet' type='text/css' href='css/basic.css' />";
 }
 if ($this->basicOption) {
  $linkcss = "";
 }
 $linkcss = ""; // 06-19-2024
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
/* use of 'CologneBasic' is coordinated with basic.css
  So basic.css won't interfere with the user page.  This
  assumes that the id 'CologneBasic' is unused on user page.
*/
 if (($options == '1')||($options == '2')) {
  $table = "<div id='CologneBasic'>\n";
  //if ($this->basicOption) {
  if (true) {  // 06-19-2024
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
  }
 }else if ($options == '3') {
  $table = "<div id='CologneBasic'>\n";  
 }else {
  $table = "<div id='CologneBasic'>\n"; 

 }

 $table .= "<table class='display'>\n";
 $ntot = count($matches);
 $dispItems=array();
 $dbg=false;
 for($i=0;$i<$ntot;$i++) {
  $dbrec = $matches[$i];
  if ($dbg) {
   dbgprint($dbg,"getwordClass dbrec[$i] = \n");
   for ($j=0;$j<count($dbrec);$j++) {
    dbgprint($dbg,"  [$j] = {$dbrec[$j]}\n");
   }
   dbgprint($dbg,"GETWORDCLASS: dbrec = " . $dbrec[2] . "\n\n");
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
  if (isset($dispItem->hcode)) {
   $hcode = $dispItem->hcode;
  }else {
   $hcode = '';
  }
  if ($i==0) {//show if first item
  }else if ($dispItem->hom) { // show if a homonym
  }else if (strlen($hcode) == 2) { // show; Only restrictive for MW
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
 // Also, set firstHui always false
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  $dispItem->firstHom=False;
  $dispItem->firstHui=False;
 }
 // Next, set firstHom True on first record with hom 
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  if ($dispItem->hom ) {
    $dispItem->firstHom=true;
    break;
  }
 } 
// Next, set firstHui True on first record with hui 
 for($i=0;$i<$ntot;$i++) {
  $dispItem=$dispItems[$i];
  if ($dispItem->hui ) {
    $dispItem->firstHui=true;
    break;
  }
 } 
 
 // Generate output
 $dispItemPrev=null;
 for($i=0;$i<$ntot;$i++) {
  $dispItem = $dispItems[$i];
  $dispItem1 = "";
  if ($options == '1') {
   $dispItem1 = $dispItem->basicDisplayRecord1($dispItemPrev);
  }else if ($options == '2') {
   $dispItem1 = $dispItem->basicDisplayRecord2($dispItemPrev);
  }else{
   $dispItem1 = $dispItem->basicDisplayRecordDefault($dispItemPrev);
  }
  $table .= $dispItem1;
  $dispItemPrev=$dispItem;
 }
 $table .= "</table>\n";
 $output .= $table;
 $output .= "</div> \n";
 return $output;
}
}
?>
