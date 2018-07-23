<?php
// modified June 24, 2013 to allow search for sanskrit words in body of text
// modified Nov 25,2013 for pwg.
$dbg = FALSE;
include("../utilities/transcoder.php");
$filter = $_GET['filter'];
$filename = $_GET['dictionary'];
$lastLnum = $_GET['lastLnum']; // file position, for seek&tell
$max = $_GET['max'];

// parms for sanskrit word
$opt_sregexp = $_GET['sregexp'];
$opt_sword = $_GET['sword'];
$opt_stransLit = $_GET['transLit'];
$opt_swordhw = $_GET['swordhw'];

// parms for non-Sanskrit word
$word = $_GET['word'];
if (!($word)) {
  //  $word="horse";
  $word = $argv[1];
}
$word = strtolower($word);
$opt_regexp = $_GET['regexp'];
$sopt_case = $_GET['scase'];
$outopt = $_GET['outopt'];

if (!($filter)) {$filter = "slp1";}
if (!($filename)) {$filename = "query_dump.txt";}
if (!($max)) {$max = 5;}
if (!($lastLnum)) {$lastLnum = 0;}
$lastLnum = intval($lastLnum);
if ($lastLnum < 0) {
    $lastLnum=0;
}
if ($lastLnum > 25000000) {
    $lastLnum = 0;
}
$meta = "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">";

$xmldata;
$xmlnew="";
$n = 0;
$matches;
$fp = fopen($filename,"r");
if (!$fp) {
    print "<n>$n</n>\n";
    print "<p>Could not open Dictionary '$filename'</p>\n";
    exit;
}
$non_word = "[^a-zA-Z0-9]";
$wordreg = "[a-zA-Z0-9-]";
$wordin="";
if ($word !="") {
 $wordchrs = preg_split ('/[^a-zA-Z.*?+]/',$word);
 $word = join('',$wordchrs);
 $wordin = $word;
 if ($opt_regexp == "exact"){
  $search_regexp = "[\t].*$non_word($word)$non_word";
 }else if ($opt_regexp == "prefix") {
  $search_regexp = "[\t].*$non_word($word$wordreg+)$non_word";
 }else if ($opt_regexp == "suffix") {
  $search_regexp = "[\t].*$non_word($wordreg+$word)$non_word";
 }else if ($opt_regexp == "instring"){
  $search_regexp = "[\t].*$non_word($wordreg+$word$wordreg+)$non_word";
 }else if ($opt_regexp == "substring"){
  $search_regexp = "[\t].*$non_word($wordreg*$word$wordreg*)$non_word";
 }else {
  $search_regexp = "[\t].*$word";
 } 
 $search_opt = $sopt_case;
 $tempar = matchkey($fp,$lastLnum,$search_regexp,$max,$search_opt,$word);
 $matches = $tempar['ans'];
 $lastLnum = $tempar['lastLnum'];
}else if (($opt_stransLit) && ($opt_sword))  {
 //in the file, the 'key' field is given in SLP.
 //we may need to modify from HK or ITRANS

 $slpword = translate_string2SLP($opt_stransLit,$opt_sword);
 $wordchrs = preg_split ('/[^a-zA-Z.*?+]/',$slpword);
 $slpword = join('',$wordchrs);
 $non_word = "[^a-zA-Z0-9]";
 $wordreg = "[a-zA-Z0-9-]";
 $wordin = $slpword;
 if ($opt_sregexp == "exact"){
  //$search_regexp = "^$slpword" . "[\t]";
  $search_regexp = "$non_word($slpword)$non_word.*[\t]";
 }else if ($opt_sregexp == "prefix") {
  //$search_regexp = "^$slpword.+" . "[\t]";
  $search_regexp = "$non_word($slpword$wordreg+)$non_word.*[\t]";
 }else if ($opt_sregexp == "suffix") {
  //$search_regexp = ".+$slpword" . "[\t]";
  $search_regexp = "$non_word($wordreg+$slpword)$non_word.*[\t]";
 }else if ($opt_sregexp == "instring"){
  //$search_regexp = ".+$slpword.+" . "[\t]";
  $search_regexp = "$non_word($wordreg+$slpword$wordreg+)$non_word.*[\t]";
 }else if ($opt_sregexp == "substring"){
  //$search_regexp = ".*$slpword.*" . "[\t]";
  $search_regexp = "$non_word($wordreg*$slpword$wordreg*)$non_word.*[\t]";
 }else {
  //$search_regexp = "^$slpword" . "[\t]";
  $search_regexp = "$slpword.*[\t]";
 } 
 $search_opt = $opt_stransLit;
 $tempar = smatchkey($fp,$lastLnum,$search_regexp,$max,$search_opt,$opt_swordhw);
 $matches = $tempar['ans'];
 $lastLnum = $tempar['lastLnum'];
}
fclose($fp);
print "$lastLnum" . "#";
echo "$meta\n";
$n = count($matches);
if ($n == 0) {
    print "<p>No matches found for:";
    print "'$word'";
    print "</p>";
    exit;
}
$noLit="on";
$noParen="off";

if ($outopt == "outopt1") {
//    display_outopt1();
}else if ($outopt == "outopt2") {
//    display_outopt2();
}else if ($outopt == "outopt3") {
//    display_outopt3();
}else if ($outopt == "outopt4") {
  $disp = display_outopt4($matches,$word,$search_regexp);
}else {
 $disp =display_outopt4($matches,$word,$search_regexp);
}
//$disp=FALSE;
if (!$disp) {
 print "<p>Problem with display</p>\n";
 $arr = array("filter" => $filter,
 "dictionary" => $filename, "lastLnum" => $lastLnum, "max" => $max,
 "sregexp" => $opt_sregexp, "sword" => $opt_sword, "stransLit" => $opt_stransLit,
 "word" => $word, "regexp" => $opt_regexp, "scase" => $sopt_case,
 "outopt" => $outopt);
 $arr['nline']=$tempar['nline'];
 $arr['nothing']=$tempar['nothing'];
 $arr['ntot']=$tempar['ntot'];

 print "<ul>\n";
  foreach($arr as $key => $val) {
   print "<li>$key = '$val'</li>\n";
  }
 print "</ul>\n";
 exit;
}
$filter = transcoder_standardize_filter($filter);
$table1 = transcoder_processElements($disp,"slp1",$filter,"SA");
echo $table1;

exit;
function matchkey($fp,$lastLnum,$regexp,$max,$opt,$word) {
// print "matchkey: $lastLnum,$regexp,$max,$opt,$word\n";
 $ntot=0;
 if (!($word)) {
  $word = "XYZ"; // so no match
 }
 fseek($fp,$lastLnum,0); // reposition
 if (!feof($fp)) {
  $line=fgets($fp);
 }else {
  $line = FALSE;
 }
 $ans = array();
 $nline=0;
 $nothing=0;
 while ($line) {
  $nline++;
  $linex="";
  $liney=$line;
  if (!preg_match("/$word/",$liney)) {
  //nothing to do
   $nothing++;
  }else if ($opt == "false"){
   if (preg_match("/$regexp/",$liney)) {   
    $linex=$line;
   }
  }else { 
   // print "Checking line: $liney\n";
   if (preg_match("/$regexp/i",$liney)) {   
    $linex=$line;
   }
  }
  if ($linex !="") {
   $newFlag = new_key_line($ntot,$line,$ans);
   // print "chk($newFlag): $line\n";
   if ($newFlag){
    $ans[$ntot] = $line;
    $ntot++;
    $lastLnum=ftell($fp); // get new file position
   }
  }
  if (!feof($fp)) {
   $line=fgets($fp);
  }else {
   $line = FALSE;
  }
  if (!($line)){$lastLnum = -1;}
  if ($ntot >= $max) {
   $line=FALSE; // end loop
  }
 }
 // print "chk: nline=$nline, nothing = $nothing, ntot=$ntot\n";
 $ans1=array();
 $ans1['ans']=$ans;
 $ans1['lastLnum']=$lastLnum;
 $ans1['nline']=$nline;
 $ans1['nothing']=$nothing;
 $ans1['ntot'] = $ntot;
 return $ans1;
}
function smatchkey($fp,$lastLnum,$regexp,$max,$transLit,$opt_swordhw) {
 $dbg=False;
 dbgprint($dbg,"smatchkey: swordhw = " . $opt_swordhw . "\n");
 $ntot = 0;
 fseek($fp,$lastLnum,0); // reposition
 //$line=fgets($fp);
 $ans=array();
 if (!feof($fp)) {
  $line=fgets($fp);
 }else {
  $line = FALSE;
 }
 //dbg: $ntry=0;
 while ($line) {
  $linex="";
  list($a,$b) = preg_split("|\t|",$line);
  if (preg_match("|^(.*?)::(.*?)$|",$a,$matches)) {
   $ahw = $matches[1];
   $atext = $matches[2];
  }else { // unexpected. Probably doesn't occur
   $ahw = $a;
   $atext = $a;
  }
  if ($opt_swordhw == 'both') {
   $liney=" " . $a . " \t"; // 
  } else if ($opt_swordhw == 'hwonly'){
   $liney=" " . $ahw . " \t"; // 
   //dbgprint($dbg,"liney = $liney\n");  // generates too much output
  } else if ($opt_swordhw == 'textonly'){
   $liney=" " . $atext . " \t"; //    
  } else { // should not occur. Same as both
   $liney=" " . $a . " \t"; // 
  }
  
  if (preg_match("/$regexp/",$liney)) {   
   $linex=$line;
  }
  if ($linex !="") {
   if (new_key_line($ntot,$line,$ans)){
    $ans[$ntot] = $line ;
    $ntot++;
    $lastLnum=ftell($fp); // get new file position
   }
  }
  if (!feof($fp)) {
   $line=fgets($fp);
  }else {
   $line = FALSE;
  }
  if (!($line)){$lastLnum = -1;}
  if ($ntot >= $max) {
   $line=FALSE; // end loop
  }
 }
 //dbg: fclose($fplog);
 $ans1=array();
 $ans1['ans']=$ans;
 $ans1['lastLnum']=$lastLnum;
 return $ans1;
}
function translate_string2SLP($transLit,$keyin) {
 $key = $keyin;
 $key = transcoder_processString($key,$transLit,'slp1');
 return $key;
}
function new_key_line($ntot,$line,$lines) {
 if (! preg_match('/^(.*?)\t(.*?)$/',$line,$matches)) {
  return FALSE;
 }
 $key = $matches[1];
 foreach($lines as $line1) {
  if (preg_match('/^(.*?)\t(.*?)$/',$line1,$matches)) {
   $key1 = $matches[1];
   if ($key1 == $key) {
    return FALSE;
   }
  }
 }
 return TRUE;
}
function display_outopt4 ($lines,$word,$search_regexp) {
 $nx=0;
 $xmlnew = "<p class='words'>\n";
 $y;
 $key;
 foreach($lines as $x) {
  if (preg_match('/^(.*?)\t(.*?)$/',$x,$matches)) {
   $nx++;
   $keypart = $matches[1];
   list($key,$sanskrit) = preg_split('|:|',$keypart);
   $key = trim($key);
   $y = $matches[2];
   $xmlnew .= "$nx <!-- $key --><a class='words' onclick='getWord4(\"$nx\");'><SA>$key</SA></a>";
   if ($word != "") {
    if (preg_match("/$search_regexp/",$x,$matches)) {
     $extra = $matches[1];
     $xmlnew .= "  ($extra)<br/>\n";
    }
   }else {
    $xmlnew .= "<br/>\n";
   }
  }
 }
 $xmlnew .= "</p>\n";
 return $xmlnew;
}
function dbgprint($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "querydbg.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}

?>
