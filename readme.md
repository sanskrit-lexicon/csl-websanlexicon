# csl-websanlexicon

_Created: 15-05-2026 · Last updated: 11-07-2026_

The **web-frontend** of the [Cologne Digital Sanskrit Dictionaries](https://www.sanskrit-lexicon.uni-koeln.de/) (CDSL). This repository is **not** a dictionary itself — it is the code that turns each Cologne dictionary into a browsable, searchable web application.

## What this repo produces

For any dictionary code (`mw`, `ap90`, `pwg`, `gra`, …) the build renders a self-contained web app — search box, headword display, hierarchy list, PDF page links — driven by a per-dictionary **SQLite** database (`<dict>.sqlite`). The same code serves every dictionary; differences are expressed through templates and per-dict "distinct" files.

## Architecture

It is a **Python + [Mako](https://www.makotemplates.org/) generator**, not a runtime framework — there is no Composer/Laravel/npm project here.

```
v02/makotemplates/web/   ← source templates (the actual frontend)
        webtc/           ← getword.php, dal.php, basicdisplay.php, parm.php, …
v02/distinctfiles/<dict>/← per-dictionary overrides
v02/inventory.txt        ← per-file action: C=copy, CD=copy-distinct, T=Mako-template, D=delete
v02/generate.py          ← the generator
        │
        ▼  sh generate_web.sh <dict> <parent-dir>
<output>/web/...          ← a ready-to-deploy per-dict web app (PHP + SQLite)
```

- Files marked **`T`** in [v02/inventory.txt](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/inventory.txt) are rendered through Mako (they contain `${…}` / `<% %>`); everything else is copied verbatim (categories `C`/`CD`) or removed (`D`). This is why a raw `php -l` over the template tree must skip the Mako files (see CI below).
- Several PHP modules in [v02/makotemplates/web/webtc/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v02/makotemplates/web/webtc) are **hand-synced forks shared with [`csl-apidev`](https://github.com/sanskrit-lexicon/csl-apidev)**. The [v02/apidev_copy.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_copy.sh) script copies [basicadjust.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicadjust.php), [basicdisplay.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/basicdisplay.php), and [getword_data.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/getword_data.php) into `csl-apidev`. [dal.php](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/makotemplates/web/webtc/dal.php) is also nominally shared but rarely changes, so its copy line is left commented out in the script. Any edit to a synced file here must be propagated to `csl-apidev` — see [v02/apidev_readme.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/apidev_readme.md) and, on the apidev side, [`csl-apidev`'s README](https://github.com/sanskrit-lexicon/csl-apidev/blob/main/README.md).
- [v00/](https://github.com/sanskrit-lexicon/csl-websanlexicon/tree/main/v00) is the legacy 2020 generation and is kept for reference only.

## Building / regenerating

```sh
cd v02
# one dictionary into a scratch dir
sh generate_web.sh ap90 tempparent/ap90
# under the hood: python3 generate.py ap90 inventory.txt makotemplates distinctfiles/ap90 tempparent/ap90
```

Requirements: Python 3 with `mako` (`pip install mako`). Batch regeneration of all dictionaries is driven by [v02/redo_xampp_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_xampp_all.sh) / [v02/redo_cologne_all.sh](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/v02/redo_cologne_all.sh). On the Cologne server the refresh runs server-side via cron.

## Running locally

Full step-by-step install guides (longer, authoritative):

- [readme_xampp.org](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/readme_xampp.org) — local install under XAMPP (Apache + PHP).
- [readme_cologne.org](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/readme_cologne.org) — deployment on the Cologne server.

## Continuous integration

| Workflow | Purpose |
|---|---|
| [ci.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/ci.yml) | `php -l` on verbatim templates · Mako compile-check of all templates · `ruff` + parse-check of the generator · YAML lint |
| [codeql.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/codeql.yml) | CodeQL SAST (Python; CodeQL has no PHP analyzer) |
| [semgrep.yml](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/.github/workflows/semgrep.yml) | Semgrep SAST for the PHP frontend — a blocking `semgrep ci` gate (diff-aware on PRs) plus an advisory full-scan upload |

## Contributing & issues

- Corrections to dictionary **content** are made in [csl-orig](https://github.com/sanskrit-lexicon/csl-orig), not here — this repo is the display layer.
- Issues follow the **Cologne tooling-repo taxonomy** (9 type labels · 4 severity levels · 5 milestones); see [CLAUDE.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/CLAUDE.md) and the [tooling runbook](https://github.com/sanskrit-lexicon/csl-observatory/blob/main/runbook/cologne-tooling-runbook.md). Cross-repo work is tracked on the [Tooling Roadmap](https://github.com/orgs/sanskrit-lexicon/projects/9) project.
- See [CONTRIBUTING.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/CODE_OF_CONDUCT.md).

## License & citation

See [LICENSE](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/LICENSE) and [CITATION.cff](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/CITATION.cff). Part of the Cologne Digital Sanskrit Dictionaries project.

_Dr. Mārcis Gasūns_
