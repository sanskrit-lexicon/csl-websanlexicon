#!/bin/bash
# run_golden.sh — H7948/H7962 golden gate for the basicadjust.php
# link-target chain (ls_callback_pwg_href). One command, four checks:
#   1. fixture provenance: basicadjust_pre_H7948.php is a content-exact copy of
#      v02/makotemplates/web/webtc/basicadjust.php at base commit $BASE;
#   2+3. named corpus (corpus.tsv) + fuzz corpus (gen_fuzz.php cartesian
#      product): old-vs-new differs ONLY on the declared roman rows
#      (intentional_divergences.tsv — the H7962 ṚV. PRĀT. roman-rule fix);
#      everything else byte-identical;
#   4. expected values (roman_expected.tsv): old side (null), new side ==
#      pinned rvps/app2 hrefs (I–L coverage).
# Exit 0 = parity proven modulo the declared roman divergences.
# Usage: sh tests/golden_link_targets/run_golden.sh
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
# Compare content-exact modulo line endings: `git show` emits the raw LF blob,
# while Windows autocrlf checkouts materialize working files with CRLF (the
# fixture has mixed LF/CRLF from its two checkout histories). sed 's/\r$//'
# strips ONLY the trailing CR per line — a real content difference still fails.
sed 's/\r$//' < "$TMP/h7948_fixture_check.php" > "$TMP/h7948_fixture_check.lf"
sed 's/\r$//' < "$HERE/basicadjust_pre_H7948.php" > "$TMP/h7948_fixture.lf"
if ! cmp -s "$TMP/h7948_fixture_check.lf" "$TMP/h7948_fixture.lf"; then
  echo "GOLDEN FAIL: fixture is not a content-exact copy of the pre-H7948 file (modulo EOL)"; exit 1
fi
echo "fixture provenance OK (content-exact copy of $BASE, modulo EOL)"

echo "== check 2+3: named + fuzz corpora, old-vs-new =="
"$PHPBIN" "$HERE/run_golden.php" old "$HERE/corpus.tsv" > "$TMP/h7948_old.txt" 2>"$TMP/h7948_old.err"
"$PHPBIN" "$HERE/run_golden.php" new "$HERE/corpus.tsv" > "$TMP/h7948_new.txt" 2>"$TMP/h7948_new.err"
"$PHPBIN" "$HERE/gen_fuzz.php" > "$TMP/h7948_fuzz.tsv" 2>"$TMP/h7948_fuzz.err"
"$PHPBIN" "$HERE/run_golden.php" old "$TMP/h7948_fuzz.tsv" > "$TMP/h7948_fuzz_old.txt" 2>/dev/null
"$PHPBIN" "$HERE/run_golden.php" new "$TMP/h7948_fuzz.tsv" > "$TMP/h7948_fuzz_new.txt" 2>/dev/null
if ! "$PHPBIN" "$HERE/check_divergence.php" \
     "$TMP/h7948_old.txt" "$TMP/h7948_new.txt" "$HERE/corpus.tsv" \
     "$TMP/h7948_fuzz_old.txt" "$TMP/h7948_fuzz_new.txt" "$TMP/h7948_fuzz.tsv" \
     "$HERE/intentional_divergences.tsv"; then
  echo "GOLDEN FAIL: divergence gate failed (see above)"; exit 1
fi

echo "== check 4: roman expected values (roman_expected.tsv) =="
if ! "$PHPBIN" "$HERE/run_expected.php"; then
  echo "GOLDEN FAIL: expected-value gate failed (see above)"; exit 1
fi

echo "GOLDEN OK — byte-identical old-vs-new except the declared roman rows (intentional_divergences.tsv)"
