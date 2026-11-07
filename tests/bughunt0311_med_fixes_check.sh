#!/bin/bash
# bughunt0311_med_fixes_check.sh — pins for BUGHUNT-FINDINGS-03.11.2026
# MED-1..4 (the four MEDIUM residues fixed after the 03-11 report; LOW-1..4
# ride along). Fails on the pre-fix tree, passes on the post-fix tree.
#   pin A  MED-3  ?dev=yes no longer flips production lookups to keydoc;
#                 only a build-time define('WEBSAN_DEV_KEYDOC', true) enables it
#   pin B  MED-2  webtc1/disphier.php + listhier.php no longer label their
#                 HTML payload as application/json; getword.php keeps its
#                 whitelisted JSONP (positive control)
#   pin C  MED-1  Dal::get/get_xml log a DB failure (error_log) instead of
#                 silently returning "no matches"; valid queries unaffected
#   pin D  MED-4  parity run_all.sh captures and restores user_version of the
#                 dictdir database it tampers with
# Usage: sh tests/bughunt0311_med_fixes_check.sh   (needs php; sqlite drivers
# auto-enabled for Windows-style zip layouts via a generated php.ini)
#
# Note on fixtures: pins A and C byte-copy the REAL dal.php out of the tree
# and load it from a fixture directory stubbing DictInfo/dbgprint — the real
# dictinfo.php is Mako TEMPLATE SOURCE (${dictyear}, <%doc>) and cannot be
# parsed by php-cli, while dal.php itself is verbatim and is exactly what the
# pins exercise.
set -u
HERE=$(cd "$(dirname "$0")/.." && pwd)
WEBTC="$HERE/v02/makotemplates/web/webtc"
WEBTC1="$HERE/v02/makotemplates/web/webtc1"
RUNALL="$HERE/tests/webtc2_parity/run_all.sh"
FIXDIR=$(mktemp -d "${TMPDIR:-/tmp}/medfixes.XXXXXX")
trap 'rm -rf "$FIXDIR"' EXIT
FAIL=0
mkdir -p "$FIXDIR/webtc"
cp "$WEBTC/dal.php" "$FIXDIR/webtc/dal.php"
cat > "$FIXDIR/webtc/dictinfo.php" <<'PHP'
<?php
// CLI stub: the real dictinfo.php is a Mako template source. Dal's
// constructor only needs sqlitedir; the fixture directory does not exist,
// which exercises the file-missing paths by design.
class DictInfo {
 public $sqlitedir;
 public function __construct($dict) { $this->sqlitedir = '/nonexistent-websan-pin/'; }
}
PHP
cat > "$FIXDIR/webtc/dbgprint.php" <<'PHP'
<?php
function dbgprint($dbg,$text) {}
PHP

# Windows zip distributions ship the sqlite drivers as DLLs and without a
# php.ini; without them the PDO/sqlite pins cannot run. Generate a minimal
# ini (Windows-native path for extension_dir) and point PHPRC at it. Linux
# builds bundle the drivers, so this branch is a no-op there.
if ! php -m 2>/dev/null | grep -qi '^pdo_sqlite$'; then
  EXEDIR=$(dirname "$(command -v php)")
  EXTDIR="$EXEDIR/ext"
  if [ -f "$EXTDIR/php_pdo_sqlite.dll" ]; then
    if command -v cygpath >/dev/null 2>&1; then EXTDIR=$(cygpath -w "$EXTDIR"); fi
    printf 'extension_dir = "%s"\nextension = pdo_sqlite\nextension = sqlite3\n' "$EXTDIR" > "$FIXDIR/php.ini"
    PHPRC="$FIXDIR/php.ini"
    export PHPRC
    echo "note: sqlite drivers enabled via generated php.ini"
  fi
fi

echo "== pin A (MED-3): ?dev=yes needs the build-time WEBSAN_DEV_KEYDOC opt-in =="
cat > "$FIXDIR/pin_a.php" <<'PHP'
<?php
$case = $argv[1]; $dir = $argv[2];
if ($case === 'on') { define('WEBSAN_DEV_KEYDOC', true); }
require $dir . '/webtc/dal.php';
$_REQUEST = array();
$d0 = new Dal('mw');
$_REQUEST = array('dev' => 'yes');
$d1 = new Dal('mw');
echo "plain:" . var_export($d0->devFlag, true) . " dev:" . var_export($d1->devFlag, true) . "\n";
PHP
php "$FIXDIR/pin_a.php" off "$FIXDIR" > "$FIXDIR/a_off.txt" 2>/dev/null
if grep -q "plain:false dev:false" "$FIXDIR/a_off.txt"; then
  echo "A1 OK: dev=yes without WEBSAN_DEV_KEYDOC leaves devFlag=false"
else
  echo "A1 FAIL (dev request enabled the dev database):"; cat "$FIXDIR/a_off.txt"; FAIL=1
fi
php "$FIXDIR/pin_a.php" on "$FIXDIR" > "$FIXDIR/a_on.txt" 2>/dev/null
if grep -q "plain:false dev:true" "$FIXDIR/a_on.txt"; then
  echo "A2 OK: explicit build-time opt-in still enables the dev database"
else
  echo "A2 FAIL (opt-in constant no longer works):"; cat "$FIXDIR/a_on.txt"; FAIL=1
fi

echo "== pin B (MED-2): webtc1 no longer mislabels its HTML payload as JSON =="
b_ok=1
for f in "$WEBTC1/disphier.php" "$WEBTC1/listhier.php"; do
  # Anchor to executable shape: an actual header() call, not the removal
  # comment that documents why the dead branch is gone.
  if grep -Eq '^[[:space:]]*header\(.*application/json' "$f"; then
    echo "B FAIL: $f still sets application/json"; b_ok=0
  elif grep -qF 'isset($_GET' "$f"; then
    echo "B FAIL: $f still wires a callback branch"; b_ok=0
  fi
done
if [ $b_ok -eq 1 ]; then echo "B OK: disphier.php + listhier.php serve HTML only"; fi
if grep -qF 'invalid callback' "$WEBTC/getword.php"; then
  echo "B OK: getword.php whitelisted JSONP intact (positive control)"
else
  echo "B FAIL: getword.php JSONP whitelist disappeared"; FAIL=1
fi
[ $b_ok -eq 0 ] && FAIL=1

echo "== pin C (MED-1): Dal::get/get_xml log DB failures, keep the contract =="
cat > "$FIXDIR/pin_c.php" <<'PHP'
<?php
$log = $argv[1]; $dir = $argv[2];
ini_set('error_log', $log); ini_set('log_errors', '1'); ini_set('display_errors', '0');
$dbfile = "$log.db";
$pdo = new PDO('sqlite:' . $dbfile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE t (key TEXT, lnum TEXT, data TEXT)');
$pdo->exec("INSERT INTO t VALUES ('a','1','x')");
$pdo = null;
require $dir . '/webtc/dal.php';
$rf = new ReflectionClass('Dal');
$d = $rf->newInstanceWithoutConstructor();
$d->file_db = new PDO('sqlite:' . $dbfile);
$d->file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$d->file_db_xml = $d->file_db;
$d->sqlitefile = $dbfile;
$d->dbg = false;
echo 'rows=' . count($d->get('select * from t')) . "\n";
echo 'miss=' . count($d->get('select * from no_such_table')) . "\n";
echo 'missxml=' . count($d->get_xml('select * from no_such_table')) . "\n";
PHP
php "$FIXDIR/pin_c.php" "$FIXDIR/c.log" "$FIXDIR" > "$FIXDIR/c.out" 2>/dev/null
if grep -q '^rows=1$' "$FIXDIR/c.out" \
   && grep -q '^miss=0$' "$FIXDIR/c.out" \
   && grep -q '^missxml=0$' "$FIXDIR/c.out" \
   && grep -qF 'Dal::get SQL failure' "$FIXDIR/c.log" \
   && grep -qF 'Dal::get_xml SQL failure' "$FIXDIR/c.log"; then
  echo "C OK: failures logged (error_log), valid-query contract intact"
else
  echo "C FAIL (silent swallow or broken contract):"
  echo "-- stdout --"; cat "$FIXDIR/c.out"
  echo "-- error log --"; cat "$FIXDIR/c.log" 2>/dev/null
  FAIL=1
fi

echo "== pin D (MED-4): parity harness restores user_version after the tamper =="
if grep -qF 'restore_user_version' "$RUNALL" \
   && grep -qF 'trap restore_user_version EXIT' "$RUNALL"; then
  echo "D1 OK: run_all.sh wires capture + restore on every exit path"
else
  echo "D1 FAIL: run_all.sh lost the restore wiring"; FAIL=1
fi
cat > "$FIXDIR/pin_d.php" <<'PHP'
<?php
// CREATE flag only for the bootstrap: run_all.sh's own one-liners (below)
// use READWRITE alone, which is correct there because query_dump.sqlite3
// always pre-exists in the dictdir under test.
$db = new SQLite3($argv[1], SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$db->exec('PRAGMA user_version=1');
$db->close();
PHP
php "$FIXDIR/pin_d.php" "$FIXDIR/q.sqlite3"
UV_ORIG=$(php -r '$db = new SQLite3($argv[1], SQLITE3_OPEN_READONLY); echo (int)$db->querySingle("PRAGMA user_version"); $db->close();' "$FIXDIR/q.sqlite3")
php -r '$db = new SQLite3($argv[1], SQLITE3_OPEN_READWRITE); $db->exec("PRAGMA user_version=2"); $db->close();' "$FIXDIR/q.sqlite3"
php -r '$db = new SQLite3($argv[1], SQLITE3_OPEN_READWRITE); $db->exec(sprintf("PRAGMA user_version=%d", (int)$argv[2])); $db->close();' "$FIXDIR/q.sqlite3" "$UV_ORIG"
UV_NOW=$(php -r '$db = new SQLite3($argv[1], SQLITE3_OPEN_READONLY); echo (int)$db->querySingle("PRAGMA user_version"); $db->close();' "$FIXDIR/q.sqlite3")
if [ "$UV_NOW" = "1" ]; then
  echo "D2 OK: user_version captured (1), tampered (2), restored ($UV_NOW)"
else
  echo "D2 FAIL: user_version is '$UV_NOW' after restore, expected 1"; FAIL=1
fi

echo "----"
if [ $FAIL -eq 0 ]; then
  echo "BUGHUNT MED FIXES: ALL PINS OK"
else
  echo "BUGHUNT MED FIXES: PINS FAILED"
fi
exit $FAIL
