<?php
// web/webtc1/listhier.php
// Revised 06-28-2018 to work with revised webtc programs

if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
 header("Access-Control-Allow-Origin: *");
}
$meta = '<meta charset="UTF-8">';
echo $meta; // why?

require_once('../webtc/dictcode.php');
require_once('listparm.php');
$getParms = new ListParm($dictcode);
#echo "<p>listhier.php: dictcode=$dictcode</p>";

require_once('listhiermodel.php');
$model = new ListHierModel($getParms);
$listmatches = $model->listmatches;
require_once('listhierview.php');
$view = new ListHierView($listmatches,$getParms);
$table = $view->table;
$table1 = transcoder_processElements($table,"slp1",$getParms->filter,"SA");
echo $table1;
exit;

?>
