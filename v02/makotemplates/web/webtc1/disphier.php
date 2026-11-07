<?php
require_once(__DIR__ . '/../security_headers.php');
error_reporting( error_reporting() & ~E_NOTICE );
?>
<?php
//web/webtc1/disphier.php
// 06-29-2018. Revised. Except for the input details, this is
// same as webtc/getword.php

//require_once('../webtc/dictcode.php');
require_once('listparm.php');
$getParms = new ListParm();

// BUGHUNT-FINDINGS-03.11.2026 MED-2: the dead JSONP branch is removed. It
// set 'content-type: application/json' whenever ?callback= was passed, but
// $callback was never read and the body echoed was always the HTML table —
// an HTML payload mislabeled as JSON. JSONP consumers must use
// webtc/getword.php, which whitelists and honors the callback. The
// cross-origin readability the branch half-provided is kept with the same
// unconditional ACAO header getword.php sends.
header("Access-Control-Allow-Origin: *");
$meta = '<meta charset="UTF-8">';
echo $meta;  // Why?
require_once('../webtc/getwordClass.php');
//$vm = new GetwordViewModel($getParms);
//$vm = new GetwordClass(); // 11-02-2020
$basicOption = false;
$vm = new GetwordClass($basicOption); // 06-19-2024
$table1 = $vm->table1;

echo $table1;

exit;

?>
