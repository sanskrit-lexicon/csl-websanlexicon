#!/bin/bash
# bughunt0311_crash_envelope_check.sh — pin for BUGHUNT-FINDINGS-03.11.2026 HIGH-1.
#
# getword_data.php throws RuntimeException (H3636 A10/A12) for malformed
# records. Before the fix, nothing in the request path caught it: every entry
# point (webtc/getword.php, webtc1/disphier.php, webtc2/query_gather1.php)
# died with an uncaught fatal (blank HTTP 500, no error envelope). The fix
# wraps the Getword_data construction in getwordClass (the chokepoint all
# three entry paths share).
#
# Behavioral pin: runs the REAL getwordClass.php against a stub fixture whose
# Getword_data constructor throws — the same exception class the real
# getword_data.php throws. Fails on the pre-fix tree (uncaught fatal), passes
# on the post-fix tree (error envelope, status=false).
# Usage: sh tests/bughunt0311_crash_envelope_check.sh
set -u
HERE=$(cd "$(dirname "$0")/.." && pwd)
REAL="$HERE/v02/makotemplates/web/webtc/getwordClass.php"
FIXDIR=$(mktemp -d /tmp/opencode/crash-envelope.XXXXXX)
trap 'rm -rf "$FIXDIR"' EXIT

# --- stub fixture mimicking the real contract ---
cat > "$FIXDIR/parm.php" <<'PHP'
<?php class Parm { public $key='x'; public $keyin1='y'; }
PHP
cat > "$FIXDIR/dbgprint.php" <<'PHP'
<?php function dbgprint($dbg,$text) {}
PHP
cat > "$FIXDIR/dispitem.php" <<'PHP'
<?php
PHP
cat > "$FIXDIR/getword_data.php" <<'PHP'
<?php class Getword_data {
 public $matches = array();
 public $xmlmatches = array();
 public function __construct($basicOption = true) {
  // same exception class + site as getword_data.php L45/L117/L137
  throw new RuntimeException('fixture: malformed dictionary record');
 }
}
PHP

# --- harness: include the REAL class, exercise the constructor ---
cat > "$FIXDIR/harness.php" <<'PHP'
<?php
require $argv[1];
$g = new GetwordClass();
fwrite(STDERR, "status=" . var_export($g->status, true) . "\n");
if (strpos($g->table1, 'Dictionary record error') === false) {
 fwrite(STDERR, "NO-ENVELOPE\n");
 exit(1);
}
fwrite(STDERR, "ENVELOPE-OK\n");
PHP

OUT=$(cd "$FIXDIR" && php -d display_errors=0 -d log_errors=0 harness.php "$REAL" 2>&1)
RC=$?
echo "$OUT"
if [ $RC -ne 0 ]; then
 echo "CRASH-ENVELOPE GATE FAIL (rc=$RC): uncaught fatal or no envelope"
 exit 1
fi
echo "CRASH-ENVELOPE GATE OK"
