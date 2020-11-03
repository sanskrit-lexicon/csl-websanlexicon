<?php
// Exclude WARNING messages also, to solve Peter Scharf Mac version.
error_reporting( error_reporting() & ~E_NOTICE & ~E_WARNING );
?>
<?php
//getword.php. 11-02-2020. This is the same as csl-apidev/getword.php
// except using GetwordViewModel instead of GetwordClass

if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
}
header("Access-Control-Allow-Origin: *");
require_once('getwordviewmodel.php');
function getwordCall() {
 //require_once('parm.php');
 //$getParms = new Parm();  
 //$temp = new GetwordViewModel($getParms);
  $temp = new GetwordViewModel();
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
