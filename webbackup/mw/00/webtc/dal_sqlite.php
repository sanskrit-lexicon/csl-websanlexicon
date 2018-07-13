<?php
/* webtc/dal_sqlite.php  ejf Oct 31, 2012
  This collects all the database access code used by
  webtc and webtc5wa.
  It assumes the data access is via sqlite.
  There are several routines:
  dal_mw1(key) from 'mw' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_mw2(L1,L2) from 'mw' return array of records (key1,lnum1,data1)
            where L1 <= lnum <= L2
  dal_mwkeys(key)  from 'mwkeys' return string; if string is empty string
                   there was no match.
  June 7, 2015  Change way '$db' is specified.
     old: $db = "../sqlite/X.sqlite"\; 
     new: $db = dal_dbname('X');  
   and dal_dbname uses path relative to this dal_sqlite.php file
  Oct 29, 2016.  Change dal_dbname so it works on Cologne as well as
     on local xampp server under Windows
  2018-05-03.  Change dal_mw1 to be like dal_mw1_get1_mwalt.php of apidev
      The former dal_mw1 is renamed dal_mw1_get1.
      This is so that HxBC records are returned as part of the result,
      even when the headword spelling differs from that of Hx.
      Also required are dal_mwget4a and dal_mwget4b, which get the previous
      or next record.
*/
function dal_dbname($pfx) {
 $dir = dirname(__FILE__); //directory containing this php file
 if (preg_match('|^/afs|',$dir)) { 
  // For Cologne server
  // Note: $dir does not end in '/'
  // Remove last component of $dir
  $dir = preg_replace("|/webtc|","",$dir);
  //echo $dir;
  return "$dir" . "/sqlite/$pfx.sqlite";
 }else {
  // xampp server (or any other server) should work on Windows and Linux.
  $dirparent = dirname($dir);
  $relpath = "sqlite/$pfx.sqlite";
  $path = "$dirparent/$relpath"; 
  $ans = realpath($path);
  #echo "<p>ans=$ans\n<p>";
  #$relpath = "../sqlite/$pfx.sqlite";
  #$ans1 = realpath($relpath);
  #echo "<p>ans1=$ans1\n<p>";
  return $ans;
 }
}
function dal_sqlite($dbin,$sql) {
// returns array of records from the sqlite database at filename $db
// according to the SQL query $sql.
// Each record is an array of all the columns (in the order specified
// in the table creation) - namely key,lnum,data at indices 0,1,2
$db = "sqlite:$dbin";
//echo "dal_sqlite: db='$db', sql='$sql'\n";
try {
 $file_db = new PDO($db);
 $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo '<p>Caught exception: ',  $e->getMessage(), "</p>\n";
    echo "<p>db = $db</p>\n";
    exit(1);
}
$result = $file_db->query($sql);
// Note: with this version, cannot return $result, a PDO object.
$ansarr=array();
foreach($result as $rec) {
 //var_dump($rec);
 $ansarr[]=$rec;
}
return $ansarr;
}
function prev_dal_sqlite($dbin,$sql) {
// returns array of records from the sqlite database at filename $db
// according to the SQL query $sql.
// Each record is an array of all the columns (in the order specified
// in the table creation)
$db = "sqlite:$dbin";
//echo "dal_sqlite: db='$db', sql='$sql'\n";
$file_db = new PDO($db);
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$result = $file_db->query($sql);
$ansarr=array();
foreach($result as $m) {
 $rec = array($m['key'],$m['lnum'],$m['data']);
 $ansarr[]=$rec;
}
return $ansarr;
}

function old_dal_sqlite($db,$sql) {
// returns array of records from the sqlite database at filename $db
// according to the SQL query $sql.
// Each record is an array of all the columns (in the order specified
// in the table creation)
$cmd = "sqlite3";
/* sqlite3 returns columns separated by a default 'separator' of '|'
 Since the '|' occurs in some records of MW, this separator needs to be
 changed.  
 This separator is used below to split the returned data into fields.
*/
$sep = '{sqlite3}';
$cmd1 = "$cmd -separator $sep $db \"$sql\"";
$ans = shell_exec($cmd1);
// $ans is a string, representing many lines. 
// Separate the lines into '$results', an array of string
$results = preg_split('/\n/',$ans);

$ansarr=array(); // the returned array
$nmatches=0;
foreach($results as $line) {
 // split each line into an array of fields,
 // and put the array of fields into the next element of returned array
 $line=trim($line);
 if ($line == ''){continue;}
 $ansarr[] = preg_split("/$sep/",$line);
}
return $ansarr;
}
function dal_mw_sql($sql) {
// General query on 'sanskrit' database. Table assumed in $sql
$db = dal_dbname("mw");
$dbg=False;
if($dbg) {echo "dal_mw_sql: $sql\n";}
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mw1_get1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
// 05-03-2018 - formerly called dal_mw1
$db = dal_dbname("mw");
$sql = "select * from mw where key='$key' order by lnum";
$recarr = dal_sqlite($db,$sql);

$ansarr=array();
$nmatches=0;
foreach($recarr as $rec) {
 list($key1,$lnum1,$data1) = $rec;
 if ($key1 == $key) {
  // may be necessary if sql query was case insensitive.
  // This is likely the case for sqlite database
  $ansarr[]=$rec;
 }
}
return $ansarr;
}
function dal_mw1_hcode($data){
 if (preg_match('/^<(H.*?)>/',$data,$matches)) {
  return $matches[1];
 }else {
  return ""; // should not happen
 }
}
function dal_mw1($key) {
 // 05-03-2018. Based on dal_get1_mwalt.php of apidev
require_once('../webtc1/dbgprint.php'); 
$dbg=False;
# first step is to call the original dal_mw1_get1
$recs = dal_mw1_get1($key);
$nrecs = count($recs);
// Step 1: fill in forward gaps in $recs
$newitems=array();
for($i=0;$i<$nrecs-1;$i++) {
 $item0 = $recs[$i];  // key,lnum,data
 $item1 = $recs[$i+1];
 $newitems[] = $item0;
 $lnum1 = $item1[1];
 while(True) {
  $lnum0 = $item0[1];
  $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  dbgprint($dbg,"Chk 1: $lnum0, $hcode0, ${item0[0]}\n");
  $temprecs = dal_mwget4b($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = dal_mw1_hcode($rec[2]);
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
 $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
 dbgprint($dbg,"Chk 1-LAST: $lnum0, $hcode0, ${item0[0]}\n");
}
// Add any records after last record of $dispItems
 while(True) {
  $lnum0 = $item0[1];
  $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  dal_mwget4b($lnum0,1);
  $temprecs = dal_mwget4b($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = dal_mw1_hcode($rec[2]);
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
   $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
   dbgprint($dbg,"Chk 1-extra: $lnum0, $hcode0, ${item0[0]}\n");
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
  $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  dbgprint($dbg,"Chk 2: $lnum0, $hcode0, ${item0[0]}\n");
  $temprecs = dal_mwget4a($lnum0,1);
  if(count($temprecs) != 1) { // only at last record in database
   break;
  }
  $rec = $temprecs[0]; // key,lnum,data
  $lnum = $rec[1];
  if ($lnum == $lnum1) {
   break;
  }
  $hcode = dal_mw1_hcode($rec[2]);
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
 $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
 dbgprint($dbg,"Chk 2-LAST: $lnum0, $hcode0, ${item0[0]}\n");
}

 while(True){
  $lnum0 = $item0[1];
  $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
  dbgprint($dbg,"Chk 2a: $lnum0, $hcode0, ${item0[0]}\n");
  $temprecs = dal_mwget4a($lnum0,1);
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
  $hcode = dal_mw1_hcode($rec[2]);
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
   $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx
   dbgprint($dbg,"Chk 2-extra: $lnum0, $hcode0, ${item0[0]}\n");
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
  $hcode0 = dal_mw1_hcode($item0[2]); // data = <Hx>{rest} ==> Hx  
  dbgprint($dbg,"Chk 3: $lnum0, $hcode0, ${item0[0]}\n");
 }
}
 $ans=$newitems;
 return $ans;
}
function dal_mwget4a($lnum0,$max) {
 $lnum0 = round($lnum0,3);  
 $dict = 'mw';
 $sql = "select * from $dict where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
 return dal_mw_sql($sql);
}
function dal_mwget4b($lnum0,$max) {
 $lnum0 = round($lnum0,3);  
 $dict = 'mw';
 $sql = "select * from $dict where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
 $sql = "select * from $dict where ('$lnum0' < lnum) order by lnum LIMIT $max";
 return dal_mw_sql($sql);
}
function dal_mw2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
// 2017-07-25 The 'DarmeRa' problem. Round to three decimal places
$L1 = round($L1,3);
$L2 = round($L2,3);
$db = dal_dbname("mw");
$sql="select * from mw where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mw3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = dal_dbname("mw");
$sql = "select * from mw where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mw4a($lnum0,$max) {
  //  Used in listhier
  // in mw, with L=99930.1, $lnum0 appears as if L=99930.1000000001
  // To guard against this, we round lnum0 to 3 decimal places.
  //  [This is consistent with the schema definition]
  $lnum0 = round($lnum0,3);
$sql = "select * from mw where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_mw_sql($sql);
return $recarr;
}
function dal_mw4b($lnum0,$max) {
  //  Used in listhier
  // in mw, with L=99930.1, $lnum0 appears as if L=99930.1000000001
  // To guard against this, we round lnum0 to 3 decimal places.
  //  [This is consistent with the schema definition]
  $lnum0 = round($lnum0,3);
$sql = "select * from mw where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `mw` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_mw_sql($sql);
return $recarr;
}

function dal_mwkeys($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from mwkeys for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("mwkeys");
$sql = "select * from mwkeys where key='$key'";
$recarr = dal_sqlite($db,$sql);

// get $keydata1 from $key using mwkeys table
$keydata1 = '';
foreach($recarr as $rec) {
 list($key1,$lnum1,$data1) = $rec;
 if ($key1 == $key) {
  $keydata1 = $data1;
 }
}
return $keydata1;
}
function dal_mwkeys1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = dal_dbname("mwkeys");
$sql = "select * from mwkeys where key='$key' order by lnum";
$recarr = dal_sqlite($db,$sql);
$ansarr=array();
$nmatches=0;
foreach($recarr as $rec) {
 list($key1,$lnum1,$data1) = $rec;
 if ($key1 == $key) {
  // may be necessary if sql query was case insensitive.
  $ansarr[]=$rec;
 }
}
return $ansarr;
}

function dal_mwkeys2($lnum1,$lnum2) {
// returns an array of records, one for each L-value in the range
// $lnum1 <= $lnum <= $lnum2
// each record is an array with three elements: key,lnum,data
$db = dal_dbname("mwkeys");
$sql="select * from mwkeys where  $lnum1 <= lnum and lnum <= $lnum2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}

function dal_mwkeys3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = dal_dbname("mwkeys");
$sql = "select * from mwkeys where key LIKE '$key%'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mwab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from mwab for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("mwab");
$sql = "select * from mwab where id='$key'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_westmwtab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from wesmwtab for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("westmwtab");
$sql = "select * from westmwtab where key='$key' order by `data`";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_whitmwtab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from whitmwtab for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("whitmwtab");
$sql = "select * from whitmwtab where key='$key' order by `data`";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mwgreek($L) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from mwgreek for the given 'L'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("mwgreek");
$sql = "select * from mwgreek where lnum='$L'";
$recarr = dal_sqlite($db,$sql);
//$n = count($recarr);
//print "dbg: dal_mwgreek: recarr has $n entries for L=$L\n";
return $recarr;
}
function unused_dal_linkmwauthorities($ls) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from linkmwauthorities for the given 'L'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("linkmwauthorities");
$sql = "select * from linkmwauthorities where key='$ls'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_mwauthtooltips($ls) {
// 
$db = dal_dbname("mwauthtooltips");
$sql = "select * from mwauthtooltips where key='$ls'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
?>
