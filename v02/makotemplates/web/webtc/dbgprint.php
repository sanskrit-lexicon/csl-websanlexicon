<?php
function dbgprint($dbg,$text) {
 if (!$dbg) {return;}
 // BUGHUNT-FINDINGS-03.11.2026 LOW-1: the log used to be the CWD-relative
 // file "dbg_apidev.txt" — created inside the web docroot (world-readable,
 // web-writable) the moment any call site flipped $dbg=true, a slow
 // info-disclosure leak. php://stderr keeps the same call signature but
 // lands in the server error log (console under CLI) instead.
 $fp1 = fopen("php://stderr","a");
 if (!$fp1) {return;}
 fwrite($fp1,"$text");
 fclose($fp1);
}
?>
