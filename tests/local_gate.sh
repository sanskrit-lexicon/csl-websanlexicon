#!/bin/bash
# tests/local_gate.sh — G88 (H5778): the ONE local deterministic gate this repo
# never had (H1668 verdict: "no local deterministic gate — Semgrep is CI-only").
#
# Mirrors the ci.yml php-lint + python-lint lanes in one exit code so the same
# facts CI checks are checkable on a dev box WITHOUT a deployed dictionary
# tree (tests/webtc2_parity/run_all.sh stays the deep parity lane — it needs a
# live dictdir + query_dump.sqlite3 and cannot run bare).
#
# Lanes (identical rules to .github/workflows/ci.yml):
#   1. php -l over v02/makotemplates/**/*.php, skipping Mako directives
#      (<%, ${, % line-directives) — those compile in lane 3;
#   2. ast.parse over v02/*.py generator scripts (fatal);
#   3. Mako Template() compile over the same template set (fatal);
#   4. ruff check (WARN-only, exactly as CI keeps it advisory).
# Usage: sh tests/local_gate.sh
set -u
HERE=$(cd "$(dirname "$0")/.." && pwd)
cd "$HERE"
fail=0

echo "== lane 1: php -l on verbatim PHP templates (mako-skipped) =="
linted=0; skipped=0
TMP_LIST=$(mktemp)
find v02/makotemplates -name '*.php' > "$TMP_LIST"
while IFS= read -r f; do
  if grep -qE '<%|\$\{|^[[:space:]]*%[[:space:]]' "$f"; then
    skipped=$((skipped+1)); continue
  fi
  if ! php -l "$f" >/dev/null 2>&1; then
    echo "PHP SYNTAX FAIL: $f"; php -l "$f" 2>&1 | head -2; fail=1
  fi
  linted=$((linted+1))
done < "$TMP_LIST"
rm -f "$TMP_LIST"
echo "php -l: linted=$linted skipped(mako)=$skipped"

echo "== lane 2: parse-check generator scripts =="
python3 - <<'PY'
import ast, glob, sys
bad = 0
for f in glob.glob('v02/*.py'):
    try:
        ast.parse(open(f, encoding='utf-8').read())
    except SyntaxError as e:
        print(f'PARSE FAIL {f}: {e}'); bad = 1
print('v02 generator scripts parse OK' if not bad else 'parse errors')
sys.exit(bad)
PY
[ $? -eq 0 ] || fail=1

echo "== lane 3: mako compile-check templates =="
python3 - <<'PY'
import glob, sys
from mako.template import Template
bad = 0; ok = 0
for f in glob.glob('v02/makotemplates/**/*.php', recursive=True):
    try:
        Template(filename=f, input_encoding='utf-8'); ok += 1
    except Exception as e:
        print(f'MAKO FAIL {f}: {type(e).__name__}: {e}'); bad = 1
print(f'mako compile: ok={ok} bad={bad}')
sys.exit(bad)
PY
[ $? -eq 0 ] || fail=1

echo "== lane 4: ruff (warn-only, mirrors CI) =="
if command -v ruff >/dev/null 2>&1; then
  ruff check . || echo "WARN: ruff reported findings (advisory, as in CI)"
else
  echo "WARN: ruff not installed — lane skipped (advisory lane)"
fi

if [ $fail -eq 0 ]; then
  echo "LOCAL GATE OK"
else
  echo "LOCAL GATE FAIL"
fi
exit $fail
