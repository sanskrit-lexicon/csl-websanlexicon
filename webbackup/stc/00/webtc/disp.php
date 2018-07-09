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
*/
require_once("disp_servepdf.php");

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
 if (strlen($line) == 0) {return "line_adjust err @ 4";}
 //$line = preg_replace('|- <br/>|','',$line);
 //$line = preg_replace('|<br/>|',' ',$line);
 return $line;
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
   $n=$attribs['n'];
   $row .= "<br/>";
   // for stc
   if ($n == 'P') {
    $style="position:relative; left:1.5em;";
    $row .= "<br/><span style='$style'>";
   }
   /*
   if (($n == '2')) {
    $style="position:relative; left:1.5em;";
    $row .= "<br/><span style='$style'>";
   } else if (($n == 'P')) {
    $style="";
    $row .= "<br/><span style='$style'>";
   } else {
    // e.g. n="3"
    $style="";
    $row .= "<br/><span style='$style'>";
   }
   */
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
  }
}
//echo "DEBUG 7\n";

function chrhndl($xp,$data) {
 global $row,$row1,$pagecol,$inSanskrit;
 global $inkey2;
 global $parentEl;
  if ($inkey2) {
   //$data = strtolower($data);
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
  } else { // Arbitrary other text
   $row .= $data;
  }
}

//echo "DEBUG 8\n";

?>
