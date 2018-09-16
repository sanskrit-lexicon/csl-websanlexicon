<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php
//web/webtc/getword.php
include('dictcode.php'); # init $dictcode variable
require_once('parm.php');
$getParms = new Parm($dictcode);  

if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
}
 header("Access-Control-Allow-Origin: *");
$meta = '<meta charset="UTF-8">'; 
echo $meta;  // Why?
require_once('getwordviewmodel.php');
$vm = new GetwordViewModel($getParms);
$table1 = $vm->table1;

echo $table1;

exit;

?>
