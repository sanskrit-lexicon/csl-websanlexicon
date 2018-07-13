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
    01-28-2018 Some adjustments for krm
    02-10-2018 Some adjustments for inm
         "F", "div" in sthndl 
    02-13-2018 Some adjustments for pe
         "div", "Poem"
    02-15-2018 Some adjustments for pgn
         "div". This does not indent as expected. This seems to 
                be an undesired effect of the use of empty div tag.
    02-16-2018. pui code copied from pgn.
                Altered "F" in endhndl
    03-30-2017. mw code
      getABdata: dal_pwgab -> dal_mwab
      sthndl: "s1", "etym", "info", "lang", "Hx", 
              "root", "to", "pb",
      endhndl: "pb"
      chrhndl: "hom". Don't display the key2 part
      line_adjust: remove hom from key2, otherwise, it prints twice.
      $row1x:  for whitney, westergaard links, if any
      $pagecol variable not needed in sthndl, endhndl, chrhndl. Should 
        remove from globals in those functions.      
      $inlex:  Added this to globals to examine text fragments within
               <lex> tag for abbreviation add-ons. parentEl not sufficient.
               Sometimes, in chrhndl, the parentEl is empty
*/
require_once("disp_servepdf.php");
require_once("dal_sqlite.php");
$parentEl; $inlex;
$row;
$row1;
$row1x;
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
 global $row,$row1,$row1x,$pagecol,$inSanskrit,$dbg;
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
  $row = "";
  $row1 = "";
  $row1x=extra_line($line);
  if ($dbg) {
   echo "<!--line $i = $line -->\n";
  }
  $line=trim($line);
  $l0=strlen($line);
  $line=line_adjust($line);
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
  $row1a = "";
  if ($row1 != "") {
   $row1a = "<span style='$style'>$row1</span>";
  }
  if ($row1x != "") {
   $row1a = "$row1a<br/>\n$row1x";
  }
  if ($row1a != "") {
   $table .= "$row1a<br/>\n$row\n";
  }else {
   $table .= "$row\n";
  }
  $table .= "</td>";
  // This is so that there will be no need for a horizontal scroll. 12-14-2017
  $table .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
  $table .= "</tr>";
  $i++;
 }
 $table .= "</table>\n";
 if(false) {  //04-17-2018 debugging. OK to remove later
  echo "DEBUG in disp.php<br/>";
  $tablines = explode("\n",$table); 
  $ntablines = count($tablines);
  $outarr = array();
  $outarr[] = "$key";
  for ($i=0;$i<$ntablines;$i++) {
   $outarr[] = $tablines[$i];
  }
  $outarr[] = " ------------------------------------";
  $out = join("\n",$outarr);
  dbgprintdisp(true,"$out\n");

 }
 return $table;
}

function unused_s_callback($matches) {
/* no special coding for Sanskrit in <s>X</s> form.
    So, just remove the <s>,</s> elements
*/
 $x = $matches[0];
 $x = preg_replace("|(\[Page.*?\])|","</s> $0 <s>",$x);
 return $x;
}
function adjust_attrib_text($text) {
 //There are restrictions to text in an attribute. 
 // This might be able to be avoided by tailoring the
 // expansions in the abbreviations database for the dictionary (e.g. mwab.sqlite)
 $text = preg_replace('/<.*?>/',' ',$text);
 $text = preg_replace('/&/','&amp;',$text);
 # apostrophe's in title cause problem not sure why. 
 # Double quotes work ok.
 $text = preg_replace("|'|","&quot;",$text);  
 return $text;
}
function extra_line($line) {
 /* Currently only used in mw for links to Whitney and Westergaard.
  Based on <info whitneyroots="X"/> or <info westergaard="X"/>
 */
 $ans1=""; // whitney
 $ans2=""; // westergaard
 if (preg_match('|<info whitneyroots="(.*?)"/>|',$line,$matches)) {
  $x = $matches[1];
  $href0="http://www.sanskrit-lexicon.uni-koeln.de/scans/KALEScan/WRScan/disp2/index.php";
  $results = preg_split("|;|",$x);
  $elts=array();
  foreach ($results as $rec) {
   list($whitkey,$whitpage) = preg_split("|,|",$rec);
   $href = "$href0" . "?page=$whitpage";
   $whitkey1 = $whitkey; 
   $whitkey2 = "";
   if (preg_match('|^([^1-9]*)([1-9]*)$|',$whitkey,$matches)) {
    $whitkey1 = $matches[1];
    $whitkey2 = $matches[2];
   }
   $elt = "<a href='$href' target='_Whitney'><SA>$whitkey1</SA>$whitkey2</a>";
   $elts[] = $elt;
  }
  $ans1a = join(", ",$elts);
  $ans1 = "<em>Whitney Roots links:</em> " . $ans1a;
 }
 if (preg_match('|<info westergaard="(.*?)"/>|',$line,$matches)) {
  $x = $matches[1];
  $href0="http://www.sanskrit-lexicon.uni-koeln.de/scans/MWScan/Westergaard/disp/index.php";
  $results = preg_split("|;|",$x);
  $elts=array();
  foreach ($results as $rec) {
   list($westkey,$westsutra,$madhaviyasutra) = preg_split("|,|",$rec);
   // westsutra is of form (section.rootnum)
   // our links require the section
   list($westsection,$westrootnum) = preg_split("|[.]|",$westsutra);
   $href = "$href0" . "?section=$westsection";
   $elt = "<a href='$href' target='_Westergaard'>$westsutra</a>";
   $elts[] = $elt;
  }
  $ans2a = join(", ",$elts);
  $ans2 = "<em>Westergaard Dhatupatha links:</em> " . $ans2a;
 }
 if (($ans1 != "") && ($ans2 != "")) {
  $ans = "$ans1&nbsp;&nbsp;&nbsp;&amp;&nbsp;$ans2";
 }else {
  $ans = "$ans1$ans2";
 }
 return $ans;
}
function unused_vlex_callback($matches) {
/* <vlex.*?>X</vlex>
  04-12-2018.  Now unused. vlex tag removed
*/
 $x = $matches[1];
 $x = preg_replace("/(cl|Ā|P)[.]/","<ab>$1.</ab>",$x);
 # drop the vlex tag.
 $dbg=false;
 $orig=$matches[0];
 dbgprintdisp($dbg,"vlex_callback: $orig -> $x\n");
 return $x;
}
function line_adjust($line) {
 global $pagecol;
 $dbg = false;
 #dbgprintdisp($dbg,"krm. line_adjust Begin. line=\n$line\n");
 $line = preg_replace('/¦/',' ',$line);
 #$line = preg_replace_callback('|<s>(.*?)</s>|',"s_callback",$line);
 $line = preg_replace("|\[Page.*?\]|",  "<pb>$0</pb>",$line);
 # for mw
 $line = preg_replace("|<key2>(.*?)<hom>.*?</hom>(.*?<body>)|","<key2>$1$2",$line);

 $line = preg_replace('/<pc>Page(.*)<\/pc>/',"<pc>\\1</pc>",$line);
 if (strlen($line) == 0) {return "line_adjust err @ 2";}
 /* global $pagecol remembers <pc>X</pc>. 
   In case current X is same as previous X (from variable $pagecol),
   we remove this element
 */
 if (preg_match('/<pc>(.*)<\/pc>/',$line,$matches)){
  if($pagecol == $matches[1]){
   // pc same as last time
   $line = preg_replace('/<pc>(.*)<\/pc>/','',$line);
  }else {$pagecol = $matches[1];} // save current page col
 }
 
 /*  mw: convert ls code into an abbreviation.
     //"<ls n='\1'>\2</ls>",$line);
 */
 $line = preg_replace_callback('|<ls>([A-ZĀĪŚṚṢṬ][A-Za-zÂáâêîñôĀāĪīŚśūûḍḥṃṅṇṉṚṛṢṣṬṭ.]*[.])(.*?)</ls>|', "ls_callback",$line);  
 // handle the frequent <ls>ib.xxx</ls> by marking ib. as abbreviation
 $line = preg_replace('|<ls>ib[.]|','<ls><ab>ib.</ab>',$line);    
   
 /* for mw  add abbreviation within vlex
  04-12-2017: Not needed. vlex removed
 $line = preg_replace_callback('|<vlex.*?>(.*?)</vlex>|',"vlex_callback",$line);
 */
 /* 12-15-2017  Don't use this for now
    Generate 'ab' markup.  This may be later replaced by having the
    <ab> markup already in pwg.xml
 $line = add_ab_markup($line);
 */
 /* 12-14-2017
  'local' abbreviation handled here. Generate an n attribute if one
   is not present
 */
 $line = preg_replace_callback('|<ab(.*?)>(.*?)</ab>|',"abbrv_callback",$line);
 if (strlen($line) == 0) {return "line_adjust err @ 4";}
 //$line = preg_replace('|- <br/>|','',$line);
 //$line = preg_replace('|<br/>|',' ',$line);

 /* 04-12-2018. For MW. Logic to place Cologne record ID at END
  of displays for <H1X> records. This acomplished by changing the
  name of the <L> tag to <L1>
 */
 if (preg_match('|<(H[1-4].)>.*(<L>.*?</L>)|',$line,$matches)) {
  $H = $matches[1];
  $Ltag = $matches[2];
  // remove L element
  $line = preg_replace("|$Ltag|","",$line);
  // construct L1 tag
  $L1tag = preg_replace("|L>|","L1>",$Ltag);
  #dbgprintdisp(true,"Ltag=$Ltag,  L1tag=$L1tag\n");
  // Insert L1tag before end of tail -- so at end of display
  $line = preg_replace("|</tail>|","$L1tag</tail>",$line);
 }
 /* for MW, don't display lex when type="inh" 
  04-13-2018.  Inherited lex element has been removed.
  It is now present as '<info lex="inh"/>'
 $line = preg_replace('|<lex type="inh">.*?</lex>|','',$line);
 */
 
 /* for MW, */
 #dbgprintdisp(true,"line_adjust End. line=\n$line\n");
 return $line;
}
function add_ab_markup_helper($x) {
 $known_abs = array("N." => "N.", "vgl." => "Vgl.","Vgl." => "Vgl.");
 $parts = preg_split("|( )|",$x,-1,PREG_SPLIT_DELIM_CAPTURE);
 $outparts = [];
 foreach($parts as $part) {
  if ($part == ' ') {
   $outparts[] = $part;
   continue;
  }
  if (isset($known_abs[$part])) {
   $newpart = $known_abs[$part];
   $outparts[] = "<ab>$newpart</ab>";
   continue;
  }
  // Default Not an abbreviation.
  $outparts[] = $part;
 }
 // reconstruct line
 $ans = join('',$outparts);
 return $ans;
 
}
function add_ab_markup($line) {
 $dbg=false;
 // First, split on <ls>.  We only add markup OUTSIDE of <ls>
 $parts = preg_split("|(<ls.*?>.*?</ls>)|",$line,-1,PREG_SPLIT_DELIM_CAPTURE);
 $outparts = [];
 foreach($parts as $part) {
  if (preg_match('|^<ls|',$part)) {
   $outparts[] = $part;
  }else {
   $outparts[] = add_ab_markup_helper($part);
  }
 }
 dbgprintdisp($dbg,"add_ab_markup: line=\n$line\n");
 // reconstruct line
 $ans = join('',$outparts);
 dbgprintdisp($dbg,"add_ab_markup: ans=\n$ans\n");
 return $ans;
}
function ls_callback($matches) {
 /* <ls>AR</ls>  A = $abbrv, R = $rest */
 $abbrv = $matches[1];
 $rest = $matches[2];
 $dbg=false;
 dbgprintdisp($dbg,"ls_callback: abbrv=$abbrv, rest=$rest\n");
 # $recs is an array of matches (we expect of length 1)
 $recs = dal_mwauthtooltips($abbrv);
 dbgprintdisp($dbg,"  ls_callback: recs is of length" . count($recs) . "\n");
 if (count($recs) != 1) { // unknown abbreviation
  $title = "Unknown literary source";
  $type = "Unknown type";
 } else {
  $rec = $recs[0];
  list($cid,$abbrv1,$title,$type) = $rec;
 }
 $tooltip = "$title ($type)";
 // The tooltip might be malformed for an html attribute. Try to fix
 $tooltip = adjust_attrib_text($tooltip);

 # reconstruct the ls element with an n attribute
 $lsnew = "<ls n='$tooltip'>$abbrv$rest</ls>";
 dbgprintdisp($dbg,"  lsnew=$lsnew\n");
 return $lsnew;
 /*  another way it might be done
 # create a 'local' abbreviation
 $ab = "<ab n='$tooltip'>$abbrv</ab>";
 # reconstruct the ls element
 $lsnew = "<ls>$ab$rest</ls>";
 return $lsnew;
 */
}
function abbrv_callback($matches) {
 /* <ab n="{tran>}">{data}</ab>
  <ab{attrib}>{data)</ab>
 */
 $x = $matches[0]; // full string
 $a = $matches[1];
 $data = $matches[2];
 $dbg=false;
 dbgprintdisp($dbg,"abbrv_callback: a=$a, data=$data\n");
 if(preg_match('/n="(.*?)"/',$a,$matches1)) {
  dbgprintdisp($dbg," abbrv_callback case 1\n");
  $ans = $x;
 }else {
  $tran = getABdata($data);  
  $tran = adjust_attrib_text($tran);
  $ans = "<ab n='$tran'>$data</ab>";
  dbgprintdisp($dbg," abbrv_callback case 2\n");
 }
 dbgprintdisp($dbg," abbrv_callback returns $ans\n");
 return $ans;
}
function dbgprintdisp($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbgdisp.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}
function unused_getLSdata($lskey) {
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
function unused_getLSdata_helper($ls) {
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
  dbgprintdisp($dbg,"Calling dal_linkpwgauthorities with $abbrvtest\n");
  $data = dal_linkpwgauthorities($abbrvtest);
  dbgprintdisp($dbg,"return dal_linkpwgauthorities with $abbrvtest\n");
  if ($data) {
   $ans = $abbrvtest;
  }
 }
 return $ans;
}
function unused_format_ls($data) {
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
function unused_format_subpart($x) {
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

function unused_line_adjust1_callback($matches) {
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
 $result = dal_mwab($key);
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
 global $row,$row1,$row1x,$pagecol,$inSanskrit;
 global $inkey2,$inlex;
 global $parentEl;
 #dbgprintdisp(true,"entering element $el\n");
  if (preg_match('/^H.+$/',$el)) {
   // don't display 'H1'. For mw, do display
   // However, don't display HxA, HxB, HxC (? see 'agre')
   if (preg_match('|^H[1-4]$|',$el)) {
     $row1 .= "($el)";
   }
  } else if ($el == "s")  {
   $inSanskrit = true;
  } else if ($el == "key2"){
   $inkey2 = true;
  } else if ($el == "b"){ // bold
   $row .= "<strong>"; 
  } else if ($el == "lex"){ // m. f., etc.
   $row .= "<strong>"; 
   $inlex = true;
   #dbgprintdisp(true,"Beginning lex element\n");
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
   // Do nothing for mw
   #$row .= "<br/>";
  } else if ($el == "key1"){
  } else if ($el == "hom"){
  } else if ($el == "F"){
   #$row .= "<br/>&nbsp;<span class='footnote'>[Footnote: ";
   $style = "font-weight:bold;";
   $row .= "<br/><span style='$style'>[Footnote: </span>";
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
   /*
   if ($n == '1') {$indent = "1.0em";}
   else if ($n == '2') {$indent = "2.0em"; }
   else if ($n == '3') {$indent = "3.0em";}
   else {$indent = "";}
   $style="position:relative; left:$indent;";
   $row .= "<br/><span style='$style'>";
   */
   /*
   // For krm
   if ($n == "F") {
    $row .= "<br/><b>Footnote </b>";
   } else {
    $row .= "<br/>";
   }
   */
   /* For inm
   if ($n == 'P') {$indent = "1.0em";}
   else {$indent = "";}
   $style="position:relative; left:$indent;";
   $row .= "<br/><span style='$style'>";
   */
   /*
   // for pe.  The tag is empty, so we can't add a span that is closed later.
   if ($n == 'P') {$indent = "1.0em";}
   else {$indent = "";} // $n = "lb" or "NI" - no indent
   $style="position:relative; left:$indent;";
   $br = "<br/>";
   if ($n == "NI") {$br = "<br/><br/>";}  // extra break
   $row .= "$br<span style='$style'></span>";
   */
   /*
   // for pgn.  The tag is empty, so we can't add a span that is closed later.
   if ($n == 'P') {$indent = "2.0em";}
   else if ($n == 'HI') {$indent = "2.0em";}
   else {$indent = "0.0em";} // $n = "lb" 
   $style="position:relative; left:$indent;";
   if ($n == 'P') {$style="margin-left:20px;";}
   $br = "<br style='$style'/>";
   $row .= "<br/>";
   */
   // pui
   $row .= "<br/>";   
  } else if ($el == "alt") {
   // Alternate headword
   $style = "font-size:smaller";
   $row .= "<span style='$style'>(";
  } else if ($el == "hwtype") {
   // Ignore
  } else if ($el == "sup") {
   $row .= '<sup style="font-weight:bold;">';
  } else if ($el == "note") {
   // no action currently. For krm.
  } else if ($el == "Poem") {
   $style = "position:relative; left:3.0em;";
   $row .= "<br/><div style='$style'>";


  } else if ($el == "lbinfo") {
    // empty tag.
  } else if ($el == "lang") {
    // nothing special here. For mw, just show the text. 
    #$row .= " (greek) ";  
  } else if ($el == "lb") {
    $row .= "<br/>";
  } else if ($el == "C") {
   $n = $attribs['n'];
   /*
   // vcp specific
   if ($n == '1') {
    $row .= "<br/>";
   }
   */
   $row .= "<strong>(C$n)</strong>";
  } else if ($el == "edit"){ // vcp
    // no display
  } else if ($el == "ls") {
   if (isset($attribs['n'])) {
    $text = $attribs['n'];
    #$rec = dal_linkpwgauthorities($n);
    #list($n0,$code,$codecap,$text) = $rec;
    #dbgprintdisp(false,"$n0 ... $code ... $text\n");
    # be sure there is no xml in the text
    $text = adjust_attrib_text($text);
    # 04-25-2018. experimental addition to style. Same as for abbreviations
    $style = "border-bottom: 1px dotted #000; text-decoration: none;";
    $row .= "<span class='ls' title='$text' style='$style'>";   
   }else {
    $row .= "&nbsp;<span class='ls'>";   
   }
  } else if ($el == "lshead") {
   $href = $attribs['href'];
   $row .= "<a href=\"$href\">";
  } else if ($el == "is") {
   #$row .= "<span style='font-style: normal; color:teal'>";
   $row .= "<span style='letter-spacing:2px;'>"; # this is more like the text
  } else if ($el == "bot") {
   $row .= "<span style='color: brown'>";
  } else if ($el == "bio") {
   $row .= "<span style='color: brown'>";
  } else if ($el == "sic") {
   // no rendering
  } else if ($el == "ab"){
    if (isset($attribs['n'])) {
     $tran = $attribs['n'];
     #dbgprintdisp(true," sthndl. ab. tran=$tran\n");
     #$row .= "<span title='$tran' style='text-decoration:underline'>";
     # this style provides a 'dotted underline'
     $style = "border-bottom: 1px dotted #000; text-decoration: none;";
     $row .= "<span title='$tran' style='$style'>";
    }else {
     $row .= "<span>";
    }
  } else if ($el == "s1") {
  } else if ($el == "etym") {
    $row .= "<i>";
  } else if ($el == "info") { // mw no action
  } else if ($el == "to") { // mw no action
  } else if ($el == "ns") { // mw no action
  } else if ($el == "shortlong") { // mw no action
  } else if ($el == "srs") { // mw no action. Different from previous version.
  } else if ($el == "pcol") { // mw no action. Different from previous version.
  } else if ($el == "L1") { // Action in chrhndl
  #} else if ($el == "vlex") { // no overt change
  #} else if ($el == "root") { // put sqrt symbol
  #  $row .= " &radic;";
  } else {
    $row .= "<br/>&lt;$el&gt;";
  }

  $parentEl = $el;
}
//echo "DEBUG 6\n";

function endhndl($xp,$el) {
// echo "endhndl, $el, $inSanskrit\n";
 global $row,$row1,$row1x,$pagecol,$inSanskrit;
 global $inkey2,$inlex;
 global $parentEl;
 #if ($el == 'lex') {
 # dbgprintdisp(true,"leaving element $el\n");
 #}
  $parentEl = "";
  if ($el == "s") {
   $inSanskrit = false;
  } else if ($el == "F") {
   #$row .= "]</span>&nbsp;<br/>";
   $row .= "]</span>";
  } else if ($el == "b"){
   $row .= "</strong>"; 
  } else if ($el == "lex"){
   $row .= "</strong>"; 
   $inlex = false;
   #dbgprintdisp(true,"endhndl: Setting inlex to $inlex\n");
  } else if ($el == "i"){
   $row .= "</i>"; 
  } else if ($el == "pb"){
   // do nothing for mw
   #$row .= "<br/>"; 
  } else if ($el == "key2") {
   $inkey2 = false;
  } else if ($el == "symbol") {
  } else if ($el == "div") {
   // close the div span
    #$row .= "</span>";
   
  } else if ($el == "alt") {
   // close the span, and introduce line break
   $row .= ")</span><br/>";
  } else if ($el == "sup") {
   $row .= "</sup>";
  } else if ($el == "Poem") {
   $row .= "</div>";
  } else if ($el == "ls") {
   #$row .= "</span>&nbsp;";
   $row .= "</span>";
  } else if ($el == "is") {
   $row .= "</span>";
  } else if ($el == "bot") {
   $row .= "</span>";
  } else if ($el == "bio") {
   $row .= "</span>";
 } else if ($el == "lshead") {
   $row .= "</a>";
 } else if ($el == "ab") {
   $row .= "</span>";
 } else if ($el == "etym") {
    $row .= "</i>";
 }
}
//echo "DEBUG 7\n";

function chrhndl($xp,$data) {
 global $row,$row1,$row1x,$pagecol,$inSanskrit;
 global $inkey2,$inlex;
 global $parentEl;
 global $accent;
 #dbgprintdisp(true,"chrhndl: parent=$parentEl, data=$data\n");
  if ($inkey2) {
   //$data = strtolower($data);
   if (! $accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   # for mw, don't add this to $row1.
   #$row1 .= "&nbsp;<span class='sdata'><SA>$data</SA></span>";
   //$row1 .= "&nbsp;<span class='sdata'>$data</span>";
  } else if ($parentEl == "key1"){ // nothing printed
  } else if ($parentEl == "pc") {
   $hrefdata = getHrefPage($data);
   //$row1 .= "<span class='hrefdata'> [p= $hrefdata]</span>";
   $row1 .= "<span class='hrefdata'> [Printed book page $hrefdata]</span>";
  } else if ($parentEl == "L") {
   $row1 .= "<span class='lnum'> [Cologne record ID=$data]</span>";
   //$row1 .= "<span class='lnum'> [L=$data]</span>";
  } else if ($parentEl == "L1") {  // for MW only
   $row .= "<span class='lnum' style='background-color:beige;'> [ID=$data]</span>";
  } else if ($parentEl == 's') {
   if (! $accent) {
    $data = preg_replace('|[\/\^\\\]|','',$data);
   }
   $row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($inSanskrit) {
   $row .= "<span class='sdata'><SA>$data</SA></span>";
  } else if ($parentEl == "hom") {
   /* For mw, we show 'hom'. */
   $row .= "<span class='hom' title='Homonym'>$data</span>";
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
  #} else if (($parentEl == "lex") || ($parentEl == "vlex")) { //?
  } else if (($inlex) && (($parentEl == "lex") || ($parentEl == ""))) { 
   # add abbreviation tooltips as needed. This logic is quite specialized
   # but seems to work in most cases.
   # parentEl can be empty string when a text node after another element
   #dbgprintdisp(true,"chrhndl inlex parent=$parentEl : '$data'\n");
   $tran = getABdata($data);
   if ($tran == "") {
    $data1 = trim($data); // remove spaces at ends
    $data1 = preg_replace('|[.]|','',$data1);
    $data1 = preg_replace('|\(.*$|','',$data1);
    $data1 = "$data1."; # add period
    $tran = getABdata($data1);
   }
   if ($tran == "") {
    $data1 = trim($data); // remove spaces at ends
    $data1 = preg_replace('|[.]|','',$data1);
    $data1 = preg_replace('|^.*\)|','',$data1);
    $data1 = "$data1."; //add period at end 
    $tran = getABdata($data1);
   }
   if ($tran == "") {
    #dbgprintdisp(true,"chrhndl parent=$parentEl problem: data='$data', data1='$data1'\n");
    $row .= $data;
   }else {
    $tran = adjust_attrib_text($tran);
    $row .= "<span  title=\"$tran\">";
    $row .= "$data";
    $row .= "</span>";
   }

  } else if ($parentEl == "ab") {
   $row .= "$data";
   /* not used 12-14-2017
   $tran = getABdata($data);
   $tran = adjust_attrib_text($tran);
   $dbg = false;
   dbgprintdisp($dbg,"getABdata: $data -> $tran\n");
   if ($tran == "") {
   $row .= "$data";
   }else {
   $row .= "<span  title='$tran' style='text-decoration:underline'>";
   $row .= "$data";
   $row .= "</span>";
   }
   */
  }else if ($parentEl == "ls") { 
   #$data1 = format_ls($data);
   #$row .= $data1;
   $row .= $data;
  } else { // Arbitrary other text
   $row .= $data;
  }
}

//echo "DEBUG 8\n";

?>
