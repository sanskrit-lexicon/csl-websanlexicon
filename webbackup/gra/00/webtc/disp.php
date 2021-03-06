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
// Dec 14, 2017  pwg specific. (adapted from stc version)
    1. basicDisplaySetAccent  copied from previous pw disp.php.
       Not sure if this is required, but the local 'getword' calls this
       function.  
    2. <lex>  treat as bold
    3. <ls> generate tooltip. Change Case of 'abbreviation' head text.
    4. <is>  IAST-Sanskrit - render with wide spacing
        Don't attempt to change size of numbers.
        Requires 'n' attrib of ls in pwg.xml.
    6. <sic/> Do not render
    7. <ab>  Tooltip based on pwgab.sqlite table. 
             Allow local abbrev not in tab
             A separate database required.
             Also requires addition to dal_sqlite
             getABdata
    dal_pwab -> dal_pwgab
   05-22-2018.  Tooltip shows mandala,hymn (modern notation) 
     See functions rgveda_verse_modern, and rgveda_verse_callback (which is 
     called by line_adjust).
   06-28-2018.  Convert to a class
*/
require_once("dbgprint.php");
class BasicDisplay {

 public $parentEl;
 public $row;
 public $row1;
 public $pagecol;
 public $dbg;
 public $inSanskrit;
 public $inkey2;
 public $accent;
 public $noLit;  // Not used here
 public $table;
public function __construct($key,$matches,$filterin,$dict) {
 $this->pagecol="";
 $this->dbg=false;
 $this->inSanskrit=false;
 $this->table = "<h1>&nbsp;<SA>$key</SA></h1>\n";

 $this->table .= "<table class='display'>\n";
 $ntot = count($matches);
 $i = 0;
 while($i<$ntot) {
  $linein=$matches[$i];
  $line=$linein;
  #dbgprint(true,"disp: line[$i+1]=$line\n");
  $line=trim($line);
  $l0=strlen($line);
  #dbgprint($this->dbg,"call line_adjust: $line\n");
  #$line=$this->line_adjust($line);
  #dbgprint($this->dbg,"back from line_adjust: $line\n");
  $this->row = "";
  $this->row1 = "";
  
  $this->inSanskrit=false;
  $this->inkey2 = false;
  $p = xml_parser_create('UTF-8');
  xml_set_element_handler($p,array($this,'sthndl'),array($this,'endhndl'));
  xml_set_character_data_handler($p,array($this,'chrhndl'));
  xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,FALSE);
  dbgprint($this->dbg,"chk 1\n");
  if (!xml_parse($p,$line)) {
   $row = $line;
   return;
  }
  dbgprint($this->dbg,"chk 2\n");
  xml_parser_free($p);
  dbgprint($this->dbg,"chk 2\n");
  /* May 4, 2017
  $this->table .= "<tr><td class='display' valign=\"top\">$row1</td>\n";
  $this->table .= "<td class='display' valign=\"top\">$row</td></tr>\n";
  */
  $this->table .= "<tr>";
  $this->table .= "<td>";
  $style = "background-color:beige";
  $row1a = "<span style='$style'>{$this->row1}</span>";
  $this->table .= "$row1a\n<br/>{$this->row}\n";
  $this->table .= "</td>";
  // This is so that there will be no need for a horizontal scroll. 12-14-2017
  $this->table .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
  $this->table .= "</tr>";
  $i++;
 }
 $this->table .= "</table>\n";
 #return $this->table;
}


 public function sthndl($xp,$el,$attribs) {

  if (preg_match('/^H.+$/',$el)) {
   // don't display 'H1'
   // $row1 .= "($el)";
  } else if ($el == "s")  {
   $this->inSanskrit = true;
  } else if ($el == "key2"){
   $this->inkey2 = true;
  } else if ($el == "b"){ // bold
   $this->row .= "<strong>"; 
  } else if ($el == "graverse") {
   $this->row .= "<span style='font-size:smaller; font-weight:100'>";
  } else if ($el == "lex"){ // m. f., etc.
   $this->row .= "<strong>"; 
  } else if ($el == "i"){
   $this->row .= "<i>"; 
  } else if ($el == "br"){
   $this->row .= "<br/>";   
  } else if ($el == "h"){
  } else if ($el == "body"){
  } else if ($el == "tail"){
  } else if ($el == "L"){
  } else if ($el == "pc"){
  } else if ($el == "pb"){
   $this->row .= "<br/>";
  } else if ($el == "key1"){
  } else if ($el == "hom"){
  } else if ($el == "F"){
   $this->row .= "<br/>&nbsp;<span class='footnote'>[Footnote: ";
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
   //$this->row .= "<br/>";
   if ($n == 'H') {$indent = "1.0em";}
   else if ($n == 'P') {$indent = "2.0em"; }
   else if ($n == 'P1') {$indent = "3.0em";}
   else {$indent = "";}
   $style="position:relative; left:$indent;";

   $this->row .= "<br/><span style='$style'>";

  } else if ($el == "alt") {
   // Alternate headword
   $style = "font-size:smaller";
   $this->row .= "<span style='$style'>(";
  } else if ($el == "hwtype") {
   // Ignore
  } else if ($el == "sup") {
   $this->row .= "<sup>";
  } else if ($el == "lbinfo") {
    // empty tag.
  } else if ($el == "lang") {
    // nothing special here  Greek remains to be filled in
    $this->row .= " (greek) ";
  } else if ($el == "lb") {
    $this->row .= "<br/>";
  } else if ($el == "C") {
   // vcp specific
   $n = $attribs['n'];
   if ($n == '1') {
    $this->row .= "<br/>";
   }
   $this->row .= "<strong>(C$n)</strong>";
  } else if ($el == "edit"){ // vcp
    // no display
  } else if ($el == "ls") {
   if (isset($attribs['n'])) {
    $n = $attribs['n'];
    $rec = dal_linkpwgauthorities($n);
    list($n0,$code,$codecap,$text) = $rec;
    dbgprint(false,"$n0 ... $code ... $text\n");
    # be sure there is no xml in the text
    $text = preg_replace('/<.*?>/',' ',$text);
    $text = preg_replace('/&/','and',$text);
    $text = preg_replace("|'|"," ",$text);  # apostrophe's in title cause problem
    $this->row .= "<span class='ls' title='$text'>";   
   }else {
    $this->row .= "&nbsp;<span class='ls'>";   
   }
  } else if ($el == "lshead") {
   $href = $attribs['href'];
   $this->row .= "<a href=\"$href\">";
  } else if ($el == "is") {
   #$this->row .= "<span style='font-style: normal; color:teal'>";
   $this->row .= "<span style='letter-spacing:2px;'>"; # this is more like the text
  } else if ($el == "bot") {
   $this->row .= "<span style='color: brown'>";
  } else if ($el == "sic") {
   // no rendering
  } else if ($el == "ab"){
    if (isset($attribs['n'])) {
     $tran = $attribs['n'];
     #dbgprint(true," sthndl. ab. tran=$tran\n");
     #$this->row .= "<span title='$tran' style='text-decoration:underline'>";
     # this style provides a 'dotted underline'
     $style = "border-bottom: 1px dotted #000; text-decoration: none;";
     $this->row .= "<span title='$tran' style='$style'>";
    }else {
     $this->row .= "<span>";
    }
  } else {
    $this->row .= "<br/>&lt;$el&gt;";
  }

  $this->$parentEl = $el;
}

 public function endhndl($xp,$el) {
  $this->$parentEl = "";
  if ($el == "s") {
   $this->inSanskrit = false;
  } else if ($el == "F") {
   $this->row .= "]</span>&nbsp;<br/>";
  } else if ($el == "b"){
   $this->row .= "</strong>"; 
  } else if ($el == "graverse") {
   $this->row .= "</span>";
  } else if ($el == "lex"){
   $this->row .= "</strong>"; 
  } else if ($el == "i"){
   $this->row .= "</i>"; 
  } else if ($el == "pb"){
   $this->row .= "<br/>"; 
  } else if ($el == "key2") {
   $this->inkey2 = false;
  } else if ($el == "symbol") {
  } else if ($el == "div") {
   // close the div span
    $this->row .= "</span>";
   
  } else if ($el == "alt") {
   // close the span, and introduce line break
   $this->row .= ")</span><br/>";
  } else if ($el == "sup") {
   $this->row .= "</sup>";
  } else if ($el == "ls") {
   $this->row .= "</span>&nbsp;";
  } else if ($el == "is") {
   $this->row .= "</span>";
  } else if ($el == "bot") {
   $this->row .= "</span>";
 } else if ($el == "lshead") {
   $this->row .= "</a>";
 } else if ($el == "ab") {
   $this->row .= "</span>";
 }
}

 public function chrhndl($xp,$data) {
  if ($this->inkey2) {
   //$data = strtolower($data);
   if (! $this->accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   $this->row1 .= "&nbsp;<span class='sdata'><SA>$data</SA></span>";
   //$this->row1 .= "&nbsp;<span class='sdata'>$data</span>";
  } else if ($this->$parentEl == "key1"){ // nothing printed
  } else if ($this->$parentEl == "pc") {
   $hrefdata = $this->getHrefPage($data);
   //$this->row1 .= "<span class='hrefdata'> [p= $hrefdata]</span>";
   $this->row1 .= "<span class='hrefdata'> [Printed book page $hrefdata]</span>";
  } else if ($this->$parentEl == "L") {
   $this->row1 .= "<span class='lnum'> [Cologne record ID=$data]</span>";
   //$this->row1 .= "<span class='lnum'> [L=$data]</span>";
  } else if ($this->$parentEl == 's') {
   if (! $this->accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   $this->row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($this->inSanskrit) {
   $this->row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($this->$parentEl == "hom") {
   /* For stc, we omit showing 'hom'. It is already printed as part of
      The first entry.
   */
   //$this->row .= "<span class='hom'>$data</span>&nbsp;";
  } else if ($this->$parentEl == 'div') { 
   $this->row .= $data;
  } else if ($this->$parentEl == 'pb') { 
   $this->row .= $data;
  } else if ($this->$parentEl == "alt") {
   $this->row .= $data ;
  } else if ($this->$parentEl == "lang") {
   // Greek typically uncoded
   //$data = $data . ' (greek)';
   $this->row .= $data;
  } else if ($this->$parentEl == "lex") {
   $tran = getABdata($data);
   $dbg = false;
   dbgprint($dbg,"getABdata: $data -> $tran\n");
   if ($tran == "") {
   $this->row .= $data;
   }else {
   $this->row .= "<span  title=\"$tran\">";
   $this->row .= "$data";
   $this->row .= "</span>";
   }

  } else if ($this->$parentEl == "ab") {
   $this->row .= "$data";
   /* not used 12-14-2017
   $tran = getABdata($data);
   $dbg = false;
   dbgprint($dbg,"getABdata: $data -> $tran\n");
   if ($tran == "") {
   $this->row .= "$data";
   }else {
   $this->row .= "<span  title='$tran' style='text-decoration:underline'>";
   $this->row .= "$data";
   $this->row .= "</span>";
   }
   */
  }else if ($this->$parentEl == "ls") { 
   #$data1 = format_ls($data);
   #$this->row .= $data1;
   $this->row .= $data;
  } else { // Arbitrary other text
   $this->row .= $data;
  }
}
public function getHrefPage($data) {
/* getHrefPage generates markup for the link to a program which displays a pdf, as
 specified by the  input argument '$data'.
 In this implementation, the program which serves the pdf is
 $serve = ../webtc/servepdf.php.
 $data is assumed to be a string with a comma-delimited list of page numbers,
 only the first of which is used to generate a link.
 The markup returned for a given $lnum in the list $data is
   <a href='$serve?page=$lnum' target='_Blank'>$lnum</a>
 It is up to $serve to associate $lnum with a file.

*/
  $ans="";
 //$lnums = preg_split('/[,-]/',$data);
 $lnums = preg_split('/[,]/',$data);  //%{pfx}
 $serve = "../webtc/servepdf.php";
 foreach($lnums as $lnum) {
  list($page,$col) =  preg_split('/[-]/',$lnum);
  $lnumref=$lnum;
  $ipage = intval($page);

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

} ## end of class 
?>
