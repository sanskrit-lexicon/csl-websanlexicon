<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php 
/*basicadjust.php
BasicAdjust class  Takes a parameter object
   and a list of xml records from the dictionary database,
   and adjust each of these records so it is ready for the BasicDisplay.
   This was formerly done by the BasicDisplay class in line_adjust function.
   The hope is to have dictionary specific code in this BasicAdjust class,
   and to have the BasicDisplay class to be identical for all dictionaries.
*/
require_once('dal.php');
require_once('dbgprint.php');
class BasicAdjust {
 public $getParms;
 public $adjxmlrecs;
 public function __construct($getParms,$xmlrecs) {
  $dict = $getParms->dict;
  $key = $getParms->key;
  require_once("dal.php");  
  #$dal = new Dal($dict);
  $this->getParms = $getParms;
  $adjxmlrecs = array();
  #$i = 0;
  foreach($xmlrecs as $line) {
   $line1 = $this->line_adjust($line);
   $this->adjxmlrecs[] = $line1;
   #$i = $i + 1;
   #dbgprint(true,"adjline[$i]=$line1\n");
  }
  
 }
 public function line_adjust($line) {
 $dbg = false;
 $line = preg_replace('/¦/',' ',$line);
 $line = preg_replace_callback('|<s>(.*?)</s>|','BasicAdjust::s_callback',$line);
 $line = preg_replace("|\[Page.*?\]|",  "<pb>$0</pb>",$line);

 $line = preg_replace('/<pc>Page(.*)<\/pc>/',"<pc>\\1</pc>",$line);

 if (preg_match('/<pc>(.*)<\/pc>/',$line,$matches)){
  if($this->pagecol == $matches[1]){
   $line = preg_replace('/<pc>(.*)<\/pc>/','',$line);
  }else {$this->pagecol = $matches[1];}
 }
 /* removed 12-14-2017
 $line = preg_replace_callback('/<ls(.*?)>(.*?)<\/ls>/',
      "line_adjust1_callback",$line);
 */
 /*  Replace the 'title' part of a known ls with its capitalized form
     This is probably particular to pwg and/or pw 
 */
 $line = preg_replace_callback('|<ls n="(.*?)">(.*?)</ls>|',
      "BasicAdjust::ls_callback",$line);

 /* 12-15-2017  Don't use this for now
    Generate 'ab' markup.  This may be later replaced by having the
    <ab> markup already in pwg.xml
 $line = add_ab_markup($line);
 */
 /* 12-14-2017
  'local' abbreviation handled here. Generate an n attribute if one
   is not present
 */
 $line = preg_replace_callback('|<ab(.*?)>(.*?)</ab>|',"BasicAdjust::abbrv_callback",$line);
 
 // Experiment 05-21-2018 for dict == gra
 if ($this->dict == 'gra') {
  $line = preg_replace_callback('| ([0-9]+)[ ,]+([0-9]+)|',"BasicAdjust::rgveda_verse_callback",$line);
 }

 //$line = preg_replace('|- <br/>|','',$line);
 //$line = preg_replace('|<br/>|',' ',$line);
 return $line;
}
 public function add_ab_markup_helper($x) {
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
 public function add_ab_markup($line) {
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
 dbgprint($dbg,"add_ab_markup: line=\n$line\n");
 // reconstruct line
 $ans = join('',$outparts);
 dbgprint($dbg,"add_ab_markup: ans=\n$ans\n");
 return $ans;
}
 public function ls_callback($matches) {
 $n = $matches[1];
 $data = $matches[2];
 #$dbg=false;
 #dbgprint($dbg,"ls_callback: n=$n, data=$data\n");
 $rec = dal_linkpwgauthorities($n);
 list($n0,$code,$codecap,$text) = $rec;
 
 #dbgprint($dbg,"  code=$code, codelo=$codelo, codecap=$codecap\n");
 $datanew = preg_replace("/^$code/",$codecap,$data);
 $ans = "<ls n='$n'>$datanew</ls>";
 #dbgprint($dbg,"ans=$ans\n");
 return $ans;
}
 public function abbrv_callback($matches) {
 /* <ab n="{tran>}">{data}</ab>
  <ab{attrib}>{data)</ab>
 */
 $x = $matches[0]; // full string
 $a = $matches[1];
 $data = $matches[2];
 $dbg=true;
 dbgprint($dbg,"abbrv_callback: a=$a, data=$data\n");
 if(preg_match('/n="(.*?)"/',$a,$matches1)) {
  dbgprint($dbg," abbrv_callback case 1\n");
  $ans = $x;
 }else {
  $tran = getABdata($data);  
  $ans = "<ab n='$tran'>$data</ab>";
  dbgprint($dbg," abbrv_callback case 2\n");
 }
 dbgprint($dbg," abbrv_callback returns $ans\n");
 return $ans;
}

 public function getABdata($key) {
 global $dispfilter;
 // abbreviation tool tips. for pwg.
 $ans="";
 $result = dal_pwgab($key);
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
 public function s_callback($matches) {
/* no special coding for Sanskrit in <s>X</s> form.
    So, just remove the <s>,</s> elements
*/
 $x = $matches[0];
 $x = preg_replace("|(\[Page.*?\])|","</s> $0 <s>",$x);
 //$x = preg_replace("|</?s>|","",$x);
 return $x;
}
 public function rgveda_verse_modern($gra) {
 /*Github user SergeA
  https://github.com/sanskrit-lexicon/Cologne/issues/223#issuecomment-390369526
 */
 $data = [
  [1,191,1,1,191],
  [192,234,2,1,43],
  [235,295,3,1,62],
  [297,354,4,1,58],
  [355,441,5,1,87],
  [442,516,6,1,75],
  [517,620,7,1,104],
  [621,668,8,1,48],
  [1018,1028,8,59,59], //Vālakhilya hymns 1—11
  [669,712,8,60,103],
  [713,826,9,1,114],
  [827,1017,10,1,191]
 ];
 for($i=0;$i<count($data);$i++) {
  list($gra1,$gra2,$mandala,$hymn1,$hymn2) = $data[$i];
  if (($gra1 <= $gra) && ($gra<=$gra2)) {
   $hymn = $hymn1 + ($gra - $gra1);
   $x = "$mandala.$hymn";
   return $x;
  }
 }
 return "?"; // algorithm failed
}
 public function rgveda_verse_callback($matches) {
/* no special coding for Sanskrit in <s>X</s> form.
    So, just remove the <s>,</s> elements
*/
 $x0 = $matches[0];
 $gra1 = $matches[1];
 $gra2 = $matches[2];
 $modern = $this->rgveda_verse_modern((int)$gra1);
 #$x = "<ab n='Standard hymn reference=$modern'>$gra1</ab>,$gra2";
 $x = "<ab n='=$modern (mandala,hymn)'>$gra1</ab>,<graverse>$gra2</graverse>";
 # restore the initial space
 $x = " $x";
 return $x;
}
 public function monierSetNoLit($value) {
 // This function has no effect now (May 4, 2017)
 $this->noLit = $value;
}
 public function basicDisplaySetAccent($accentin){
 if ($accentin == 'yes') {
  $this->accent = True;
 }else {
  $this->accent = False;
 }
}

}
?>
