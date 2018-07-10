<?php
/* webtc/dal_sqlite.php  ejf Nov 23, 2013
  Modified June 24, 2013.  dal_pwg1 routine uses php PDO.
  This is the only function used in the pwg display.

  This collects all the database access code used by
  webtc and webtc5wa.
  It assumes the data access is via sqlite.
  There are several routines:
  dal_pwg1(key) from 'pwg' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_pwg2(L1,L2) from 'pwg' return array of records (key1,lnum1,data1)
            where L1 <= lnum <= L2
  dal_mwkeys(key)  from 'mwkeys' return string; if string is empty string
                   there was no match.
 12-14-2017.  Added dal_linkpwgauthorities and dbprintdisp1 from pw logic
   changing name to linkpwgauthorities

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
 if (preg_match('/pwgab.sqlite$/',$dbin)) {
  $rec = array($m['id'],$m['data']);
 }else if (preg_match('/pwgbib.sqlite$/',$dbin)) {
  $rec = array($m['id'],$m['code'],$m['codecap'],$m['data']);
 }else {
  $rec = array($m['key'],$m['lnum'],$m['data']);
 }
 $ansarr[]=$rec;
}
return $ansarr;
}
function dal_pwg_sql($sql) {
// General query on 'pwg' database. Table assumed in $sql
$db = "../sqlite/pwg.sqlite";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pwg1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "sqlite:../sqlite/pwg.sqlite";
// Create (connect to) SQLite database in file
$file_db = new PDO($db);
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "select * from pwg where key='$key' order by lnum";
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

function sqlite3_dal_pwg1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pwg.sqlite";
$sql = "select * from pwg where key='$key' order by lnum";
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
function dal_pwg2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pwg.sqlite";
$sql="select * from pwg where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pwg3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pwg.sqlite";
$sql = "select * from pwg where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pwg4a($lnum0,$max) {
$sql = "select * from pwg where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_pwg_sql($sql);
return $recarr;
}
function dal_pwg4b($lnum0,$max) {
$sql = "select * from pwg where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `pwg` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_pwg_sql($sql);
return $recarr;
}

function dal_dbname($pfx) {
 $dir = dirname(__FILE__); //directory containing this php file
 //echo "<p>dal_dbname: dir=$dir</p>";
 // Note: $dir does not end in '/'
 // Remove last component of $dir
 // With XAMPP on Windows, the file-separator is '\'
 //$dir = preg_replace("|/webtc|","",$dir);
 $dir = preg_replace("|.webtc|","",$dir);
 $ans = "$dir" . "/sqlite/$pfx.sqlite";
 return $ans;
}

function dal_linkpwgauthorities($n) {

$db = dal_dbname("pwgbib");
$dbg = false;
dbgprintdisp1($dbg,"dal_linkpwgauthorities. n=" . $n . "\n");
$sql = "select * from pwgbib where id='$n'";
#dbgprintdisp1($dbg,"dal_linkpwgauthorities. sql=" . $sql . "\n");
$recarr = dal_sqlite($db,$sql);
dbgprintdisp1($dbg,"dal_linkpwgauthorities. back from dal_sqlite.");
if (count($recarr) == 1) {
 $rec = $recarr[0];
 
} else {
 $rec = array();
}
return $rec;
}
function dbgprintdisp1($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbgdisp.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}

function dal_pwgab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from pwgab for the given 'key'.
$db = dal_dbname("pwgab");
$sql = "select * from pwgab where id='$key'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
?>
