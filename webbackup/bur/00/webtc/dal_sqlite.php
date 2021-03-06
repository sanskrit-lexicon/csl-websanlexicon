<?php
/* webtc/dal_sqlite.php  ejf Oct 31, 2012
  Modified May 14, 2013.  dal_bur1 routine uses php PDO.
  This is the only function used in the bur display.

  This collects all the database access code used by
  webtc and webtc5wa.
  It assumes the data access is via sqlite.
  There are several routines:
  dal_bur1(key) from 'bur' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_bur2(L1,L2) from 'bur' return array of records (key1,lnum1,data1)
            where L1 <= lnum <= L2
  dal_mwkeys(key)  from 'mwkeys' return string; if string is empty string
                   there was no match.
*/
function dal_sqlite($dbin,$sql) {
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
function dal_bur_sql($sql) {
// General query on 'bur' database. Table assumed in $sql
$db = "../sqlite/bur.sqlite";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_bur1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "sqlite:../sqlite/bur.sqlite";
// Create (connect to) SQLite database in file
$file_db = new PDO($db);
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "select * from bur where key='$key' order by lnum";
$result = $file_db->query($sql);
//echo "<p>Using PDO...</p>";
$ansarr=array();
foreach($result as $m) {
 $rec = array($m['key'],$m['lnum'],$m['data']);
 $ansarr[]=$rec;
}
//echo "<p>ansarr has " . count($ansarr) . " records</p>\n";
return $ansarr;

}

function sqlite3_dal_bur1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/bur.sqlite";
$sql = "select * from bur where key='$key' order by lnum";
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
function dal_bur2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/bur.sqlite";
$sql="select * from bur where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_bur3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/bur.sqlite";
$sql = "select * from bur where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_bur4a($lnum0,$max) {
$sql = "select * from bur where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_bur_sql($sql);
return $recarr;
}
function dal_bur4b($lnum0,$max) {
$sql = "select * from bur where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `bur` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_bur_sql($sql);
return $recarr;
}

function dal_mwkeys($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from mwkeys for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = "../sqlite/mwkeys.sqlite";
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
$db = "../sqlite/mwkeys.sqlite";
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
$db = "../sqlite/mwkeys.sqlite";
$sql="select * from mwkeys where  $lnum1 <= lnum and lnum <= $lnum2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}

function dal_mwkeys3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/mwkeys.sqlite";
$sql = "select * from mwkeys where key LIKE '$key%'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function unused_dal_sqlite($db,$sql) {
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

?>
