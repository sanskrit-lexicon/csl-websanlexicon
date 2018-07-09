<?php
/* webtc/dal_sqlite.php  
  This collects all the database access code used by web display programs.
  
  It assumes the data access is via sqlite.
  There are several routines:
  dal_cae1(key) from 'cae' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_cae2(L1,L2) from 'cae' return array of records (key1,lnum1,data1)
            where L1 <= lnum <= L2
  10-30-2017. Added dal_caeab
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
 if (preg_match('/caeab.sqlite$/',$dbin)) {
  $rec = array($m['id'],$m['data']);
 }else {
  $rec = array($m['key'],$m['lnum'],$m['data']);
 }
 $ansarr[]=$rec;
}
return $ansarr;
}
function dal_cae_sql($sql) {
// General query on 'cae' database. Table assumed in $sql
$db = "../sqlite/cae.sqlite";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_cae1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "sqlite:../sqlite/cae.sqlite";
// Create (connect to) SQLite database in file
$file_db = new PDO($db);
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "select * from cae where key='$key' order by lnum";
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

function sqlite3_dal_cae1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/cae.sqlite";
$sql = "select * from cae where key='$key' order by lnum";
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
function dal_cae2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/cae.sqlite";
$sql="select * from cae where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_cae3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/cae.sqlite";
$sql = "select * from cae where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_cae4a($lnum0,$max) {
$sql = "select * from cae where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_cae_sql($sql);
return $recarr;
}
function dal_cae4b($lnum0,$max) {
$sql = "select * from cae where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `cae` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_cae_sql($sql);
return $recarr;
}
function dal_caeab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from caeab for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("caeab");
$sql = "select * from caeab where id='$key'";
$recarr = dal_sqlite($db,$sql);
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



?>
