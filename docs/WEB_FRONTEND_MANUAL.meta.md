# WEB_FRONTEND_MANUAL.md — metadoc

_Created: 28-07-2026 · Last updated: 28-07-2026_

Companion record for [docs/WEB_FRONTEND_MANUAL.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/WEB_FRONTEND_MANUAL.md).

## Purpose

Close the worst code-to-docs gap measured in the 28-07-2026 org docs-debt scan (~1,249 code files vs ~11 markdown files, ratio ~113): give csl-websanlexicon — the shared CDSL web-frontend engine — a single operator manual from which a newcomer can regenerate, install, and troubleshoot a dictionary web app without reverse-engineering the Mako/PHP tree or the Jim-style fragment readmes (`readme_xampp.org`, `readme_cologne.org`, scattered `readme.txt` files).

## Audience

1. A new operator (human or agent session) regenerating or deploying a dictionary web app.
2. Maintainers triaging display bugs (§8 symptom table, §11 triage rules).
3. Future doc work: the July 2026 operator-manual wave (H501–H531 dictionaries + H1782–H1786 engines).

## Provenance

- Handoff: [H1782](https://github.com/gasyoun/Uprava/blob/main/handoffs/H1782-Fable_csl-websanlexicon_web-frontend-operator-manual_28.07.26.md) (minted 28-07-2026 by Grok 4.5 `grok-4.5` from the docs-debt ranking session).
- Authored 28-07-2026 by Fable 5 (`claude-fable-5`), modelled on the RussianRamayana Litpam-Indexator [MANUAL.md](https://github.com/gasyoun/RussianRamayana/blob/main/Litpam-Indexator/docs/indesign-pipeline/MANUAL.md) and the H501–H531 manual family.
- Ground truth: the §4 walkthrough was executed live on 28-07-2026 (Windows 10, Git Bash, Python 3, Mako 1.3.12; `gra` generated to a scratch dir — 104 files, Mako substitution spot-checked in `dictcode.php` and `index.php`). Runtime facts (`dal.php` path resolution, `query_dump.txt` consumer, JSONP whitelist) read from the v02 sources at commit `7e2b942`.

## Ranked improvement backlog

1. **Link the sibling engine manuals** when H1783 (csl-pywork) and H1784 (csl-apidev) ship — §2 table and §11 both point at them by name.
2. **Runtime-verify the XAMPP walkthrough** (§4.3) on a machine with Apache+PHP actually running — the generation half is verified; the serving half is reproduced from the generated `readme.txt` and in-repo notes, not re-executed.
3. **Document webtc2 query internals** (`querymodel.php` matching, the `mb_strtolower` case trap noted in `readme_cologne.org`) — currently only surfaced as a data-file dependency.
4. **Per-dictionary quirk appendix**: the `.org` readmes carry rich per-dict conversion notes (PWG `pwgbib`, SKD chakra images, STC French pages); §5 shows the mechanism but only samples the instances.
5. **Screenshot(s) of the landing page / four surfaces** if the repo ever adopts binary doc assets.

## Known limitations

- Cologne-server deploy specifics (cron cadence, server paths, who runs the refresh) are described only to the depth the in-repo notes support; server-side operational detail lives with the Cologne maintainers.
- The v00 legacy tree is deliberately covered only as a trap (§11.1, §11.6), not documented.
- Batch-list counts (43/45) are as of commit `7e2b942`; they drift when dictionaries are added.

## Related documents

- README.md — repo overview, links here.
- [docs/ROADMAP_2026_2027.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/ROADMAP_2026_2027.md) — security roadmap (drives the work queue).
- [docs/JS_DEPENDENCY_AUDIT_2026.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/JS_DEPENDENCY_AUDIT_2026.md) — vendored JS dependency audit.
- Org-level: [csl-corrections correction workflow](https://github.com/sanskrit-lexicon/csl-corrections/blob/main/docs/correction-workflow.md), [Cologne tooling runbook](https://github.com/sanskrit-lexicon/csl-observatory/blob/main/runbook/cologne-tooling-runbook.md).

## Revision history

| Date | Author | Change |
|---|---|---|
| 28-07-2026 | Fable 5 (`claude-fable-5`), H1782 | Initial version: 11 sections, verified walkthrough, fork-sync contract, symptom table, maintainer appendix |

_Dr. Mārcis Gasūns_
