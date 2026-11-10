<?php
/* H7948 golden-harness stub — same signature and behavior as
 * v02/makotemplates/web/webtc/dbgprint.php (no-op unless $dbg). */
function dbgprint($dbg,$text) {
 if (!$dbg) {return;}
 $fp1 = fopen("php://stderr","a");
 if (!$fp1) {return;}
 fwrite($fp1,"$text");
 fclose($fp1);
}
