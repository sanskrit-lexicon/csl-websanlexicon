<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php
//web/webtc/getword.php

if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
}
header("Access-Control-Allow-Origin: *");
require_once('getwordviewmodel.php');
function getwordCall() {
 require_once('parm.php');
 //$getParms = new Parm($dictcode);  
 $getParms = new Parm();  
  $temp = new GetwordViewModel($getParms);
  $table1 = $temp->table1;
  if (isset($_GET['callback'])) {
   $json = json_encode($table1);
   echo "{$_GET['callback']}($json)";
  }else {
   echo $table1;
  }
 }
 getwordCall();
?>
