<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php
//web/webtc1/disphier.php
// 06-29-2018. Revised. Except for the input details, this is
// same as webtc/getword.php

require_once('../webtc/dictcode.php');
require_once('listparm.php');
$getParms = new ListParm($dictcode);

if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
 header("Access-Control-Allow-Origin: *");
}
$meta = '<meta charset="UTF-8">'; 
echo $meta;  // Why?
require_once('../webtc/getwordviewmodel.php');
$vm = new GetwordViewModel($getParms);
$table1 = $vm->table1;

echo $table1;

exit;

?>
