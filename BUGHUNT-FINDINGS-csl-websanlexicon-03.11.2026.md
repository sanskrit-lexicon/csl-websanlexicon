# BUGHUNT-FINDINGS — csl-websanlexicon — 03.11.2026

_Run: nightly one-repo bug hunt (MG ruling 26-09-2026), 2026-11-03.
Elapsed: ~35 min wall (hunt + evidence + report + same-run HIGH auto-fix).
Scope: active code only — `v02/makotemplates/web/` (webtc, webtc1, webtc2,
mobile1, utilities), `v02/*.py` generators, root `backup.py`, `tests/`.
Out of scope (backup/archive data, not live code): `webbackup/`, `v00/`
(legacy generation tree, superseded by v02), vendored JS (`jquery.min.js`,
`js.cookie.min.js`, `transcoderjs/`), data giants.
All findings carry file:line + live evidence commands actually run on
2026-11-03 against `origin/main` @ `55f8b71`._

## Summary

1 HIGH (crash class — auto-fixed same-run, see Phase-3 section), 4 MEDIUM
(report only), 6 LOW (report only), plus one negative finding (twin sync is
clean). No committed secrets, no SQLi, no reachable XSS found: the A13+A14
parameterization sweep, H1523 key/attr binding, H3636 fail-loud, and JSONP
callback whitelists from earlier passes are all holding.

## HIGH (1)

### HIGH-1 · CRASH · Uncaught RuntimeException in the word-display endpoint = blank 500, no error envelope (auto-fix follows in this run)

- **Where:**
  [`getword_data.php` L45/L117/L137](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/getword_data.php#L45)
  throw `RuntimeException` (H3636 A10/A12 "fail loud with an error envelope");
  callers
  [`getwordClass.php` L21](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/getwordClass.php#L21)
  and
  [`getword.php` L13/L27](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/getword.php#L13)
  never catch.
- **Why it is a bug:** A10's own comment promises an "error envelope instead of
  a blank HTTP 200", but no try/catch exists anywhere in the request path, so
  one malformed dictionary record (XML parse failure, malformed MW page-info,
  leftover markup in key2) turns into an **uncaught fatal → blank HTTP 500** —
  worse than the blank-200 it replaced, and with no envelope at all.
- **Class, not instance:** three entry points reach the same throw sites via
  `GetwordClass` — `webtc/getword.php`, `webtc1/disphier.php` L24,
  `webtc2/query_gather1.php` L44. The fix must sit at the chokepoint
  (`getwordClass` constructor), not in one entry file.
- **Evidence (run 2026-11-03):**
  - `grep -rn 'try\s*{\|catch\|RuntimeException' getword.php getwordClass.php dispitem.php`
    → zero hits (throw sites unguarded in-path).
  - `grep -rn 'new GetwordClass' v02/makotemplates/web/` → the three entry
    points above.
  - Runtime shape proof: `php -r 'error_reporting(E_ALL); ini_set("display_errors","0"); throw new RuntimeException("test");'`
    → `PHP Fatal error: Uncaught RuntimeException …`, exit=255 (with
    `display_errors=0` the body is empty).
- **Disposition:** CODE bug → auto-fixed in this run (Phase-3 section below).

## MEDIUM (4 — report only per night ruling)

### MED-1 · SWALLOWED ERROR · dal.php get()/get_xml() turn DB failures into silent "no matches"

[`dal.php` L126–134/L144–152](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/dal.php#L126):
`catch (PDOException $e) { return $ansarr; }` — the exception is neither
logged nor surfaced, while the same file runs `PDO::ERRMODE_EXCEPTION`
(dal.php L86). Any SQL error (corrupt db, schema drift, disk full) produces
exactly the response a missing headword produces; with the H4227
report-everything policy this is the one place defects still vanish.
Evidence: sed of the two catch blocks (empty return, no `$e` use).

### MED-2 · BROKEN CONTRACT · webtc1 dead JSONP branch sets `application/json` but echoes raw HTML

[`disphier.php` L12–21](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc1/disphier.php#L12)
and
[`listhier.php` L6–9](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc1/listhier.php#L6):
`isset($_GET['callback'])` sets `content-type: application/json` (and CORS),
but `$callback` is never read and the body echoed is the HTML table
(`echo $table1`). Unlike `webtc/getword.php` (callback whitelisted and used),
these two branches are dead wiring that mislabels an HTML payload as JSON.
No XSS (nothing is echoed back), but any consumer passing `?callback=` gets a
mislabeled response. Evidence: sed of both files; `$callback` grep = 0 uses.

### MED-3 · DEAD CONFIG WIRING · public `?dev=yes` flips production lookups to the keydoc DB

[`dal.php` L38–43](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/dal.php#L38)
+ [L93](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/dal.php#L93):
`$_REQUEST['dev']=='yes'` sets `devFlag`, which makes `Dal` open
`keydoc.sqlite` when present, and `get1_mwalt()` (L371) then routes lookups
through `get1_keydoc()`. There is no server-side gate — any visitor can
toggle lookup semantics on a deployed tree that ships the keydoc file. Needs
a build-time constant or deploy decision (not a one-line code fix), hence
report-only. Evidence: grep devFlag — only consumer is the keydoc open.

### MED-4 · TEST INFRA · parity phase D permanently tampers `user_version` of the real dictdir DB, never restores

[`run_all.sh` L31–32](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/tests/webtc2_parity/run_all.sh#L31):
`php -r '…PRAGMA user_version=2…'` mutates the **live** `query_dump.sqlite3`
in the dictdir under test; no restore anywhere in the tree (grep
`user_version` across tests/ + v02 → only this write and the reader at
querymodel.php L75). Effect after the drill: the fresh-index lane is silently
disabled (querymodel falls back to the scan on `user_version != 1`) until the
index is regenerated. Derived data, so not data-loss, but the test leaves the
fixture worse than it found it. Evidence: grep — single write site, zero
restore sites.

## LOW (6 — report only)

### LOW-1 · dbgprint writes a relative-path log into the docroot
[`dbgprint.php` L4–10](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/makotemplates/web/webtc/dbgprint.php#L4):
`$filename = "dbg_apidev.txt"` is CWD-relative → a web-writable, publicly
readable file in the docroot the moment any call site flips `$dbg=true`.
Latent (all 248 call sites currently pass false), but the failure mode is a
slow info-disclosure leak. Suggest an absolute log dir or `php://stderr`.

### LOW-2 · local_gate lane 3 fails undiagnosably without mako
Ran `sh tests/local_gate.sh` on this box: lane 3's heredoc died on
`ModuleNotFoundError: No module named 'mako'` (raw traceback, no summary
line, verdict `LOCAL GATE FAIL` with no named reason). CI installs mako
(ci.yml L59 `pip install ruff mako`) so CI is fine, but the gate's own claim
("checkable on a dev box") breaks on machines without mako. Suggest
fail-closed with `MAKO NOT INSTALLED — lane skipped is not allowed; pip
install mako`.

### LOW-3 · generate.py makedirs swallows all OSError
[`v02/generate.py` L45–51](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/generate.py#L45):
`except OSError: pass` catches more than EEXIST (permission denied passes
silently and resurfaces later as a confusing copyfile error). Suggest
`except FileExistsError`.

### LOW-4 · generate.py mutates the module-level alldictparms
[`v02/generate.py` L120–121](https://github.com/gasyoun/csl-websanlexicon/blob/55f8b71b81c32c40b9eac371a33eaff0a626cfc4/v02/generate.py#L120):
`dictparms['dictversion'] = dictparms['dictversion'] + microversion` mutates
the shared dict; harmless as a once-per-process CLI, but the value
accumulates if the module is ever imported and run twice in-process.

### LOW-5 · H4227 error-reporting policy covers only the getword.php path
`getword_data.php` sets `error_reporting(E_ALL)` (H4227 header) and wins by
require-order inside getword.php — but `servepdf.php` L2,
`webtc1/disphier.php` L2, `webtc1/listhier.php` L2 still blanket-suppress
(`E_ALL & ~E_NOTICE & ~E_WARNING`), and `getword.php` L4 itself still carries
the old suppression line as dead code. The policy is per-file drift, not a
shared bootstrap.

### LOW-6 · ruff advisory debt in v02+tests
`ruff check v02 tests` → ~230 findings, dominated by UP025 (143, unicode
literals) + UP031 (47, % formatting) in `v02/imagework/*` and
`tests/webtc2_parity/*`; CI keeps ruff warn-only so this is posture, not
breakage. No F/E-class findings in the served templates.

## Negative finding (checked, clean)

- **apidev twins are in sync** — `diff` of `basicadjust.php`,
  `basicdisplay.php`, `getword_data.php` vs `csl-apidev` **origin/main**:
  byte-identical (H4227 headers present on both sides). An initial apparent
  drift was a stale **local** clone of csl-apidev (behind 7 commits), not a
  repo bug — verified with `git show origin/main:basicadjust.php`.
- No committed secrets: password/apikey/token greps over `backup.py`,
  `v02/*.py`, `v00/*.py` → 0 hits; no `exec/system/shell_exec` on user input
  in the template trees; SQL paths parameterized (b913637) with `(float)` /
  `(int)` casts and table-name charset guards (dal.php L52–55, L164–170).

## What verified green (live evidence, 2026-11-03)

- `sh tests/local_gate.sh` lane 1: **php -l, 26 files linted, 0 syntax
  failures** (9 mako-directive files correctly skipped).
- Lane 2: `ast.parse` over all `v02/*.py` → OK.
- `python3 -m py_compile` over all tracked `.py` → only the expected
  Python-2-syntax failures inside the `webbackup/` archive (out of scope;
  live-code .py all compile).
- `php -l v02/makotemplates/web/webtc/getwordClass.php` → clean.
- JSONP guards on `webtc/getword.php` and `webtc2/query.php`: callback
  whitelist regex present and active (code read + grep).
- `servepdf.php` traversal audit: page→filename resolution goes through
  server-side `pdffiles.txt` (no user-controlled path join); unresolvable
  pages degrade to page 1 (servepdfClass.php L151 `if(!$ncur){$ncur=1;}`).

## Phase-3 section — same-run auto-fix (HIGH-1)

Fix lane ran in this session per MG ruling 26-09-2026 (HIGH code bugs fixed
in-run, ≤3 attempts). See the follow-up fix PR: chokepoint try/catch in
`getwordClass` (class-fix covering getword.php + disphier.php +
query_gather1.php), failing-before/pinned check + CHANGELOG bullet in the fix
PR body. MEDIUM/LOW rows stay report-only; MED-1..4 residues are recorded as
one GTD `@DO` row linking this report.

— Гасунс · bughunt nightly rotation · 2026-11-03
