# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.1] - 2026-07-03

### Changed
- Semgrep is now a blocking gate: a `semgrep-ci-gate` job runs `semgrep ci`, which is diff-aware on pull requests and fails only on findings introduced by the PR. The existing SARIF-upload `semgrep` job stays advisory. Grandfathers the clean `main` baseline (v0.2.0) with no separate baseline file (Wave 2, D3 in `docs/ROADMAP_2026_2027.md`, PR #77).

## [0.2.0] - 2026-07-03

### Added
- `docs/ROADMAP_2026_2027.md` — security & vulnerability-avoidance roadmap (Waves 1–5) with the four MG interview rulings (D1–D4).

### Security
- Fixed HIGH reflected XSS in `webtc/indexcaller_fr.php` (`?word=` echoed raw into an HTML attribute; this interface applied no char-strip). Now `htmlspecialchars(ENT_QUOTES)` (PR #76).
- Fixed HIGH path traversal + reflected XSS via `?dictionary=` in `webtc2` — `queryparm.php` now `basename()`s and allowlists the dump filename before `fopen()`; `query.php` escapes the reflected error message (PR #76).
- Fixed MEDIUM ReDoS / PCRE injection in `webtc2/querymodel.php` — `?word=` is `preg_quote()`d before entering any regex (PR #76).
- Fixed MEDIUM reflected XSS in a JS string in `webtc/indexcaller.php` — `var key`/`dict` now `json_encode(JSON_HEX_*)` (PR #76).
- Hardened `value="$init"` attribute in `webtc/indexcaller.php` and `mobile1/index.php` (defense in depth) (PR #76).
- Enabled GitHub secret scanning, push protection, and Dependabot vulnerability alerts + security updates.

### Changed
- Excluded the dead `v00/` legacy tree from Semgrep (`--exclude v00`) and CodeQL (`paths-ignore`) — kept in-repo for reference, out of scanning (PR #76).

## [0.1.0] - 2026-06-30

### Added
- Initial release of csl-websanlexicon
- PHP + SQLite frontend for Cologne Digital Sanskrit Lexicon dictionaries
- Multi-dictionary support with standardized display interface
- Word search and headword lookup functionality
- XML dictionary source parsing and database generation

### Changed
- Security hardening: XSS vulnerabilities fixed via htmlspecialchars output encoding (PR #69)
- SQL injection mitigation: prepared statements and parameter binding throughout
- Branch naming: `master` → `main` for default branch (per GitHub 2020+ policy)

### Deprecated

### Removed

### Fixed
- XSS and SQL injection vulnerabilities across PHP endpoints
- Stale master branch references in CI triggers (GitHub Actions)
- Abot display code compatibility across PHP versions (PR #67)
- Abot abbreviation correction (PWG issue #195)

### Security
- Eliminated reflected XSS via `htmlspecialchars()` on all user-controlled output
- Implemented parameterized SQL queries to prevent SQL injection
- Updated CodeQL configuration for PHP code analysis

[0.2.0]: https://github.com/sanskrit-lexicon/csl-websanlexicon/releases/tag/v0.2.0
[0.1.0]: https://github.com/sanskrit-lexicon/csl-websanlexicon/releases/tag/v0.1.0
