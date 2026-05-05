# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo does

This repository generates and installs the PHP/HTML/JS/CSS web displays for the Sanskrit lexicon dictionaries hosted at the Cologne Digital Sanskrit Dictionaries project (http://www.sanskrit-lexicon.uni-koeln.de/). It does **not** contain the dictionary data itself (SQLite files, XML source) — those live in sibling `<DICT>Scan/<YEAR>/` directories.

There are two active revisions of the web display:
- **`v00/`** — original revision, Python 2 origin, updated to Python 3
- **`v02/`** — current revision with additional features (WorldCat links, bibliography entries, more dictionaries)

## Key commands

### Generate web files for a single dictionary (v02, recommended)
```bash
cd v02
sh generate_web.sh <dictcode> <output-parent-dir>
# Example: sh generate_web.sh acc ../../ACCScan/2020/
```

### Regenerate all dictionaries in-place on the Cologne server (v02)
```bash
cd v02
sh redo_cologne_all.sh
```

### Generate for v00 (lower-level, direct Python call)
```bash
cd v00
python generate.py <dictcode> inventory.txt makotemplates <output-dir>
# Example: python generate.py gra inventory.txt makotemplates ../../GRAScan/2014/web
```

### Check inventory coverage
```bash
cd v00/check_inventory
python webinventory.py webinventory.txt
```

### Sync shared PHP modules to csl-apidev (v02)
```bash
cd v02
sh apidev_copy.sh
```

## Architecture

### Generation pipeline

`generate.py` is the core script. It:
1. Reads `dictparms.py` to get parameters for a given dictionary code (upper/lower name, version, accent support, Devanagari option, bibliography metadata)
2. Reads `inventory.txt` to get the list of files to produce, each tagged with a category:
   - `T` — render as a **Mako template**, substituting `dictparms` variables
   - `C` — plain **copy** from `makotemplates/`
   - `CD` — copy from `distinctfiles/<dictcode>/` (v02 only, for per-dictionary overrides)
   - `D` — **delete** the file from the target if it exists
3. Creates the output directory structure and writes all files

### Template source locations
- `v00/grav00/` / `v02/makotemplates/` — shared Mako templates and static files for all dictionaries
- `v02/distinctfiles/<dictcode>/` — per-dictionary overrides (primarily `webtc/pdffiles.txt` and occasional images)

### Inventory files
- `v00/inventory.txt` — master file list for v00 (lines are `filename:category`)
- `v02/inventory.txt` — master file list for v02 (lines are `dicts:filename_template:category`, where `dicts` is `*` or a space-separated list of dict codes)
- `inventories/<dict>_<ver>.txt` — per-dictionary backup snapshots of what was actually deployed

### Web display subdirectories (inside the generated `web/` folder)
| Subdir | Purpose |
|--------|---------|
| `webtc/` | Main word-lookup display (single-word query) |
| `webtc1/` | Hierarchical list/browse display |
| `webtc2/` | Advanced search (full-text, multi-word) |
| `mobile1/` | Mobile-optimised display |
| `sqlite/` | SQLite schema + PHP to build `.sqlite` from XML |
| `utilities/transcoder/` | Transliteration XML maps (SLP1 ↔ Devanagari, HK, ITRANS, etc.) |

### Key PHP modules shared with csl-apidev
`webtc/dal.php`, `webtc/basicadjust.php`, and `webtc/basicdisplay.php` (in `v02/makotemplates/`) are used by both this repo and the `csl-apidev` project. When you edit any of these, run `sh apidev_copy.sh` to propagate the changes.

### Dictionary parameters (`dictparms.py`)
Each dictionary entry defines:
- `dictup` / `dictlo` — UPPER and lower case code (e.g. `"MW"` / `"mw"`)
- `dictversion` — display version string (v02 uses `"02"`, suffixed with `microversion`)
- `dictaccent` — whether accent marks are displayed
- `webtc2devatextoption` — whether Devanagari input is offered in advanced search
- `dictwc`, `dictbe`, `dicttitle` (v02 only) — WorldCat URL, bibliographic entry, display title

### After generating files
After installing generated files into a live web directory, initialise the query cache:
```bash
cd <webdir>/webtc2
sh init_query.sh
```

## Notes on `install.py`
`v00/install.py` is a **Python 2** script (uses `print` statements without parentheses) used for the original install-and-swap workflow on the Cologne server. It is **not used** in the v02 workflow. Do not update it to Python 3 without verifying it is still needed.

## Dependency
Templates use the **Mako** templating library (`pip install mako`). The `v00/` directory historically included a local copy of Mako 1.0.7 to work around an old Python 2.6 installation on the Cologne server; this is no longer needed with Python 3.
