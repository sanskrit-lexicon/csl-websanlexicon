#!/bin/bash
# run_golden.sh — H7948 golden parity gate for the basicadjust.php
# link-target refactor (ls_callback_pwg_href). One command, three checks:
#   1. fixture provenance: basicadjust_pre_H7948.php is a byte-exact copy of
#      v02/makotemplates/web/webtc/basicadjust.php at base commit $BASE;
#   2. named corpus (corpus.tsv): old vs new outputs byte-identical;
#   3. fuzz corpus (gen_fuzz.php cartesian product): same.
# Exit 0 = parity proven. Usage: sh tests/golden_link_targets/run_golden.sh
set -u
HERE=$(cd "$(dirname "$0")" && pwd)
REPO=$(cd "$HERE/../.." && pwd)
BASE=d090a1076bcd019e595e1cb9f70571f047d6ce04
TMP=${TMPDIR:-/tmp}

PHPBIN=php
if ! php -r 'exit(0);' >/dev/null 2>&1; then
  echo "GOLDEN FAIL: no usable php interpreter"; exit 1
fi

echo "== check 1: fixture provenance vs base commit $BASE =="
git -C "$REPO" show "$BASE:v02/makotemplates/web/webtc/basicadjust.php" > "$TMP/h7948_fixture_check.php" 2>/dev/null
if [ ! -s "$TMP/h7948_fixture_check.php" ]; then
  echo "GOLDEN FAIL: cannot read base file from git ($BASE)"; exit 1
fi
if ! cmp -s "$TMP/h7948_fixture_check.php" "$HERE/basicadjust_pre_H7948.php"; then
  echo "GOLDEN FAIL: fixture is not a byte-exact copy of the pre-H7948 file"; exit 1
fi
echo "fixture provenance OK (byte-exact copy of $BASE)"

echo "== check 2: named corpus (corpus.tsv) old-vs-new =="
"$PHPBIN" "$HERE/run_golden.php" old "$HERE/corpus.tsv" > "$TMP/h7948_old.txt" 2>"$TMP/h7948_old.err"
"$PHPBIN" "$HERE/run_golden.php" new "$HERE/corpus.tsv" > "$TMP/h7948_new.txt" 2>"$TMP/h7948_new.err"
if ! diff -u "$TMP/h7948_old.txt" "$TMP/h7948_new.txt" > "$TMP/h7948_diff.txt"; then
  echo "GOLDEN FAIL: corpus diff not empty:"; cat "$TMP/h7948_diff.txt"; exit 1
fi
echo "corpus parity OK ($(wc -l < "$TMP/h7948_old.txt" | tr -d ' ') cases, diff empty)"

echo "== check 3: fuzz corpus (gen_fuzz.php) old-vs-new =="
"$PHPBIN" "$HERE/gen_fuzz.php" > "$TMP/h7948_fuzz.tsv" 2>"$TMP/h7948_fuzz.err"
"$PHPBIN" "$HERE/run_golden.php" old "$TMP/h7948_fuzz.tsv" > "$TMP/h7948_fuzz_old.txt" 2>/dev/null
"$PHPBIN" "$HERE/run_golden.php" new "$TMP/h7948_fuzz.tsv" > "$TMP/h7948_fuzz_new.txt" 2>/dev/null
if ! diff -u "$TMP/h7948_fuzz_old.txt" "$TMP/h7948_fuzz_new.txt" > "$TMP/h7948_fuzz_diff.txt"; then
  echo "GOLDEN FAIL: fuzz diff not empty (first 40 lines):"; head -40 "$TMP/h7948_fuzz_diff.txt"; exit 1
fi
echo "fuzz parity OK ($(wc -l < "$TMP/h7948_fuzz_old.txt" | tr -d ' ') cases, diff empty)"

echo "GOLDEN OK — byte-identical old-vs-new on named + fuzz corpora"
