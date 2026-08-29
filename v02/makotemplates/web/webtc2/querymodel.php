<?php error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING); //querymodel.php
require_once("../webtc/dal.php");
require_once("../webtc/dbgprint.php");
class QueryModel{
 // Gathers a collection of dictionary records 
 public $querymatches; // primary result of constructor
 public $dict; 
 public $status,$lastLnum;
 public $queryParms;
 public $errmsg;
 public $word;
 public $fp;
 public $search_regexp_nonSanskrit;
 public $sopt_case;
 public function __construct($queryParms) {
  $this->dict = $queryParms->dict;
  //$this->dal = new Dal($this->dict);
  $this->queryParms = $queryParms;
  $this->querymatches = array();
  $this->word = $queryParms->word;
  $this->sopt_case = false;
  $n = 0;
  $xmldata;
  $xmlnew="";
  $wordin="";
  if (!($this->openfile())) {return;}

  if ($this->word !="") {
   $this->match_nonSanskrit();
  }else if (($this->queryParms->opt_stransLit) && ($this->queryParms->opt_sword))  {
   $this->match_Sanskrit();
  }
  fclose($this->fp);
 }
 public function openfile() {
  $this->fp = fopen($this->queryParms->filename,"r");
  if (!$this->fp) {
   $this->status = false;
   $this->errmsg = "Could not open Dictionary '{$this->queryParms->filename}'";
   return false;
  }else {
   return true;
  }
 }

 // -------------------------------------------------------------
 // H3633 (G3 / audit D4+W10): generation-time SQLite line index
 // (query_dump.sqlite3, built by csl-pywork pywork/webtc2/build_query_index.py).
 // The index only narrows WHICH dump lines are examined; every candidate
 // line is still matched with the exact legacy regex logic below, so the
 // returned matches (keys, order, matchword, lastLnum) are identical to the
 // plain linear scan.  The flat query_dump.txt remains the source of truth;
 // whenever the index is absent, unreadable, of unknown schema, or stale
 // (its recorded dumpsize differs from the actual dump), open_query_index()
 // returns null and the original sequential fgets scan is used unchanged.
 // -------------------------------------------------------------
 public function open_query_index() {
  if (!class_exists('SQLite3')) {
   return null; // sqlite3 extension unavailable: legacy scan
  }
  $idxfile = preg_replace('/\.txt$/','',$this->queryParms->filename) . '.sqlite3';
  if (!file_exists($idxfile)) {
   return null; // no index shipped: flat-file fallback
  }
  clearstatcache(true,$this->queryParms->filename);
  $dumpsize = @filesize($this->queryParms->filename);
  if ($dumpsize === false) {
   return null;
  }
  $db = @new SQLite3($idxfile,SQLITE3_OPEN_READONLY);
  if ($db === false) {
   return null;
  }
  @$db->busyTimeout(2000);
  $ver = @$db->querySingle('PRAGMA user_version');
  if ($ver === false || $ver === null || intval($ver) != 1) {
   // unknown schema: never guess, fall back to the scan
   $db->close();
   return null;
  }
  $ds = @$db->querySingle("SELECT v FROM meta WHERE k='dumpsize'");
  if ($ds === false || $ds === null || intval($ds) != intval($dumpsize)) {
   // stale index (dump regenerated since the index was built)
   $db->close();
   return null;
  }
  return $db;
 }
 // Longest run of REQUIRED literal alphanumeric characters of the search
 // term.  A dump line can only satisfy the legacy regex if it contains this
 // literal run (the index stores the hyphen-stripped line, and '-' is
 // stripped from English query words (COLOGNE#75) while SLP1 Sanskrit terms
 // never contain '-').
 // The term may be a PCRE fragment: non-exact Sanskrit modes keep the
 // wildcards . * ? + | as quantifiers/metacharacters, so an alnum char
 // followed by * or ? is OPTIONAL (kr* = 'k' + zero-or-more 'r') and only
 // unquantified chars are guaranteed present.  An optional char also ends
 // the current run (kr*a guarantees 'k' and 'a' but never 'ka' contiguously).
 // preg_quote()d terms (exact modes / English words) contain no active
 // quantifiers at all, since every metachar is backslash-escaped.
 // Returns '' when the pattern has no required literal part (pure
 // wildcards), which selects the legacy scan instead.
 public function index_anchor($word) {
  $best = "";
  $cur = "";
  $n = strlen($word);
  $i = 0;
  while ($i < $n) {
   $c = $word[$i];
   if ($c == "\\" && ($i + 1) < $n) {
    // escaped char: literal; required iff alphanumeric
    $c = $word[$i + 1];
    $i = $i + 2;
    if (ctype_alnum($c)) {
     $cur .= $c;
     if (strlen($cur) > strlen($best)) {$best = $cur;}
    }else {
     $cur = "";
    }
    continue;
   }
   $i = $i + 1;
   if (ctype_alnum($c)) {
    $quantified = ($i < $n && ($word[$i] == "*" || $word[$i] == "?"));
    if ($i < $n && $word[$i] == "+") {
     $i = $i + 1; // X+ requires at least one X: still required, consume it
    }
    if ($quantified) {
     $cur = ""; // optional: nothing guaranteed, and run continuity breaks
    }else {
     $cur .= $c;
     if (strlen($cur) > strlen($best)) {$best = $cur;}
    }
   }else {
    // metacharacter (. * ? + | or anything else): breaks continuity
    $cur = "";
   }
  }
  return $best;
 }
 // Candidate line offsets in file order, in chunks of 2000.  $inclusive
 // must be true for the first chunk so a paginated lastLnum (always a line
 // start) is re-examined, exactly like the legacy fseek($fp,$lastLnum).
 public function index_candidates($db,$anchor,$afteroff,$inclusive) {
  $offs = array();
  if ($inclusive) {
   $sql = "SELECT off FROM lines WHERE off >= ? AND t LIKE ? ORDER BY off LIMIT 2000";
  }else {
   $sql = "SELECT off FROM lines WHERE off > ? AND t LIKE ? ORDER BY off LIMIT 2000";
  }
  $stmt = @$db->prepare($sql);
  if ($stmt === false) {
   return $offs;
  }
  $stmt->bindValue(1,$afteroff,SQLITE3_INTEGER);
  $stmt->bindValue(2,'%'.$anchor.'%',SQLITE3_TEXT);
  $result = @$stmt->execute();
  if ($result !== false) {
   while (($row = $result->fetchArray(SQLITE3_NUM)) !== false) {
    $offs[] = intval($row[0]);
   }
   $result->finalize();
  }
  $stmt->close();
  return $offs;
 }

 public function match_nonSanskrit() {
   $non_word = "[^a-zA-Z0-9]";
   // After soft-hyphen strip (COLOGNE#75), word chars no longer need '-'
   $wordreg = "[a-zA-Z0-9]";
   /*
   $wordchrs = preg_split ('/[^a-zA-Z.*?+]/',$this->word);
   $this->word = join('',$wordchrs);
   */
   $wordin = $this->word;
   $word = $this->word; // for simplicity in following string expressions
   $word = mb_strtolower($word);
   // COLOGNE#75: digitization soft-hyphens (dia-mond) block exact English
   // matches for 'diamond'. Strip '-' from the query word; matchkey also
   // strips '-' from each dump line before regex matching.
   $word = str_replace('-', '', $word);
   // $word is interpolated raw into several preg_match() patterns below and
   // in matchkey(); quote it so a crafted ?word= cannot inject regex syntax
   // (catastrophic backtracking / ReDoS, or a pattern-compile error).
   $word = preg_quote($word, '/');
   if ($this->queryParms->opt_regexp == "exact"){
    $search_regexp = "[\t].*$non_word($word)$non_word";
   }else if ($this->queryParms->opt_regexp == "prefix") {
    $search_regexp = "[\t].*$non_word($word$wordreg+)$non_word";
   }else if ($this->queryParms->opt_regexp == "suffix") {
    $search_regexp = "[\t].*$non_word($wordreg+$word)$non_word";
   }else if ($this->queryParms->opt_regexp == "instring"){
    $search_regexp = "[\t].*$non_word($wordreg+$word$wordreg+)$non_word";
   }else if ($this->queryParms->opt_regexp == "substring"){
    $search_regexp = "[\t].*$non_word($wordreg*$word$wordreg*)$non_word";
   }else {
    $search_regexp = "[\t].*$word";
   } 
   $this->search_regexp_nonSanskrit = $search_regexp;
   $search_opt = $this->sopt_case;
   $tempar = $this->matchkey($search_regexp,$search_opt,$word);
   $this->querymatches = $tempar['ans'];
   $this->lastLnum = $tempar['lastLnum'];
   if (count($this->querymatches) == 0) {
    $this->status = true;
    $this->errmsg = "No matches found for '$word'";
   } else {
    $this->status = true;
   }
 }
 public function match_Sanskrit() {
  //in the file, the 'key' field is given in SLP.
  //we may need to modify from HK or ITRANS
  
   $slpword = $this->translate_string2SLP($this->queryParms->opt_stransLit,$this->queryParms->opt_sword);
   $wordchrs = preg_split ('/[^a-zA-Z.*?+|]/',$slpword); // 10-9-2021
   $slpword = join('',$wordchrs);
   // H1523: in exact mode, treat sword as a literal (modes supply wildcards).
   // Other modes still allow * ? . + | as intentional user wildcards (cap length
   // already applied in QueryParm).
   if ($this->queryParms->opt_sregexp == "exact") {
    $slpword = preg_quote($slpword, '/');
   }
   //dbgprint(true,"match_Sanskrit: slpword='$slpword'\n");
   if ($slpword == '') {
    $this->status = true;
    $this->querymatches = array();
    $this->errmsg = "No matches found for '$slpword'";
    return;
   }
   $non_word = "[^a-zA-Z0-9|]";  // 10-9-2021
   $wordreg = "[a-zA-Z0-9-|]";
   $wordin = $slpword;
   if ($this->queryParms->opt_sregexp == "exact"){
    //$search_regexp = "^$slpword" . "[\t]";
    $search_regexp = "$non_word($slpword)$non_word.*[\t]";
   }else if ($this->queryParms->opt_sregexp == "prefix") {
    //$search_regexp = "^$slpword.+" . "[\t]";
    $search_regexp = "$non_word($slpword$wordreg*)$non_word.*[\t]";
   }else if ($this->queryParms->opt_sregexp == "suffix") {
    //$search_regexp = ".+$slpword" . "[\t]";
    $search_regexp = "$non_word($wordreg*$slpword)$non_word.*[\t]";
   }else if ($this->queryParms->opt_sregexp == "instring"){
    //$search_regexp = ".+$slpword.+" . "[\t]";
    $search_regexp = "$non_word($wordreg+$slpword$wordreg+)$non_word.*[\t]";
   }else if ($this->queryParms->opt_sregexp == "substring"){
    //$search_regexp = ".*$slpword.*" . "[\t]";
    $search_regexp = "$non_word($wordreg*$slpword$wordreg*)$non_word.*[\t]";
   }else {
    $search_regexp = "$slpword.*[\t]";
   }
   //dbgprint(true,"search_regexp='$search_regexp'\n");
   $search_regexp = preg_replace('/\|/','\\|',$search_regexp);
   //dbgprint(true,"search_regexp ADJ='$search_regexp'\n");
   $this->search_regexp_nonSanskrit = null;
   #$search_opt = $this->queryParms->opt_stransLit;
   $opt_swordhw = $this->queryParms->opt_swordhw;
   // H3633 (G3): pass the term itself so the index anchor can be derived
   // from it ($search_regexp embeds character-class wrapper syntax).
   $tempar = $this->smatchkey($search_regexp,$slpword);
   $this->querymatches = $tempar['ans'];
   $this->lastLnum = $tempar['lastLnum'];
   $this->status = true;
 }

 public function matchkey($regexp,$opt,$word) {
 // word is lower case. $opt is 
 $fp = $this->fp;
 $lastLnum = $this->queryParms->lastLnum;
 $max = $this->queryParms->max;
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
 // H3633 (G3): consult the generation-time index when present and usable;
 // the per-line matching is the exact legacy logic in both paths below.
 $db = $this->open_query_index();
 $anchor = "";
 if ($db !== null) {
  $anchor = $this->index_anchor($word);
 }
 if (($db !== null) && ($anchor != "")) {
  $dumpsize = filesize($this->queryParms->filename);
  $cursor = $lastLnum;
  $inclusive = true;
  $candidates = array();
  $ncand = 0;
  $ix = 0;
  $more = true;
  while (true) {
   if ($ix >= $ncand) {
    if (!$more) {break;}
    $candidates = $this->index_candidates($db,$anchor,$cursor,$inclusive);
    $inclusive = false;
    $ncand = count($candidates);
    $ix = 0;
    if ($ncand == 0) {break;}
    $more = ($ncand >= 2000);
   }
   $off = $candidates[$ix];
   $ix = $ix + 1;
   $cursor = $off;
   fseek($fp,$off,0); // reposition
   if (!feof($fp)) {
    $line=fgets($fp);
   }else {
    $line = FALSE;
   }
   if (!($line)) {break;} // index/dump disagreement guard
   $nline++;
   $linex="";
   // COLOGNE#75: match against hyphen-stripped body so 'diamond' finds 'dia-mond'
   $liney = str_replace('-', '', $line);
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
     //$ans[$ntot] = $line;
     // return key and non-sanskrit matchword
     if (!preg_match('/^(.*?)\t(.*?)$/',$linex,$matches)) {
      continue; // should not happen
     }
     $keypart = $matches[1];
     list($key,$sanskrit) = preg_split('|:|',$keypart);
     $key = trim($key);  // the headword
     // extract matchword from hyphen-stripped line (same text used for match)
     $liney_match = str_replace('-', '', $linex);
     if (!preg_match("/$regexp/",$liney_match,$matches)){
      $matchword=""; // should not happen
     }else {
      $matchword = $matches[1];
      //dbgprint(true,"querymodel: matchword=$matchword, regexp=$regexp\n");
     }
     if ($this->new_key_line($ntot,$key,$ans)){
      $ans[$ntot] = array( "key"=>$key, "matchword"=>$matchword);
      $ntot++;
      $lastLnum=ftell($fp); // get new file position
     }
    }
   
   if ($ntot >= $max) {
    // legacy scan reads one more line and sets lastLnum=-1 when that hit
    // EOF; mirror that: if the just-accepted line is the dump's last line,
    // there is no further page
    if (ftell($fp) >= $dumpsize) {$lastLnum = -1;}
    break;
   }
  }
  if ($ntot < $max) {$lastLnum = -1;} // legacy scan would have reached EOF
 }else {
  if ($db !== null) {$db->close();}
  $db = null;
  $nline=0;
  $nothing=0;
  while ($line) {
   $nline++;
   $linex="";
   // COLOGNE#75: match against hyphen-stripped body so 'diamond' finds 'dia-mond'
   $liney = str_replace('-', '', $line);
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
     //$ans[$ntot] = $line;
     // return key and non-sanskrit matchword
     if (!preg_match('/^(.*?)\t(.*?)$/',$linex,$matches)) {
      continue; // should not happen
     }
     $keypart = $matches[1];
     list($key,$sanskrit) = preg_split('|:|',$keypart);
     $key = trim($key);  // the headword
     // extract matchword from hyphen-stripped line (same text used for match)
     $liney_match = str_replace('-', '', $linex);
     if (!preg_match("/$regexp/",$liney_match,$matches)){
      $matchword=""; // should not happen
     }else {
      $matchword = $matches[1];
      //dbgprint(true,"querymodel: matchword=$matchword, regexp=$regexp\n");
     }
     if ($this->new_key_line($ntot,$key,$ans)){
      $ans[$ntot] = array( "key"=>$key, "matchword"=>$matchword);
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
 }
 if ($db !== null) {$db->close();}
 // print "chk: nline=$nline, nothing = $nothing, ntot=$ntot\n";
 $ans1=array();
 $ans1['ans']=$ans;
 $ans1['lastLnum']=$lastLnum;
 $ans1['nline']=$nline;
 $ans1['nothing']=$nothing;
 $ans1['ntot'] = $ntot;
 return $ans1;
}

public function smatchkey($regexp,$slpword='') {
 //dbg: $fplog = fopen('query_log.txt','w');
 //dbg: fwrite($fplog,"smatchkey regexp = $regexp\n");
 //dbg: fclose($fplog);
 $dbg=false;
 dbgprint($dbg,"QueryModel.smatchkey. regexp=$regexp\n");
 $fp = $this->fp;
 $lastLnum = $this->queryParms->lastLnum;
 $lastLnum = (int)$lastLnum;
 $max = (int)$this->queryParms->max;
 $opt_swordhw = $this->queryParms->opt_swordhw;
 dbgprint($dbg," lastLnum=$lastLnum, max = $max\n");
 #$transLit = $this->queryParms->opt_stransLit;
 $ntot = 0;
 fseek($fp,$lastLnum,0); // reposition
 //$line=fgets($fp);
 $ans=array();
 if (!feof($fp)) {
  $line=fgets($fp);
 }else {
  $line = FALSE;
 }
 $ntry = 0;
 // H3633 (G3): consult the generation-time index when present and usable;
 // the per-line matching is the exact legacy logic in both paths below.
 $db = $this->open_query_index();
 $anchor = "";
 if ($db !== null) {
  $anchor = $this->index_anchor($slpword);
 }
 if (($db !== null) && ($anchor != "")) {
  $dumpsize = filesize($this->queryParms->filename);
  $cursor = $lastLnum;
  $inclusive = true;
  $candidates = array();
  $ncand = 0;
  $ix = 0;
  $more = true;
  while (true) {
   if ($ix >= $ncand) {
    if (!$more) {break;}
    $candidates = $this->index_candidates($db,$anchor,$cursor,$inclusive);
    $inclusive = false;
    $ncand = count($candidates);
    $ix = 0;
    if ($ncand == 0) {break;}
    $more = ($ncand >= 2000);
   }
   $off = $candidates[$ix];
   $ix = $ix + 1;
   $cursor = $off;
   fseek($fp,$off,0); // reposition
   if (!feof($fp)) {
    $line=fgets($fp);
   }else {
    $line = FALSE;
   }
   if (!($line)) {break;} // index/dump disagreement guard
   $ntry = $ntry + 1;
   $linex="";
   list($a,$b) = preg_split("|\t|",$line);
   #$liney=" " . $a . " \t"; // 
   // New logic when searching for sanskrit within text is possible.
   if (preg_match("|^(.*?)::(.*?)$|",$a,$matches)) {
    $ahw = $matches[1];
    $atext = $matches[2];
   }else { // unexpected. Probably doesn't occur
    $ahw = $a;
    $atext = $a;
   }
   if (in_array($this->dict,array('ae','mwe','bor'))) {
    # search for sanskrit word only within text.
    $liney=" " . $atext . " \t"; //    
   }else  if ($opt_swordhw == 'both') {
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
    dbgprint($dbg,"liney=$liney\n");
     //$ans[$ntot] = $line ;
     if (!preg_match('/^(.*?)\t(.*?)$/',$linex,$matches)) {
      continue; // should not happen
     }
     $keypart = $matches[1];
     list($key,$sanskrit) = preg_split('|:|',$keypart);
     $key = trim($key);  // the headword
     $matchword = ""; // only relevant for non-sanskrit match
     if ($this->new_key_line($ntot,$key,$ans)){
      $ans[$ntot] = array( "key"=>$key, "matchword"=>$matchword);
      $ntot++;
      $lastLnum=ftell($fp); // get new file position
     }
    }
   
   if ($ntot >= $max) {
    // legacy scan reads one more line and sets lastLnum=-1 when that hit
    // EOF; mirror that: if the just-accepted line is the dump's last line,
    // there is no further page
    if (ftell($fp) >= $dumpsize) {$lastLnum = -1;}
    break;
   }
  }
  if ($ntot < $max) {$lastLnum = -1;} // legacy scan would have reached EOF
 }else {
  if ($db !== null) {$db->close();}
  $db = null;
  while ($line) {
   $ntry = $ntry + 1;
   $linex="";
   list($a,$b) = preg_split("|\t|",$line);
   #$liney=" " . $a . " \t"; // 
   // New logic when searching for sanskrit within text is possible.
   if (preg_match("|^(.*?)::(.*?)$|",$a,$matches)) {
    $ahw = $matches[1];
    $atext = $matches[2];
   }else { // unexpected. Probably doesn't occur
    $ahw = $a;
    $atext = $a;
   }
   if (in_array($this->dict,array('ae','mwe','bor'))) {
    # search for sanskrit word only within text.
    $liney=" " . $atext . " \t"; //    
   }else  if ($opt_swordhw == 'both') {
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
    dbgprint($dbg,"liney=$liney\n");
     //$ans[$ntot] = $line ;
     if (!preg_match('/^(.*?)\t(.*?)$/',$linex,$matches)) {
      continue; // should not happen
     }
     $keypart = $matches[1];
     list($key,$sanskrit) = preg_split('|:|',$keypart);
     $key = trim($key);  // the headword
     $matchword = ""; // only relevant for non-sanskrit match
     if ($this->new_key_line($ntot,$key,$ans)){
      $ans[$ntot] = array( "key"=>$key, "matchword"=>$matchword);
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
 }
 if ($db !== null) {$db->close();}
 dbgprint($dbg," ntry = $ntry, ntot=$ntot\n");
 $ans1=array();
 $ans1['ans']=$ans;
 $ans1['lastLnum']=$lastLnum;
 return $ans1;
}
public function translate_string2SLP($transLit,$keyin) {
 $key = $keyin;
 $key = transcoder_processString($key,$transLit,'slp1');
 return $key;
}
public function extract_key($line) {
 // 11-13-2018.  Do same as in querlistview.php function display_outopt4
 if (! preg_match('/^(.*?)\t(.*?)$/',$line,$matches)) {
  return FALSE;
 }
 $keypart = $matches[1];
 list($key,$sanskrit) = preg_split('|:|',$keypart);
 $key = trim($key);
 return $key;
}

 public function new_key_line($ntot,$key,$ans) {
  $dbg=false;
  dbgprint($dbg,"new_key_line: key=$key, line=$line\n");
  foreach($ans as $a) {
   $key1 = $a['key'];
   if ($key1 == $key) {
    return false;  # duplicate key
   }
  }
  dbgprint($dbg,"new_key_line returns true\n");
  return true;
 }

}
  
?>
