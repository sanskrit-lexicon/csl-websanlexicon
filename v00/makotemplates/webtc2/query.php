<?php
// modified June 24, 2013 to allow search for sanskrit words in body of text
// modified Nov 25,2013 for pwg.
if (isset($_GET['callback'])) {
 header('content-type: application/json; charset=utf-8');
 header("Access-Control-Allow-Origin: *");
}
$meta = '<meta charset="UTF-8">';
#echo $meta; // why?
$dbg = false;
require_once("../webtc/dbgprint.php");
dbgprint($dbg,"query.php starts\n");
#include("../utilities/transcoder.php");
require_once('../webtc/dictcode.php');
require_once('queryparm.php');
dbgprint($dbg,"query.php call QueryParm: dictcode=$dictcode\n");
$getParms = new QueryParm($dictcode);

require_once('querymodel.php');
$model = new QueryModel($getParms);
if ($model->status == false) {
 echo $model->errmsg;
 exit;
}
$matches = $model->querymatches;
$lastLnum = $model->lastLnum;


require_once('querylistview.php');
$view = new QueryListView($getParms,$model);
$outputarr = $view->resultarr;
$disp = join('#',$outputarr);
$table1 = transcoder_processElements($disp,"slp1",$getParms->filter,"SA");
echo $table1;

exit;
?>
