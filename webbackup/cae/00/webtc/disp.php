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
// Oct 30, 2017  cae specific. (adapted from pw version)
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

$parentEl;
$row;
$row1;
$pagecol = "";
$dbg = false;
$inSanskrit=false;
$inkey2;
$dbg=false;

//echo "DEBUG 1\n";
function monierSetNoLit($value) {
 // This function has no effect now (May 4, 2017)
 $noLit = $value;
}
//echo "DEBUG 2\n";
function basicDisplaySetAccent($accentin){
 global $accent;
 if ($accentin == 'yes') {
  $accent = True;
 }else {
  $accent = False;
 }
}
function basicDisplay($key,$matches,$filterin) {
 global $row,$row1,$pagecol,$inSanskrit,$dbg;
 global $inkey2;
 global $parentEl;
 
 $table = "<h1>&nbsp;<SA>$key</SA></h1>\n";

 $table .= "<table class='display'>\n";
 $ntot = count($matches);
 //echo "<p>$ntot lines for $key</p>\n";
 $i = 0;
 while($i<$ntot) {
  $linein=$matches[$i];
  $line=$linein;
  if ($dbg) {
   echo "<!--line $i = $line -->\n";
  }
  $line=trim($line);
  $l0=strlen($line);
  $line=line_adjust($line);
  $row = "";
  $row1 = "";
  
  $inSanskrit=false;
  $inkey2 = false;
//   $line = preg_replace('/</','&lt;',$line);
//   $line = preg_replace('/>/','&gt;',$line);
//   echo "<p>The underlying data for this line:<br/>$line</p>\n";
  if ($dbg) {echo "<!--begin parse $i=\n$line\n\n-->";}
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
  if ($dbg) {echo "<!--after line, row1=$row1\n\nrow=$row1\n\n-->";}
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

function s_callback($matches) {
/* no special coding for Sanskrit in <s>X</s> form.
    So, just remove the <s>,</s> elements
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
 $line = preg_replace_callback('|<s>(.*?)</s>|',"s_callback",$line);
 $line = preg_replace("|\[Page.*?\]|",  "<pb>$0</pb>",$line);

 $line = preg_replace('/<pc>Page(.*)<\/pc>/',"<pc>\\1</pc>",$line);
 if (strlen($line) == 0) {return "line_adjust err @ 2";}

 if (preg_match('/<pc>(.*)<\/pc>/',$line,$matches)){
  if($pagecol == $matches[1]){
   $line = preg_replace('/<pc>(.*)<\/pc>/','',$line);
  }else {$pagecol = $matches[1];}
 }
 // May 5, 2017. render '--' as mdash  
 // The next form '&mdash;' generated a parsing error.
 //$line = preg_replace('/--/','&mdash;',$line);
 // This form of mdash works fine.
 // It might be better to do this transformation either in acc.txt or
 // in acc.xml.
 //$line = preg_replace('/--/','&#8212;',$line);
 $line = preg_replace_callback('/<ls>(.*?)<\/ls>/',
      "line_adjust1_callback",$line);
 if (strlen($line) == 0) {return "line_adjust err @ 4";}
 //$line = preg_replace('|- <br/>|','',$line);
 //$line = preg_replace('|<br/>|',' ',$line);
 return $line;
}
function dbgprintdisp($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbgdisp.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
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
     // {} are used instead of <>; the <> are returned in chrhdl.
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
function format_ls($data) {
 // idea suggested by Marcis Gasyun. May 10, 2014
 //return $data; // dbg - turn this off
 // This logic assumes that $data is in AS (number-letter) coding
 $ansarr = array();
 $parts = preg_split("|([A-Z][A-Z0-9.]+)|",$data,-1,
   PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
 for($i=0;$i<count($parts);$i++) {
  $part = $parts[$i];
  if (preg_match("|^[A-Z]|",$part)) { 
   //assume this is the ls name
   $part1 = ucfirst(strtolower($part));
   $ans1 = transcoder_processString($part1,"as","roman1");
   $ansarr[] = $ans1;
   continue;
  }else if (preg_match("|^[a-z]|",$part)){
   // Non-numerical text
   $ans1 = transcoder_processString($part,"as","roman1");
   $ans1 = "<span class='display'>$ans1</span>";
   $ansarr[] = $ans1;
   continue;
  }
  // assum $part is a sequence of 1 or more subrefs, separated by period
  $subparts = preg_split("|[.]|",$part);
  $subansarr = array();
  foreach($subparts as $subpart){
   $subansarr[] = format_subpart($subpart);
  }
  $subans = join('.',$subansarr);
  $ansarr[] = $subans;
  // echo "\n"; // commented out Dec 16, 2015
 }
 $ans1 = join('',$ansarr);
 return $ans1;
}
function format_subpart($x) {
 $parts = preg_split('|,|',$x);
 $ansarr=array();
 for($i=0;$i<count($parts);$i++) {
  $part = $parts[$i];
  if ($i==0) {
   $ans0 = $part;
  }else if ($i == 1) {
   $ans0 = "<span style='font-size:90%'>$part</span>";
  }else if ($i == 2) {
   $ans0 = "<span style='font-size:80%'>$part</span>";
  }else if ($i >= 3) {
   $ans0 = "<span style='font-size:70%'>$part</span>";
  }

  if (preg_match("|^ *[a-z]|",$ans0)){
   $ans1 = transcoder_processString($ans0,"as","roman");
   $ans1 = "<span class='display'>$ans1</span>";
   dbgprintdisp($dbg,"i=$i, part=$part, ans0=$ans0,ans1=$ans1\n");
   $ans0=$ans1;
  }

  $ansarr[] = $ans0;
 }
 return join(',',$ansarr);
}

function line_adjust1_callback($matches) {
 $lskey = $matches[1]; // content of <ls> tab

 $lsdata = getLSdata($lskey);
 if (!$lsdata) {
  $ans = $matches[0];
 }else {
 // $lsdata 
 // 10-09-2017 convert $lskey from IAST to AS.
 // This is required so that format_ls will work properly
 $lskey_as = transcoder_processString($lskey,"roman1","as");
 $ans = "<lshead $lsdata><ls>$lskey_as</ls></lshead>";
 }
 dbgprintdisp($dbg,"line_adjust1_callback: ans=$ans\n");
 return $ans;  
}
function getABdata($key) {
 global $dispfilter;
 // abbreviation tool tips.
 $ans="";
 $result = dal_caeab($key);
 $dbg=false;
 dbgprintdisp($dbg,"getABdata: key = '$key', len result=" . count($result) . "\n");
 if (count($result) == 1) {
  list($key1,$data) = $result[0];
  dbgprintdisp($dbg,"getABdata: key1='$key1', data='$data'\n");
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
 global $parentEl;

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
   // handled in chrhndl
   } else if ($el == "vlex"){
   // no display
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
 global $parentEl;
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
   $tran = getABdata($data);
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
   $data1 = format_ls($data);
   $row .= $data1;
  } else { // Arbitrary other text
   $row .= $data;
  }
}

//echo "DEBUG 8\n";

?>
