# csl-websanlexicon — Web-Frontend Operator Manual

_Created: 28-07-2026 · Last updated: 28-07-2026_

The operator manual for [csl-websanlexicon](https://github.com/sanskrit-lexicon/csl-websanlexicon) — the **shared CDSL web frontend**: a Python + [Mako](https://www.makotemplates.org/) generator that renders every [Cologne Digital Sanskrit Dictionaries](https://www.sanskrit-lexicon.uni-koeln.de/) dictionary into a browsable, searchable **PHP + SQLite** web application. Written so that a new operator can regenerate, install, and troubleshoot a dictionary web app end-to-end without reverse-engineering the template tree. Every command in the walkthrough was executed and verified on 28-07-2026 (Windows 10, Git Bash, Python 3, Mako 1.3.12).

What this repo is **not**: it is not a runtime framework (no Composer, no Laravel, no npm build), and it is not a dictionary — dictionary *content* corrections belong in [csl-orig](https://github.com/sanskrit-lexicon/csl-orig), and the dictionary *databases* are built by [csl-pywork](https://github.com/sanskrit-lexicon/csl-pywork). This repo owns only the display layer.

## 1. Cheat-sheet — the whole flow on one screen

```sh
# 0. one-time: Python 3 with Mako
pip install mako

# 1. generate the web app for ONE dictionary (from the v02 directory)
cd v02
sh generate_web.sh gra tempparent/gra
#    = python3 generate.py gra inventory.txt makotemplates distinctfiles/gra tempparent/gra
#    output: tempparent/gra/web/...   (~104 files; PHP, JS, CSS, fonts, help pages)

# 2. the generator ships NO data — copy these into the output web/ tree:
#    web/sqlite/gra.sqlite            (from csl-pywork build, or the Cologne graweb1.zip)
#    web/webtc2/query_dump.txt        (advanced-search data)
#    web/graheader.xml                (from the dictionary's pywork/)

# 3. serve it: put the web/ folder under a PHP server, e.g. XAMPP
#    C:\xampp\htdocs\cologne\gra\web  ->  http://localhost/cologne/gra/web/

# 4. batch regeneration over all dictionaries
sh redo_xampp_all.sh      # local XAMPP layout   ../../<dict>/web
sh redo_cologne_all.sh    # Cologne server layout ../../<DICT>Scan/<YEAR>/web

# 5. if you touched basicadjust.php / basicdisplay.php / getword_data.php:
sh apidev_copy.sh         # propagate the hand-synced forks to csl-apidev
```

Everything current lives under [v02/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02). The [v00/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v00) tree is the legacy 2020 generation, kept for reference only.

## 2. Data-flow — where this engine sits

```
csl-orig            digitized dictionary text (content corrections happen THERE)
   │
   ▼
csl-pywork          builds per-dict artifacts: <dict>.sqlite, query_dump.txt,
   │                <dict>header.xml, pdffiles data
   │
   ▼                                     csl-websanlexicon (THIS REPO)
per-dict data files ──────────────┐      v02/makotemplates/web/  ← source templates
                                  │      v02/distinctfiles/<dict>/ ← per-dict overrides
                                  │      v02/inventory.txt  ← per-file action C/CD/T/D
                                  │      v02/dictparms.py   ← per-dict template variables
                                  │      v02/generate.py    ← the generator
                                  │               │
                                  │               ▼  sh generate_web.sh <dict> <parent>
                                  └────►  <parent>/web/     ← deployable PHP+SQLite app
                                                  │
                     ┌────────────────────────────┤
                     ▼                            ▼
      local XAMPP: htdocs/cologne/<dict>/web   Cologne server: <DICT>Scan/<YEAR>/web
                                                  (refresh runs server-side via cron)
```

Sibling-repo dependencies:

| Repo | Relationship |
|---|---|
| [csl-orig](https://github.com/sanskrit-lexicon/csl-orig) | Canonical dictionary text. Display bugs are fixed here in csl-websanlexicon; text bugs go to csl-orig via the [correction workflow](https://github.com/sanskrit-lexicon/csl-corrections/blob/main/docs/correction-workflow.md) — never edited directly. |
| [csl-pywork](https://github.com/sanskrit-lexicon/csl-pywork) | Builds the per-dictionary data the frontend reads: `<dict>.sqlite` (+ auxiliary `<dict>ab.sqlite`, `<dict>bib.sqlite`, …), `webtc2/query_dump.txt`, `<dict>header.xml`. |
| [csl-apidev](https://github.com/sanskrit-lexicon/csl-apidev) | REST/API layer. Shares **hand-synced forked copies** of four PHP modules — see §7, the fork-sync contract. |
| [sanskrit-lexicon-scans](https://github.com/sanskrit-lexicon-scans) | Per-dictionary page-scan repos; [servepdf.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/servepdf.php) serves a local clone or falls back to the Cologne scan server. |

## 3. Environment and prerequisites

**Generation time** (running the generator):

- Python 3 with the `mako` package (`pip install mako`; verified with Mako 1.3.12). No other Python dependencies.
- A POSIX shell for the `.sh` wrappers — on Windows use **Git Bash** (the scripts are one-liners; you can always call [generate.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/generate.py) directly from PowerShell instead).
- **Windows trap:** [generate_web.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/generate_web.sh) invokes `python3`. Git Bash on Windows often has only `python` on PATH. Cure: run the underlying command directly — `python generate.py <dict> inventory.txt makotemplates distinctfiles/<dict> <outdir>` — which is exactly what the wrapper does.

**Runtime** (serving the generated app):

- Apache + PHP with the SQLite3 PDO extension. Tested by the project on PHP 7.1.9 and 8.0.0; XAMPP is the recommended local stack (only its Apache + PHP components are used — MySQL, FileZilla, Mercury, Tomcat are not).
- No database server: each dictionary reads its own `web/sqlite/<dict>.sqlite` file. The filename-to-table convention is fixed: `X.sqlite` contains a table named `X`.
- Linux/macOS: after installing under the web root, `chmod -R 755 cologne` may be needed.

## 4. Step-by-step walkthrough (verified 28-07-2026)

### 4.1 Generate one dictionary

```sh
cd v02
sh generate_web.sh gra tempparent/gra
```

Silence is success — the generator prints nothing when it succeeds. Expected result: `tempparent/gra/web/` with ~104 files in eleven subdirectories (`fonts`, `images`, `js`, `mobile1`, `sqlite`, `utilities`, `webtc`, `webtc1`, `webtc2`, plus root [index.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/index.php), `readme.txt`, [security_headers.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/security_headers.php)).

Spot-check that Mako substitution ran: the rendered `web/webtc/dictcode.php` must contain your dictionary code and version, e.g.

```php
$dictcode = "gra";
$dictwebversion = "02.004";   // dictversion "02" + microversion ".004" from dictparms.py
```

and the rendered `web/index.php` must show the dictionary's display name (for `gra`: "Grassmann Wörterbuch zum Rig Veda").

The second argument is the **parent** directory — the generator always writes into `<parent>/web/…`, because every path in [v02/inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/inventory.txt) carries the `web/` prefix. Any dictionary code known to [v02/dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) works (`mw`, `pwg`, `ap90`, … — 45+ codes); an unknown code fails with a Python `KeyError` on `alldictparms`.

### 4.2 Add the data files (the generator ships none)

The freshly generated tree is code-complete but **data-empty**: `web/sqlite/` contains only a placeholder `empty.txt`, and `web/webtc2/` has no `query_dump.txt`. Copy in, from the dictionary's csl-pywork build or from an existing deployment:

| File | Read by | Purpose |
|---|---|---|
| `web/sqlite/<dict>.sqlite` | [dal.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dal.php) | Main entry database (table named `<dict>`) |
| `web/sqlite/<dict>ab.sqlite`, `<dict>bib.sqlite`, … | [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php) | Auxiliary: abbreviation tooltips, bibliography links (only some dictionaries) |
| `web/webtc2/query_dump.txt` | [queryparm.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc2/queryparm.php) | Advanced-search full-text data |
| `web/<dict>header.xml` | display code | Dictionary header metadata, comes from the dictionary's `pywork/` |
| `web/webtc/pdffiles.txt` | (generated, category `CD`) | Page-image index — already placed by the generator from [v02/distinctfiles/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/distinctfiles) |

End users skip this step entirely: each deployed dictionary offers a `<dict>web1.zip` download (linked from its `webtc/download.html`) that bundles code + data, and the generated `web/readme.txt` is the corresponding self-contained install guide.

A dev convenience wired into [dictinfo.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dictinfo.php): data paths resolve via `webparent/web/…`, so a scratch copy of the code installed as a sibling `web1/` directory automatically reads the data files of the real `web/` next to it.

### 4.3 Serve locally under XAMPP

Layout: `HOME/cologne/<dict>/web`, where `HOME` is the server web root (Windows XAMPP: `C:\xampp\htdocs`). Start Apache in the XAMPP control panel and open:

```
http://localhost/cologne/gra/web/
```

You should see the "Available displays" landing page. The full end-user version of this procedure — including installing local page scans from [sanskrit-lexicon-scans](https://github.com/sanskrit-lexicon-scans) so [servepdf.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/servepdf.php) serves images locally — is the generated `web/readme.txt` itself.

### 4.4 Batch regeneration

- [v02/redo_xampp_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_xampp_all.sh) — regenerates 43 dictionaries **in place** into the local XAMPP layout (`../../<dict>`, i.e. the repo clone is expected to sit inside `htdocs/cologne/`).
- [v02/redo_cologne_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_cologne_all.sh) — 45 dictionaries into the Cologne server layout (`../../<DICT>Scan/<YEAR>/`; the year varies per scan: most 2020, `lrv` 2022, `abch`/`acph`/`acsj` 2023, `fri` 2025, `nmmb` 2026). On the Cologne server this refresh runs server-side via cron.

"Regeneration in place" is an **upgrade** operation: category-`D` inventory rows delete files that older deployments still contain (retired fonts, superseded PHP modules), so re-running the generator both refreshes and cleans a live tree. Data files are untouched — the inventory never lists them, and the generator only writes what the inventory names.

### 4.5 Propagate fork-synced files (only if you edited them)

If your change touched [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php), [basicdisplay.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php), or [getword_data.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/getword_data.php) — run the sync (§7).

## 5. How inventory.txt drives generation

[v02/inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/inventory.txt) is the single manifest: one colon-separated row per output file, `dicts:path:category`. Lines starting with `;` are comments.

- **dicts** — `*` (all dictionaries) or a space-separated list of lower-case codes (`pwg pw`). This is how per-dictionary rows work, e.g. `stc:web/index_fr.php:CD` ships the French landing page only for STC, and `skd:web/images/…cakram.png:CD` ships SKD's diagram images.
- **path** — a `string.Template` with one allowed variable, `${dictlo}` (e.g. `*:web/${dictlo}header.xml:CD` would expand per dictionary — currently commented out because headers come from csl-pywork).
- **category** — the action:

| Category | Action | Source |
|---|---|---|
| `C` | copy verbatim | [v02/makotemplates/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/makotemplates) |
| `CD` | copy a per-dictionary **distinct** file | [v02/distinctfiles/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/distinctfiles)`<dict>/` |
| `T` | render through **Mako** with the dictionary's parameters | `v02/makotemplates/` |
| `D` | **delete** from the output tree if present | — (upgrade cleanup) |

Only `T` files contain Mako syntax (`${…}`, `<% %>`); this is why raw `php -l` over the template tree must skip them (CI does — see §9). The template variables come from [v02/dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py): per dictionary `dictup`/`dictlo` (code in both cases), `dictname`, `dicttitle`, `dictversion`, `dictyear` (scan year, used to build Cologne URLs), `dictaccent`, `webtc2devatextoption`, `dictwc` (WorldCat URL), `dictbe` (bibliographic entry); plus the generator-injected `dictmmddyyyy` (build date) and the file-global `microversion` suffix.

**Microversion discipline:** the rendered `dictwebversion` (in `webtc/dictcode.php`) is `dictversion + microversion`. Bump `microversion` in [dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) when templates change, so deployed apps carry a distinguishable version.

## 6. The runtime, surface by surface

The generated app is plain PHP + SQLite behind Apache; the landing [index.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/index.php) links four display surfaces:

| Surface | Entry point | What it is |
|---|---|---|
| Basic display | [webtc/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/makotemplates/web/webtc) via [indexcaller.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/indexcaller.php) | Headword search + entry display. [getword.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/getword.php) is also the JSONP endpoint (callback whitelist-validated). |
| List/hierarchy | [webtc1/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/makotemplates/web/webtc1) ([index.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc1/index.php), [listhier.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc1/listhier.php), [disphier.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc1/disphier.php)) | Alphabetical/hierarchical browsing with an on-screen transliteration keyboard and help subsite. |
| Advanced search | [webtc2/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/makotemplates/web/webtc2) ([query.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc2/query.php), [querymodel.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc2/querymodel.php)) | Full-text search over `query_dump.txt` (not the SQLite DB). |
| Mobile | [mobile1/index.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/mobile1/index.php) | Reduced mobile interface. |

The request path for an entry display: `getword.php` → [getwordClass.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/getwordClass.php) → [dal.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dal.php) (opens `web/sqlite/<dict>.sqlite`, table `<dict>`, via paths from [dictinfo.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dictinfo.php)) → [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php) (XML adjustment: literary-source links, `div` normalization, abbreviation wrapping) → [basicdisplay.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php) (XML → HTML rendering).

Shared infrastructure: [utilities/transcoder.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/utilities/transcoder.php) with XML mapping tables (SLP1 ↔ Devanagari/IAST/HK/ITRANS/WX; some dictionary-specific variants like `slp1_deva2.xml` for MW accents); [security_headers.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/security_headers.php), `require_once`'d at every HTML/JSON-emitting entry point (nosniff, referrer policy, frame options, Report-Only CSP); a root `web/.htaccess` with static-asset cache headers.

## 7. Fork-sync contract with csl-apidev

Four PHP modules exist as **hand-synced forked copies** in [csl-apidev](https://github.com/sanskrit-lexicon/csl-apidev):

- Actively synced by [v02/apidev_copy.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_copy.sh): [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php), [basicdisplay.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php), [getword_data.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/getword_data.php).
- Nominally shared, copy line commented out (rarely changes): [dal.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dal.php).

The contract (see [v02/apidev_readme.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_readme.md)): **any edit to a synced file here must be propagated to csl-apidev in the same work cycle**, and vice-versa an apidev-side fix must come back here. The Cologne update flow is: sync locally → push both repos → pull both on the Cologne server. Beware: [apidev_copy.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_copy.sh) hardcodes `/c/xampp/htdocs/cologne/` paths — on any other layout, copy the three files manually or run the org-level drift check, the [/cologne-fork-sync-check](https://github.com/gasyoun/claude-config/blob/main/commands/cologne-fork-sync-check.md) skill, which diffs both sides against the last-known-synced state.

## 8. Symptom → cause → cure

| Symptom | Cause | Cure |
|---|---|---|
| `generate_web.sh: python3: command not found` | Windows Git Bash has `python`, not `python3` | Run `python generate.py <dict> inventory.txt makotemplates distinctfiles/<dict> <outdir>` directly |
| `KeyError: '<dict>'` from generate.py | Dictionary code missing from [dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) | Add the dictionary's parameter block (and its `CD` rows + [distinctfiles/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/distinctfiles) entries if any) |
| `FileNotFoundError` on a `distinctfiles/<dict>/…` path | An inventory `CD` row applies to this dictionary but the distinct file is absent | Add the file under `v02/distinctfiles/<dict>/web/…` or scope the inventory row to the right dictionaries |
| Generated app shows "Available displays" but every search fails | Data files missing — the generator ships none | Copy `web/sqlite/<dict>.sqlite` (+ auxiliaries) and `web/webtc2/query_dump.txt` in (§4.2) |
| Advanced search (webtc2) empty while basic display works | `query_dump.txt` missing or stale | Reinstall it from the csl-pywork build; it is a separate artifact from the SQLite DB |
| `php -l` reports syntax errors in a template | The file is inventory category `T` — Mako syntax is not PHP | Expected; lint only the rendered output or let CI's inventory-aware lint handle it |
| Page-scan links 404 locally | No local scans installed | Optional: clone the dictionary's [sanskrit-lexicon-scans](https://github.com/sanskrit-lexicon-scans) repo under `cologne/scans/` (see generated `web/readme.txt`), or let [servepdf.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/servepdf.php) fall back to the Cologne server |
| Fix applied here but the API still serves the old behaviour | Forked copy in csl-apidev not synced | §7 — run [apidev_copy.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_copy.sh) or copy manually, push + pull both repos |
| Old font files / modules linger in a deployed tree | Deployment predates the `D` rows that retire them | Re-run the generator over the deployed tree — regeneration in place applies the deletions (§4.4) |
| Browser shows stale JS/CSS after an upgrade | Static-asset cache headers (root `.htaccess`) | Hard-reload; for real releases bump `microversion` in [dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) |

## 9. CI and change safety

| Gate | What it enforces |
|---|---|
| [ci.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/ci.yml) | `php -l` on verbatim (non-`T`) templates · Mako compile-check of all templates · `ruff` + parse-check of the generator · YAML lint |
| [codeql.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/codeql.yml) | CodeQL SAST (Python only — CodeQL has no PHP analyzer) |
| [semgrep.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/semgrep.yml) | Semgrep SAST for the PHP frontend — blocking `semgrep ci` gate, diff-aware on PRs |

`main` is protected: PR required, all five status checks required, stale approvals dismissed. The security work queue lives in [docs/ROADMAP_2026_2027.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/ROADMAP_2026_2027.md); session state in [.ai_state.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.ai_state.md). Issues use the Cologne tooling-repo taxonomy — see [CLAUDE.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/CLAUDE.md).

## 10. Glossary

| Term | Meaning |
|---|---|
| dictionary code | Lower-case CDSL identifier (`mw`, `pwg`, `ap90`, `gra`, …); upper-case variant names the scan repo/dir (`MWScan`) |
| inventory | [v02/inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/inventory.txt) — the manifest of every generated file with its action (§5) |
| category `C`/`CD`/`T`/`D` | copy / copy-distinct / Mako-template / delete (§5) |
| distinct file | A per-dictionary override shipped from `v02/distinctfiles/<dict>/` instead of the shared template tree |
| dictparms | Per-dictionary template variables in [v02/dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) |
| microversion | Global version suffix in dictparms.py, appended to every dictionary's `dictversion` |
| webtc / webtc1 / webtc2 / mobile1 | The four display surfaces: basic, list/hierarchy, advanced search, mobile (§6) |
| `<dict>web1.zip` | Per-dictionary downloadable bundle (code + data) linked from each deployment's `webtc/download.html` |
| scan year | The `<DICT>Scan/<YEAR>` directory component on the Cologne server; recorded as `dictyear` in dictparms.py |
| basicadjust / basicdisplay | The XML-adjust and XML-to-HTML stages of entry rendering; fork-synced with csl-apidev (§7) |

## 11. Maintainer appendix

**Known traps and observed doc-vs-code defects (as of 28-07-2026):**

1. **The two `.org` readmes describe v00, not v02.** [readme_xampp.org](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/readme_xampp.org) and [readme_cologne.org](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/readme_cologne.org) (2018, Python-2 era) document the legacy `v00/` generation — inventory format without the dicts field, `install.py`, per-dict `init_query.sh`. Historically valuable, operationally superseded by this manual and [README.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/README.md). Same for [v02/makotemplates/web/readme.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/readme.txt)'s siblings under `v00/`.
2. **The batch lists differ.** [redo_xampp_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_xampp_all.sh) covers 43 dictionaries; [redo_cologne_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_cologne_all.sh) covers 45 — `ap` and `pd` are in the Cologne list only. If you need them locally, add the two lines by analogy.
3. **`apidev_copy.sh` is machine-specific** — hardcoded `/c/xampp/htdocs/cologne/` source and target (§7).
4. **`webtc2` no longer ships its data builder.** `init_query.php` survives only as [unused_init_query.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc2/unused_init_query.php); `query_dump.txt` is produced by the dictionary build (csl-pywork), not here.
5. **`dictyear` is a scan year, not a content year** — it feeds Cologne URL construction in [dictinfo.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dictinfo.php) (`get_cologne_webPath`). Setting it wrong silently breaks remote page-scan links only.
6. **`v00/` deletion is planned** (Wave 4 in [.ai_state.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.ai_state.md), gated on [#72](https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/72) and confirmation it is unserved) — when it lands, ignore any v00 references that remain in the `.org` readmes.
7. **Adding a new dictionary** touches, in order: [dictparms.py](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/dictparms.py) (parameter block), [inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/inventory.txt) (any dict-scoped rows), [distinctfiles/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/distinctfiles)`<dict>/` (at minimum `web/webtc/pdffiles.txt`), both `redo_*_all.sh` lists, and the [inventories/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/inventories) backup list if a pre-existing deployment must be backed up first. Recent example in history: `nmmb` (2026).
8. **Display bug vs content bug triage:** wrong rendering of correct text → this repo (usually [basicdisplay.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php) / [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php)); wrong text → [csl-orig](https://github.com/sanskrit-lexicon/csl-orig) via the [correction workflow](https://github.com/sanskrit-lexicon/csl-corrections/blob/main/docs/correction-workflow.md) (agents never commit to csl-orig directly).

**Related manuals:** the sibling engine manuals for [csl-pywork](https://github.com/sanskrit-lexicon/csl-pywork) (dictionary build) and [csl-apidev](https://github.com/sanskrit-lexicon/csl-apidev) (API backend) are queued as H1783/H1784 in the same July 2026 manual wave; link them here when they ship.

_Dr. Mārcis Gasūns_
