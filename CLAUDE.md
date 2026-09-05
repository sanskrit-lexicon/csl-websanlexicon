# CLAUDE.md

_Created: 06-05-2026 · Last updated: 05-09-2026_

**csl-websanlexicon** is the shared CDSL **web frontend**. A Python + Mako
generator under [`v02/`](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02)
renders each dictionary into a PHP + SQLite app. It is not a dictionary and
not the XML builder — content lives in
[csl-orig](https://github.com/sanskrit-lexicon/csl-orig); databases are built
by [csl-pywork](https://github.com/sanskrit-lexicon/csl-pywork).

Operator manual (read this, do not re-derive):
[docs/WEB_FRONTEND_MANUAL.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/WEB_FRONTEND_MANUAL.md).

## How to run

```sh
cd v02
sh generate_web.sh gra tempparent/gra
```

Windows trap: the wrapper calls `python3`. Run the equivalent directly if
needed: `python generate.py gra inventory.txt makotemplates distinctfiles/gra tempparent/gra`.

The generator ships **no data**. Copy `<dict>.sqlite` and
`webtc2/query_dump.txt` from the csl-pywork build into the output `web/`
tree, then serve under XAMPP / Apache.

### Fork-sync (`basicadjust.php` / `basicdisplay.php`)

[`v02/makotemplates/web/webtc/basicadjust.php`](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php)
and [`basicdisplay.php`](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php)
(plus `getword_data.php`) are **hand-synced forks** of the same files in
[csl-apidev](https://github.com/sanskrit-lexicon/csl-apidev). Any edit here
must be propagated the same cycle (`v02/apidev_copy.sh` — hardcoded
`/c/xampp/htdocs/cologne/` paths; otherwise copy by hand). Before assuming
either side is current, run
[`/cologne-fork-sync-check`](https://github.com/gasyoun/claude-config/blob/main/commands/cologne-fork-sync-check.md)
(`python Uprava/tools/cologne_fork_sync_check.py`).

### XSS

Use the existing escape playbook
([`/cologne-php-xss-sweep`](https://github.com/gasyoun/claude-config/blob/main/commands/cologne-php-xss-sweep.md)):
context-correct `htmlspecialchars(ENT_QUOTES)` / `json_encode(JSON_HEX_*)`.
Do **not** add new `innerHTML` assignments. Display bug (wrong rendering of
correct text) → this repo. Wrong text → csl-orig via the correction queue.

## Do not

- Commit or push [csl-orig](https://github.com/sanskrit-lexicon/csl-orig).
- Edit `basicadjust.php` / `basicdisplay.php` here and leave csl-apidev
  stale (or the reverse).
- Treat `v00/` or the 2018 `.org` readmes as the current pipeline.

## Primer

[SANSKRIT_CONTEXT_PRIMER.md](https://github.com/gasyoun/github-spine/blob/main/SANSKRIT_CONTEXT_PRIMER.md).

Issues use the **Cologne tooling-repo taxonomy** (not the dictionary
runbook) — see
[`/cologne-tooling-runbook`](https://github.com/gasyoun/claude-config/blob/main/commands/cologne-tooling-runbook.md)
and the [observatory tooling runbook](https://github.com/sanskrit-lexicon/csl-observatory/blob/main/runbook/cologne-tooling-runbook.md).

_Dr. Mārcis Gasūns_
