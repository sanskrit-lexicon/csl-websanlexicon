<?php
/* dal.php  Apr 28, 2015 Multidictionary access to sqlite Databases
 June 4, 2015 - use pywork/html/Xhtml.sqlite
 May 10, 2015 - also allow use of web/sqlite/X.sqlite
 June 29, 2018. Recode as a class.
*/
require_once('dictinfo.php');
require_once('dbgprint.php');
class Dal {
 public $dict;
 public $dictinfo;
 public $sqlitefile;
 public $file_db;
 public $dbg=false;
 public $dbname; 
 public $tabname;  # name of table in sqlitefile. 
 public $tabid;    # name of 'id' key used by getgeneral
 // dbname is assumed to be for auxiliary sqlite data, such as
 // abbreviations  xab.sqlite, xath.sqlite -- new Dal('mw','mwab')
 // Not yet implemented.  Would need to modify dictinfo for filenames also.
 // 
 public function __construct($dict,$dbname=null) {
  $this->dict=strtolower($dict);
  $this->dbname = $dbname;
  $this->dictinfo = new DictInfo($dict);
  $sqlitedir = $this->dictinfo->sqlitedir;
  if ($dbname == null) {
   $this->sqlitefile = "$sqlitedir/{$this->dict}.sqlite";
   $this->tabname = $this->dict;
   $this->tabid = 'key';
   dbgprint($this->dbg,"Dal construct. sqlitefile={$this->sqlitefile}, tabname={$this->tabname}\n");
  }else if ($dbname == "ab") {
   $this->tabname = $this->dict . "ab";
   $this->sqlitefile = "$sqlitedir/{$this->tabname}.sqlite";
   $this->tabid = 'id';
  }else if ($dbname == "bib") {  // author file for pwg, pw
   $this->tabname = $this->dict . "bib";
   $this->sqlitefile = "$sqlitedir/{$this->tabname}.sqlite";
   $this->tabid = 'id';
  }else if ($dbname == "authtooltips") {  // author file for mw
   $this->tabname = $this->dict . "authtooltips";
   $this->sqlitefile = "$sqlitedir/{$this->tabname}.sqlite";
   $this->tabid = 'key';
  }else { // unknown $dbname
   $this->file_db = null;
   $this->status=false;
   return;
  }
  // connection to sqlitefile
  $dbg=false;
  if (file_exists($this->sqlitefile)) {
  try {
   $this->file_db = new PDO('sqlite:' .$this->sqlitefile);
   $this->file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   #dbgprint($dbg,"dal.php: opened " . $this->sqlitefile . "\n");
   $this->status=true;
  } catch (PDOException $e) {
   $this->file_db = null;
   #dbgprint($dbg,"dal.php: Cannot open " . $this->sqlitefile . "\n");
   $this->status=false;
  }
 } else {
   $this->file_db = null;
   dbgprint($dbg,"dal.php: File does not exist. Cannot open " . $this->sqlitefile . "\n");
   $this->status=false;
 }
 }
 public function close() {
  if ($this->file_db) {
   $this->file_db = null;  //ref: //php.net/manual/en/pdo.connections.php
  }
  if ($this->file_db_xml) { // not sure of usage here
   $this->file_db_xml = null;  
  }
 }
 public function get($sql) {
  $ansarr = array();
  if (!$this->file_db) {
   //"file_db is null for $this->sqlitefile.
   return $ansarr;
  }
  $result = $this->file_db->query($sql);
  if ($result == false) {
   return $ansarr;
  }
  foreach($result as $m) {
   $rec = array($m['key'],$m['lnum'],$m['data']);
   $ansarr[]=$rec;
  }
  return $ansarr; 
 }
 public function get_xml($sql) {
  $ansarr = array();
  if (!$this->file_db_xml) {
   dbgprint($this->dbg, "file_db_xml is null. sqlitefile={$this->sqlitefile}\n");
   return $ansarr;
  }
  $result = $this->file_db_xml->query($sql);
  foreach($result as $m) {
   $rec = array($m['key'],$m['lnum'],$m['data']);
   $ansarr[]=$rec;
  }
  return $ansarr; 
 }
 public function get1($key) {
  // Returns associative array for the records in dictionary with this key
  $sql = "select * from {$this->dict} where key='$key' order by lnum";
  return $this->get($sql);
 }
 public function get1_xml($key) {
  // Returns associative array for the records in dictionary with this key
  $sql = "select * from {$this->dict} where key='$key' order by lnum";
  dbgprint($this->dbg, "get1_xml, sql=$sql\n");
  return $this->get_xml($sql);
 }

 public function get2($L1,$L2) {
  //  Used in listhier
  // returns an array of records, one for each L-value in the range
  // $L1 <= $L <= $L2
  // each record is an array with three elements: key,lnum,data
  $sql="select * from {$this->dict} where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
  return $this->get($sql);
 }
 public function get3($key) {
  // returns an array of records, which start like $key
  $sql = "select * from {$this->dict} where key LIKE '$key%' order by lnum";
  return $this->get($sql);
 }
 public function get3a($key,$max) {
  // returns an array of records, which start like $key
  // Setting a pragma must for case_sensitive
  $pragma="PRAGMA case_sensitive_like=true;";
  $this->file_db->query($pragma);
  $sql = " select * from {$this->dict} where key LIKE '$key%' order by lnum LIMIT $max";
  return $this->get($sql);
 }
 public function get4a($lnum0,$max) {
  //  Used in listhier
  // in mw, with L=99930.1, $lnum0 appears as if L=99930.1000000001
  // To guard against this, we round lnum0 to 3 decimal places.
  //  [This is consistent with the schema definition]
  $lnum0 = round($lnum0,3);
  $sql = "select * from {$this->dict} where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
  return $this->get($sql);
 }
 public function get4b($lnum0,$max) {
  //  Used in listhier
  // in mw, with L=99930.1, $lnum0 appears as if L=99930.1000000001
  // To guard against this, we round lnum0 to 3 decimal places.
  //  [This is consistent with the schema definition]
  $lnum0 = round($lnum0,3);
  $sql = "select * from {$this->dict} where ('$lnum0' < lnum) order by lnum LIMIT $max";
  return $this->get($sql);
 }
 /* Alternate test version for mw
   Jul 19, 2015
 */
public function get1_mwalt($key) {
 // 05-03-2018. Based on dal_get1_mwalt.php of apidev
 // This code initially copied from mw/web/webtc/dal_sqlite.php
 // and adjusted for use within Dal class.
$dbg=False;
# first step is to call the original dal_mw1_get1
$recs = $this->get1($key);
$nrecs = count($recs);
// 07-15-2018. When no recs found, return $recs
if ($nrecs == 0) {
 return $recs;
}
// Step 1: fill in forward gaps in $recs
$newitems=array();
for($i=0;$i<$nrecs-1;$i++) {
 $item0 = $recs[$i];  // key,lnum,data
 $item1 = $recs[$i+1];
 $newitems[] = $item0;
 $lnum1 = $item1[1];
 while(True) {
  $lnum0 = $item0[1];
  $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  $item00 = $item0[0];
  dbgprint($dbg,"Chk 1: $lnum0, $hcode0, $item00\n");
  $temprecs = $this->get4b($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = $this->dal_mw1_hcode($rec[2]);
  if (strlen($hcode) != 3) { //is $hcode like HnA, HnB, HnC ?
   break;
  }
  if(substr($hcode0,0,2) != substr($hcode,0,2)) {
   break;
  }
  // We have another rocord
  $newitems[] = $rec;
  $item0 = $rec;
 } // while True
} // for($i)
// Add the last record of $dispItems
$item0 = $recs[$nrecs-1];
$newitems[] = $item0;
if ($dbg) {
 $lnum0 = $item0[1];
 $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
 $item00 = $item0[0];
 dbgprint($dbg,"Chk 1-LAST: $lnum0, $hcode0, $item00\n");
}
// Add any records after last record of $dispItems
 while(True) {
  $lnum0 = $item0[1];
  $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  $this->get4b($lnum0,1);
  $temprecs = $this->get4b($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = $this->dal_mw1_hcode($rec[2]);
  if (strlen($hcode) != 3) { //is $hcode like HnA, HnB, HnC ?
   break;
  }
  if(substr($hcode0,0,2) != substr($hcode,0,2)) {
   break;
  }
  // We have another rocord
  $newitems[] = $rec;
  $item0 = $rec;
  if ($dbg) {
   $lnum0 = $item0[1];
   $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
   $item00 = $item0[0];
   dbgprint($dbg,"Chk 1-extra: $lnum0, $hcode0, $item00\n");
  }
 } // end while
// reset $recs as $newitems
$recs = $newitems;
$nrecs = count($recs);
// Step 2. fill in backward gaps in $recs
//    Similar to Step 1, but backwards
$newitems = array();
for($i=$nrecs-1;$i>0;$i--) {
 $item0 = $recs[$i];  // key,lnum,data
 $item1 = $recs[$i-1];
 $newitems[] = $item0;
 $lnum1 = $item1[1];
 while(True) {
  $lnum0 = $item0[1];
  $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  dbgprint($dbg,"Chk 2: $lnum0, $hcode0, $item00\n");
  $item00 = $item0[0];
  $temprecs = $this->get4a($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = $this->dal_mw1_hcode($rec[2]);
  if (strlen($hcode0) != 3) { //is $hcode0 like HnA, HnB, HnC ?
   break;
  }
  if(substr($hcode0,0,2) != substr($hcode,0,2)) {
   break;
  }
  // We have another rocord
  $newitems[] = $rec;
  if ($lnum0 == $lnum) {
    break;  // 2017-07-24  ? why needed
  }
  $item0 = $rec;
 } // while True
} // end step 2
// Add the first record 
$item0 = $recs[0];
$newitems[] = $item0;
// Get ones occurring Before first record 
if ($dbg) {
 $lnum0 = $item0[1];
 $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
 $item00 = $item0[0];
 dbgprint($dbg,"Chk 2-LAST: $lnum0, $hcode0, $item00\n");
}

 while(True){
  $lnum0 = $item0[1];
  $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  $item00 = $item0[0];
  dbgprint($dbg,"Chk 2a: $lnum0, $hcode0, $item00\n");
  $temprecs = $this->get4a($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  /* why skip this ?
  if ($lnum == $lnum1) {
   break;
  }
  */
  $hcode = $this->dal_mw1_hcode($rec[2]);
  if (strlen($hcode0) != 3) { //is $hcode like HnA, HnB, HnC ?
   break;
  }
  if(substr($hcode0,0,2) != substr($hcode,0,2)) {
   break;
  }
  // We have another rocord
  $newitems[] = $rec;
  /*
  if ($lnum0 == $lnum) {
    break;  // 2017-07-24  ? why needed
  }
  */
  $item0 = $rec;
  if ($dbg) {
   $lnum0 = $item0[1];
   $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
   $item00 = $item0[0];
   dbgprint($dbg,"Chk 2-extra: $lnum0, $hcode0, $item00\n");
  }
 }
// newitems is 'backwards' lnum order. Get it back in forward lnum order
$nitems = count($newitems);
$newitems1=$newitems;
$newitems=array();
for($i=$nitems-1;$i>=0;$i--) {
 $newitems[]=$newitems1[$i];
 if ($dbg) {
  $item0 = $newitems1[$i];
  $lnum0 = $item0[1];
  $hcode0 = $this->dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx  
  $item00 = $item0[0];
  dbgprint($dbg,"Chk 3: $lnum0, $hcode0, $item00\n");
 }
}
 $ans=$newitems;
 return $ans;
}

public function dal_mw1_hcode($data){
 if (preg_match('/^<(H.*?)>/',$data,$matches)) {
  return $matches[1];
 }else {
  return ""; // should not happen
 }
} 
public function getgeneral($key,$table) {
  if (!$this->file_db) {
   //"file_db is null for $this->sqlitefile.
   return array();
  }
#$sql = "select * from $table where id='$key'";
$sql = "select * from $table where {$this->tabid}='$key'";
$result = $this->file_db->query($sql);
$ansarr = array();
foreach($result as $m) {
 $ansarr[] = $m;
}
return $ansarr;
}
}
?>
