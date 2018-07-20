<?php
/* webtc/dal_sqlite.php  
  This collects all the database access code used by web display programs.
  
  It assumes the data access is via sqlite.
  There are several routines:
  dal_pw1(key) from 'pw' return array of records (key1,lnum1,data1)
            where key1 = key
  dal_pw2(L1,L2) from 'pw' return array of records (key1,lnum1,data1)
            where L1 <= lnum <= L2
*/
function dal_sqlite($dbin,$sql) {
// returns array of records from the sqlite database at filename $db
// according to the SQL query $sql.
// Each record is an array of all the columns (in the order specified
// in the table creation)
$db = "sqlite:$dbin";
//echo "dal_sqlite: db='$db', sql='$sql'\n";
$ansarr=array();
try {
$file_db = new PDO($db);

$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$result = $file_db->query($sql);
foreach($result as $m) {
 if (preg_match('/pwab.sqlite$/',$dbin)) {
  $rec = array($m['id'],$m['data']);
 }else {
  $rec = array($m['key'],$m['lnum'],$m['data']);
}
 $ansarr[]=$rec;
}
}catch (Exception $e) {
    echo '<p>Caught exception: ',  $e->getMessage(), "</p>\n";
    echo "<p>db = $db</p>\n";
    throw new Exception("dal_sqlite error: db=$dbin");
}
return $ansarr;
}
function dal_pw_sql($sql) {
// General query on 'pw' database. Table assumed in $sql
$db = "../sqlite/pw.sqlite";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pw1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "sqlite:../sqlite/pw.sqlite";
// Create (connect to) SQLite database in file
$file_db = new PDO($db);
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "select * from pw where key='$key' order by lnum";
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

function sqlite3_dal_pw1($key) {
// returns an array of records, one for each L-value which matches key.
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pw.sqlite";
$sql = "select * from pw where key='$key' order by lnum";
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
function dal_pw2($L1,$L2) {
// returns an array of records, one for each L-value in the range
// $L1 <= $L <= $L2
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pw.sqlite";
$sql="select * from pw where  $L1 <= lnum and lnum <= $L2  order by lnum"; 
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pw3($key) {
// returns an array of records, which start like $key
// each record is an array with three elements: key,lnum,data
$db = "../sqlite/pw.sqlite";
$sql = "select * from pw where key LIKE '$key%' order by lnum";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
function dal_pw4a($lnum0,$max) {
$sql = "select * from pw where (lnum < '$lnum0') order by lnum DESC LIMIT $max";
$recarr = dal_pw_sql($sql);
return $recarr;
}
function dal_pw4b($lnum0,$max) {
$sql = "select * from pw where ('$lnum0' < lnum) order by lnum LIMIT $max";
//$sql = "select * from `pw` where \"$lnum0\" < `lnum`  order by `lnum`  LIMIT $max";
$recarr = dal_pw_sql($sql);
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
function dal_linkpwauthorities($ls) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from linkpwauthorities for the given 'L'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$dbg=false;
dbgprintdisp1($dbg,"dal_sqlite/dal_linkpwauthorities. ENTER\n");
$db = dal_dbname("linkauth");
// There is a problem when $ls is KUHN\'S.  For sqlite, this should
// be KUHN''S
dbgprintdisp1($dbg,"dal_linkpwauthorities. ls=" . $ls . "\n");
$ls = str_replace("'","''",$ls);  
$sql = "select data from linkauth where key='$ls'";
dbgprintdisp1($dbg,"dal_linkpwauthorities. sql=" . $sql . "\n");
$recarr = dal_sqlite($db,$sql);
dbgprintdisp1($dbg,"dal_linkpwauthorities. back from dal_sqlite\n");
return $recarr; 
}
function dbgprintdisp1($dbg,$text) {
 if (!$dbg) {return;}
 $filename = "dbgdisp.txt";
 $fp1 = fopen($filename,"a");
 fwrite($fp1,"$text");
 fclose($fp1);
}
function dal_pwab($key) {
// returns a string. If empty string, no match. Otherwise, the
// 'data' from pwab for the given 'key'.
//  The structure of 'data' string is a semicolon delimited set of records,
//  each with three values: hcode, L1,L2  
$db = dal_dbname("pwab");
$sql = "select * from pwab where id='$key'";
$recarr = dal_sqlite($db,$sql);
return $recarr;
}
?>
