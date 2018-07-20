<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php
/*
// web/webtc/disp.php
// The main function basicDisplay constructs an HTML table from
// an array of data elements.
// Each of the  data elements is a string which is valid XML.
// The XML is processed using the XML Parser routines (see PHP documentation)
// This XML string is further assumed to be in UTF-8 encoding.
// Apr 5, 2017. Modified to be consistent with revisions. Unused code removed.
// May 4, 2017. Render '<div>'. Follow indentation pattern used with AP
//              Remove more unused code.
//              Change display to use 1 column, follow SCH example.
// May 5, 2017. Correct '<pb>' display.
//              Render '--' as unicode EM DASH
// May 22, 2017 <hwtype>, <alt>, <div n="3">
//               Experimental handling of <br/> [See line_adjust]
//              Code ^x  as <sup>x</sup>  (superscript).
// Sep 16, 2017 stc specific code  (adapted from vcp version)
// Oct 6, 2017  pw specific. (adapted from stc version)
    1. basicDisplaySetAccent  copied from previous pw disp.php.
       Not sure if this is required, but the local 'getword' calls this
       function.  
    2. <lex>  treat as bold
    3. <ls>  handle in line_adjust; add line_adjust1_callback
             getLSdata, dbgprintdisp (optional), getLSdata_helper,
             format_ls, format_subpart
             <lshead>
      Note:  in line_adjust1_callback, it is neeed to convert the
             contents of <ls> tag to AS coding.
             This is because of logic in format_ls;
             I tried to do the roman1-as conversion in format_ls,
             which is called on each text node in chrhndl.  However,
             it was found that the xml parser puts non-ascii characters
             into separate nodes from ascii characters; and this interferes
             with the intended results of format_ls (which want to capitalize
             the first letter, and lower-case the subsequent letters).
    4. <is>  IAST-Sanskrit - render as font-style:normal
    5. <bot>  botanical name. render in brown (same as MW)
    6. <sic/> Do not render
    7. <ab>  Tooltip. Follow pattern of mw.
             A separate database required.
             Also requires addition to dal_sqlite
             getABdata
*/
require_once("disp_servepdf.php");
require_once("dal_sqlite.php");
require_once("disp_format_ls.php"); // for pw, pwg
$dir = dirname(__FILE__); //directory containing this php file
// Note: $dir does not end in '/'
$dir = "$dir/";
$dir1 = $dir . '../utilities/';
$dirphp = realpath($dir1);
$pathutil = $dirphp ."/". 'transcoder.php';
require_once($pathutil); // initializes transcoder
$dbg = false;

$parentEl;
$row;
$row1;
$pagecol = "";
$inSanskrit=false;
$inkey2;
$dbg=false;

// for Advanced search11
global $accent;
$accent=false;
//echo "DEBUG 1\n";
function monierSetNoLit($value) {
 // This function has no effect now (May 4, 2017)
 $noLit = $value;
}
//echo "DEBUG 2\n";
function basicDisplaySetAccent($accentin){
 global $accent;
 if ($accentin == 'yes') {
  $accent = true;
 }else {
  $accent = false;
 }
}
function basicDisplay($key,$matches,$filterin) {
 global $row,$row1,$pagecol,$inSanskrit,$dbg;
 global $inkey2;
 global $parentEl;
 $dbg=false;
 dbgprintdisp($dbg,"webtc/disp.php: basicDisplay enter\n");
 $table = "<h1>&nbsp;<SA>$key</SA></h1>\n";

 $table .= "<table class='display'>\n";
 $ntot = count($matches);
 //echo "<p>$ntot lines for $key</p>\n";
 $i = 0;
 while($i<$ntot) {
  $linein=$matches[$i];
  $line=$linein;
  dbgprintdisp($dbg,"webtc/disp.php: basicDisplay loop $i of $ntot\n");
  $line=trim($line);
  $l0=strlen($line);
  dbgprintdisp($dbg,"webtc/disp.php: basicDisplay: enter line_adjust\n");
  $line=line_adjust($line);
  dbgprintdisp($dbg,"webtc/disp.php: basicDisplay: return line_adjust\n");
  $row = "";
  $row1 = "";
  
  $inSanskrit=false;
  $inkey2 = false;
//   $line = preg_replace('/</','&lt;',$line);
//   $line = preg_replace('/>/','&gt;',$line);
//   echo "<p>The underlying data for this line:<br/>$line</p>\n";
  dbgprintdisp($dbg,"<!--begin parse $i=\n$line\n");
  $p = xml_parser_create('UTF-8');
  xml_set_element_handler($p,'sthndl','endhndl');
  xml_set_character_data_handler($p,'chrhndl');
  xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,FALSE);
  if (!xml_parse($p,$line)) {
//   sprintf("XML error: %s ",
//                    xml_error_string(xml_get_error_code($p)));
   $row1 = "basicDidsplay Error parsing line:";
   //$line=$linein;
   $fpout = fopen("error.xml","w");
   fwrite($fpout,$line);
   $row = $line;
  }
  xml_parser_free($p);
  dbgprintdisp($dbg,"<!--after line, row1=$row1\n\nrow=$row1\n\n-->");
  /* May 4, 2017
  $table .= "<tr><td class='display' valign=\"top\">$row1</td>\n";
  $table .= "<td class='display' valign=\"top\">$row</td></tr>\n";
  */
  $table .= "<tr>";
  $table .= "<td>";
  $style = "background-color:beige";
  $row1a = "<span style='$style'>$row1</span>";
  $table .= "$row1a\n<br/>$row\n";
  $table .= "</td>";
  $table .= "</tr>";
  $i++;
 }
 $table .= "</table>\n";
 return $table;
}

function unused_s_callback($matches) {
/* no special coding for Sanskrit in <s>X</s> form.
    So, just remove the <s>,</s> elements
  10-15-2017. This function not now required, as
  there are no instances of page breaks being embedded
  in <s> (Devanagari) elements.
*/
 $x = $matches[0];
 $x = preg_replace("|(\[Page.*?\])|","</s> $0 <s>",$x);
 //$x = preg_replace("|</?s>|","",$x);
 return $x;
}
function line_adjust($line) {
 global $pagecol;
 $dbg = false;
 if ($dbg) {
  $line1 = preg_replace('|<|',"&lt;",$line);
  $line1 = preg_replace('|>|',"&gt;",$line1);
  echo "<p>@0, line1 = $line1</p>\n";
 } 
 /* removed 10-15-2017
 $line = preg_replace_callback('|<s>(.*?)</s>|',"s_callback",$line);
 */
 // Introduce '<pb>' element for intra-element page breaks
 $line = preg_replace("|\[Page.*?\]|",  "<pb>$0</pb>",$line);
 // '<pc>' is part tail element. This adjustment (remove 'Page') not needed
 // comment out 10-15-2017
 //$line = preg_replace('/<pc>Page(.*)<\/pc>/',"<pc>\\1</pc>",$line);
 // Get global '$pagecol' from '<pc>'
 if (preg_match('/<pc>(.*)<\/pc>/',$line,$matches)){
  if($pagecol == $matches[1]){
   $line = preg_replace('/<pc>(.*)<\/pc>/','',$line);
  }else {$pagecol = $matches[1];}
 }
 // prepare for complicated logic in display of '<ls>' tags.
 $line = preg_replace_callback('/<ls>(.*?)<\/ls>/',"line_adjust1_callback",$line);
 return $line;
}
function dbgprintdisp($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbgdisp.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}
function line_adjust1_callback($matches) {
 $dbg=false;
 $lskey = $matches[1]; // content of <ls> tag

 $lsdata = getLSdata($lskey);
 dbgprintdisp($dbg,"line_adjust1_callback: lskey=$lskey, lsdata = $lsdata\n");
 if (!$lsdata) {
  $ans = $matches[0]; // original ls element: no 'n' attribute 10-15-2017x
 }else {
 // $lsdata 
 // 10-09-2017 convert $lskey from IAST to AS.
 // This is required so that format_ls will work properly
 $lskey_as = transcoder_processString($lskey,"roman1","as");
 // 10-15-2017x
 //$ans = "<lshead $lsdata><ls>$lskey_as</ls></lshead>";
 // put lskey_as in attribute, and put some non-whitespace content in ls,
 // (so chrhndl will be called) We use ?
 $ans = "<lshead $lsdata><ls n=\"$lskey_as\">?</ls></lshead>";
 }
 dbgprintdisp($dbg,"line_adjust1_callback: ans=$ans\n");
 return $ans;  
}

function getLSdata($lskey) {
   $dbg = false;
     dbgprintdisp($dbg," getLSdata enter: lskey=$lskey\n");
    //$ans=" $lskey ??"; // returned if problem
    $ans = "";
    $data1 = getLSdata_helper($lskey);
    dbgprintdisp($dbg,"back from getLSdata_helper\n");
    dbgprintdisp($dbg," getLSdata: data1=$data1\n");
    if ($data1 != "") {
     //data1 is key into pwauth table
     $file = "pwauth.html"; // directory is in winls javascript
     $lskey1 = preg_replace('/[.]/','_',$data1);
     $file1 = "$file#" . "record_$lskey1";
     // {} are used instead of <>; the <> are returned in chrhndl.
     //July 8, 2014.  Transcode $lskey
     //Oct 6, 2017. $lskey already is Unicode 'roman' (IAST)
     //$lskeyas = transcoder_processString($lskey,"as","roman");
     //$ans = "{a href='javascript:winls(\"$file\",\"record_$lskey1\")'}$lskeyas{/a}";
    // $ans = "href='javascript:winls(\"$file\",\"record_$lskey1\")'";
    /// $ans = "href='javascript:winls(\'$file\',\'record_$lskey1\')'";
    // The examples of KUHN'S cause an error in the next.
    // But the PHP addslashes method escapes these
    $lskey1 = addslashes($lskey1);
    dbgprintdisp($dbg," getLSdata: lskey1=$lskey1\n");
     $ans = <<<EOT
href="javascript:winls('$file','record_$lskey1')"
EOT;
    } 
     dbgprintdisp($dbg," getLSdata: ans=$ans\n");
    return $ans;
}
function getLSdata_helper($ls) {
 $dbg=false;
 $parts = explode(".",$ls);
 $ans = "";
 if(preg_match("/^[0-9]/",$parts[0])) {
  return $ans;
 }
 if(preg_match("/^(\()|(\[)|(ebend)/",$parts[0])) {
  return $ans;
 }
 // get longest match
 for($i=0;$i<count($parts);$i++) {
  $part = $parts[$i];
  if ($i == 0) {
   $abbrvtest=$part;
  }else if (preg_match("/^[0-9]/",$part)) {
   break;
  }else {
   $abbrvtest = "$abbrvtest.$part";
  }
  dbgprintdisp($dbg,"Calling dal_linkpwauthorities with $abbrvtest\n");
  $data = dal_linkpwauthorities($abbrvtest);
  dbgprintdisp($dbg,"return dal_linkpwauthorities with $abbrvtest\n");
  if ($data) {
   $ans = $abbrvtest;
  }
 }
 return $ans;
}

function getABdata($key) {
 global $dispfilter;
 // abbreviation tool tips.
 $ans="";
 $result = dal_pwab($key);
 if (count($result) == 1) {
  list($key1,$data) = $result[0];
  if (preg_match('/<disp>(.*?)<\/disp>/',$data,$matches)) {
   $ans = $matches[1];
   $temp = strtolower($dispfilter);
   $filterflag = (preg_match('/deva/',$temp) || preg_match('/roman/',$temp));
   if ($filterflag) {
	$ans = preg_replace('/<s>/','<SA>',$ans);
	$ans = preg_replace('/<\/s>/','</SA>',$ans);
   }
  }
 }
 return $ans;
}

function sthndl($xp,$el,$attribs) {
 global $row,$row1,$pagecol,$inSanskrit;
 global $inkey2;
 global $parentEl,$abexpand,$lsexpand;

  if (preg_match('/^H.+$/',$el)) {
   // don't display 'H1'
   // $row1 .= "($el)";
  } else if ($el == "s")  {
   $inSanskrit = true;
  } else if ($el == "key2"){
   $inkey2 = true;
  } else if ($el == "b"){ // bold
   $row .= "<strong>"; 
  } else if ($el == "lex"){ // m. f., etc.
   $row .= "<strong>"; 
  } else if ($el == "i"){
   $row .= "<i>"; 
  } else if ($el == "br"){
   $row .= "<br/>";   
  } else if ($el == "h"){
  } else if ($el == "body"){
  } else if ($el == "tail"){
  } else if ($el == "L"){
  } else if ($el == "pc"){
  } else if ($el == "pb"){
   $row .= "<br/>";
  } else if ($el == "key1"){
  } else if ($el == "hom"){
  } else if ($el == "F"){
   $row .= "<br/>&nbsp;<span class='footnote'>[Footnote: ";
  } else if ($el == "symbol") {
  } else if ($el == "div") {
   // for vcp, just a line break
   // line break, and 
   //  n = 'P': Sub-headword. indent
   //  n = '2': before '||'.  before '||'.  Extra indent
   //  n = '3': before mdash.  no indent
   // indent, whether 'n' is '2' or 'P' (only values allowed) 05-04-2017
   // also, n='3' 05-21-2017 
   //  Examples: n=2: akulAgamatantra
   //  n=P paRqitasvAmin
   //  n=3 agastyasaMhitA
   // for pw:
   //  n = 1 (number div), n = 2 (English letter), n = 3 (Greek letter)
   //  n = p (prefixed form, in verbs
   $n=$attribs['n'];
   //$row .= "<br/>";
   if ($n == '1') {$indent = "1.5em";}
   else if ($n == '2') {$indent = "3.0em";}
   else if ($n == '3') {$indent = "4.5em";}
   else {$indent = "";}
   $style="position:relative; left:$indent;";
   $row .= "<br/><span style='$style'>";

  } else if ($el == "alt") {
   // Alternate headword
   $style = "font-size:smaller";
   $row .= "<span style='$style'>(";
  } else if ($el == "hwtype") {
   // Ignore
  } else if ($el == "sup") {
   $row .= "<sup>";
  } else if ($el == "lbinfo") {
    // empty tag.
  } else if ($el == "lang") {
    // nothing special here
    $row .= " (greek) ";
  } else if ($el == "lb") {
    $row .= "<br/>";
  } else if ($el == "C") {
   // vcp specific
   $n = $attribs['n'];
   if ($n == '1') {
    $row .= "<br/>";
   }
   $row .= "<strong>(C$n)</strong>";
  } else if ($el == "edit"){ // vcp
    // no display
  } else if ($el == "ls") {
   
   if (isset($attribs['n'])) {
    $lskey_as = $attribs['n'];
    $lsexpand = format_ls($lskey_as);
   }else {
    $lsexpand = null;
   }
   #dbgprintdisp(true,"sthndl.ls. lsexpand=$lsexpand\n");
   $row .= "&nbsp;<span class='ls'>";
  } else if ($el == "lshead") {
   $href = $attribs['href'];
   $row .= "<a href=\"$href\">";
  } else if ($el == "is") {
   $row .= "<span style='font-style: normal; color:teal'>";
  } else if ($el == "bot") {
   $row .= "<span style='color: brown'>";
  } else if ($el == "sic") {
   // no rendering
  } else if ($el == "ab"){
   // handled in chrhndl, but if 'n' attribute is present, use it for expansion
   if (isset($attribs['n'])) {
    $abexpand = $attribs['n'];
   }else {
    $abexpand = null;
   }
  } else {
    $row .= "<br/>&lt;$el&gt;";
  }

  $parentEl = $el;
}
//echo "DEBUG 6\n";

function endhndl($xp,$el) {
// echo "endhndl, $el, $inSanskrit\n";
 global $row,$row1,$pagecol,$inSanskrit;
 global $inkey2;
 global $parentEl;
  $parentEl = "";
  if ($el == "s") {
   $inSanskrit = false;
  } else if ($el == "F") {
   $row .= "]</span>&nbsp;<br/>";
  } else if ($el == "b"){
   $row .= "</strong>"; 
  } else if ($el == "lex"){
   $row .= "</strong>"; 
  } else if ($el == "i"){
   $row .= "</i>"; 
  } else if ($el == "pb"){
   $row .= "<br/>"; 
  } else if ($el == "key2") {
   $inkey2 = false;
  } else if ($el == "symbol") {
  } else if ($el == "div") {
   // close the div span
    $row .= "</span>";
   
  } else if ($el == "alt") {
   // close the span, and introduce line break
   $row .= ")</span><br/>";
  } else if ($el == "sup") {
   $row .= "</sup>";
  } else if ($el == "ls") {
   $row .= "</span>&nbsp;";
  } else if ($el == "is") {
   $row .= "</span>";
  } else if ($el == "bot") {
   $row .= "</span>";
 } else if ($el == "lshead") {
   $row .= "</a>";
  }
}
//echo "DEBUG 7\n";

function chrhndl($xp,$data) {
 global $row,$row1,$pagecol,$inSanskrit;
 global $inkey2;
 global $parentEl,$abexpand,$lsexpand;
 global $accent;
  if ($inkey2) {
   //$data = strtolower($data);
   if (! $accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   $row1 .= "&nbsp;<span class='sdata'><SA>$data</SA></span>";
   //$row1 .= "&nbsp;<span class='sdata'>$data</span>";
  } else if ($parentEl == "key1"){ // nothing printed
  } else if ($parentEl == "pc") {
   $hrefdata = getHrefPage($data);
   //$row1 .= "<span class='hrefdata'> [p= $hrefdata]</span>";
   $row1 .= "<span class='hrefdata'> [Printed book page $hrefdata]</span>";
  } else if ($parentEl == "L") {
   $row1 .= "<span class='lnum'> [Cologne record ID=$data]</span>";
   //$row1 .= "<span class='lnum'> [L=$data]</span>";
  } else if ($parentEl == 's') {
   if (! $accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   $row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($inSanskrit) {
   $row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($parentEl == "hom") {
   /* For stc, we omit showing 'hom'. It is already printed as part of
      The first entry.
   */
   //$row .= "<span class='hom'>$data</span>&nbsp;";
  } else if ($parentEl == 'div') { 
   $row .= $data;
  } else if ($parentEl == 'pb') { 
   $row .= $data;
  } else if ($parentEl == "alt") {
   $row .= $data ;
  } else if ($parentEl == "lang") {
   // Greek typically uncoded
   //$data = $data . ' (greek)';
   $row .= $data;
  } else if ($parentEl == "lex") {
   $tran = getABdata($data);
   $dbg = false;
   dbgprintdisp($dbg,"getABdata: $data -> $tran\n");
   if ($tran == "") {
   $row .= $data;
   }else {
   $row .= "<span  title=\"$tran\">";
   $row .= "$data";
   $row .= "</span>";
   }

  } else if ($parentEl == "ab") {
   // 10-14-2017.
   if ($abexpand == null) {
    $tran = getABdata($data);
   } else {
    $tran = $abexpand;
   }
   $abexpand = null; // reset for next abbreviation
   $dbg = false;
   dbgprintdisp($dbg,"getABdata: $data -> $tran\n");
   if ($tran == "") {
   $row .= "$data";
   }else {
   $row .= "<span  title='$tran' style='text-decoration:underline'>";
   //$row .= "<font color='#006600'>";
   $row .= "$data";
   //$row .= "</font>";
   $row .= "</span>";
   }
  }else if ($parentEl == "ls") { 
   // 10-15-2017x
   //$data1 = format_ls($data);
   #dbgprintdisp(true,"chrhndl.ls. lsexpand=$lsexpand\n");

   if ($lsexpand == null) {
    $data1 = format_ls($data);
   } else { // usual case
    $data1 = $lsexpand;
   }
   $row .= $data1;
  } else { // Arbitrary other text
   $row .= $data;
  }
}

//echo "DEBUG 8\n";

?>
