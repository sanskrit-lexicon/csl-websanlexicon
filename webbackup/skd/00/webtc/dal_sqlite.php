<?php
/* webtc/dal_sqlite.php  ejf Oct 3, 2013
  Modified June 24, 2013.  dal_skd1 routine uses php PDO.
  This is the only function used in the skd display.

  This collects all the database access code used by
  webtc and webtc5wa.
  It assumes the data access is via sqlite.
  There are several routines:
  dal_skd1(key) from 'skd' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_skd2(L1,L2) from 'skd' return array of records (key1,lnum1,data1)
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
function dal_skd_sql($sql) {
// General query on 'skd' database. Table assumed in $sql
$db = "../sqlite/skd.sqlite";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_skd1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "sqlite:../sqlite/skd.sqlite";
// Create (connect to) SQLite database in file
$file_db = new PDO($db);
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "select * from skd where key='$key' order by lnum";
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

function sqlite3_dal_skd1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/skd.sqlite";
$sql = "select * from skd where key='$key' order by lnum";
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
function dal_skd2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/skd.sqlite";
$sql="select * from skd where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_skd3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/skd.sqlite";
$sql = "select * from skd where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_skd4a($lnum0,$max) {
$sql = "select * from skd where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_skd_sql($sql);
return $recarr;
}
function dal_skd4b($lnum0,$max) {
$sql = "select * from skd where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `skd` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_skd_sql($sql);
return $recarr;
}



?>
