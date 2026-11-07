<?php
require_once(__DIR__ . '/../security_headers.php');
// web/webtc1/listhier.php
// Revised 06-28-2018 to work with revised webtc programs

// BUGHUNT-FINDINGS-03.11.2026 MED-2: the dead JSONP branch is removed (same
// defect as webtc1/disphier.php): it labeled the echoed HTML table as
// 'application/json' when ?callback= was passed, without ever reading
// $callback. This endpoint serves HTML; JSONP consumers must use
// webtc/getword.php. Cross-origin readability is kept via the unconditional
// ACAO header getword.php also sends.
header("Access-Control-Allow-Origin: *");
$meta = '<meta charset="UTF-8">';
echo $meta; // why?

require_once('../webtc/dictcode.php');
require_once('listparm.php');
$getParms = new ListParm($dictcode);

require_once('listhiermodel.php');
$model = new ListHierModel($getParms);
$listmatches = $model->listmatches;
require_once('listhierview.php');
$view = new ListHierView($listmatches,$getParms);
$table = $view->table;
if (in_array($getParms->dict,array('ae','mwe','bor'))) {
 // for dictionaries with english headwords, no transcoding of list of words
 // This accomplished by transcoding from slp1 to slp1
 $table1 = transcoder_processElements($table,"slp1","slp1","SA");
} else {
 $table1 = transcoder_processElements($table,"slp1",$getParms->filter,"SA");
}
echo $table1;
exit;

?>
