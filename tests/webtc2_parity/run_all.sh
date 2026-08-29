#!/bin/bash
# run_all.sh — H3633 (G3) parity + timing matrix for ONE dictionary tree.
# Usage: sh run_all.sh <dictdir> <pywork-repo> <websanlexicon-main-qm> [maxrep]
set -e
DICTDIR=$(cd "$1" && pwd)
PW=$(cd "$2" && pwd)
OLDQM="$3"
MAXREP=${4:-3}
HERE=$(cd "$(dirname "$0")" && pwd)
W2="$DICTDIR/web/webtc2"

cp "$OLDQM" "$W2/qm_old.php"
cp "$PW/v02/makotemplates/web/webtc2/querymodel.php" "$W2/qm_new.php"
cp "$HERE/run_parity.php" "$W2/run_parity.php"
cp "$HERE/queries.json" "$W2/queries.json"

cd "$W2"
echo "== phase A: legacy querymodel (current main scan; index ignored) =="
php run_parity.php "$DICTDIR" queries.json ./qm_old.php --maxrep="$MAXREP" > parity_A_old.ndjson
echo "== phase B: new querymodel + generation-time index =="
php run_parity.php "$DICTDIR" queries.json ./qm_new.php --maxrep="$MAXREP" > parity_B_new_index.ndjson
echo "== phase C: new querymodel, index hidden (flat-file fallback) =="
php run_parity.php "$DICTDIR" queries.json ./qm_new.php --maxrep="$MAXREP" --hide-index > parity_C_new_fallback.ndjson

echo "== A vs B (old scan vs new index) =="
python3 "$HERE/compare_parity.py" parity_A_old.ndjson parity_B_new_index.ndjson old-scan new-index
AB=$?
echo "== A vs C (old scan vs new fallback) =="
python3 "$HERE/compare_parity.py" parity_A_old.ndjson parity_C_new_fallback.ndjson old-scan new-fallback
AC=$?
echo "== phase D: stale-index guard (user_version tampered) =="
php -r '$db = new SQLite3("query_dump.sqlite3", SQLITE3_OPEN_READWRITE); $db->exec("PRAGMA user_version=2"); $db->close();'
php run_parity.php "$DICTDIR" queries.json ./qm_new.php --maxrep=1 > parity_D_stale_guard.ndjson
python3 "$HERE/compare_parity.py" parity_A_old.ndjson parity_D_stale_guard.ndjson old-scan stale-index-guard
AD=$?
if [ $AB -eq 0 ] && [ $AC -eq 0 ] && [ $AD -eq 0 ]; then
  echo "PARITY OK for $DICTDIR"
else
  echo "PARITY FAIL for $DICTDIR (AB=$AB AC=$AC AD=$AD)"
  exit 1
fi
