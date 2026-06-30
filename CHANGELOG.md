# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[0.1.0]: https://github.com/sanskrit-lexicon/csl-websanlexicon/releases/tag/v0.1.0
