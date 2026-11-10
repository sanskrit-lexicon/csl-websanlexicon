# golden_link_targets — H7948/H7962 golden gate

Byte-identical old-vs-new proof for the `basicadjust.php` abbreviation→href
link-target chain (`BasicAdjust::ls_callback_pwg_href`): the repeated
per-abbreviation `preg_match`/href blocks now live in ONE data-driven table
(`v02/makotemplates/web/webtc/link_targets.php`) iterated by a single loop.
The ONLY allowed old-vs-new divergences are the declared roman rows of the
H7962 ṚV. PRĀT. roman-rule fix (see below).

Run: `sh tests/golden_link_targets/run_golden.sh` (needs `php` + `git`).

| Check | What it proves |
|---|---|
| 1. fixture provenance | `basicadjust_pre_H7948.php` is a content-exact copy (modulo line endings — Windows autocrlf checkouts materialize CRLF; `git show` emits the LF blob) of `v02/makotemplates/web/webtc/basicadjust.php` at base commit `d090a1076bcd019e595e1cb9f70571f047d6ce04` |
| 2+3. named + fuzz corpora | every preserved abbreviation case (132 rows) and the deterministic cartesian product (83 abbreviations × 16 citation shapes × 3 dicts × 2 keys = 7 968 cases) returns the same href old-vs-new **except** the 38 declared roman rows (`intentional_divergences.tsv`); `check_divergence.php` enforces set-equality of changed-vs-declared, the `(null)`→href value pins, and a BIJECTION between declared ids and corpus rows matching the fixed rule's match surface `^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+)` |
| 4. expected values | `roman_expected.tsv` (14 roman inputs, I–L coverage): OLD side returns `(null)` for every row, NEW side returns the pinned `rvps/app2/?<ipage>` href (`run_expected.php`) |

`run_golden.php old|new [corpus]` runs one side: `old` loads the fixture,
`new` loads the refactored file, each through
`ReflectionClass::newInstanceWithoutConstructor` (the href chain touches only
the public `$dict`/`$key` properties — no Dal, no database; `stubs/` satisfies
the `require_once('dal.php')` because the real `dal.php` pulls the Mako
template `dictinfo.php`, which is not loadable raw PHP).

## Kept-in-code cases (H7948 Stop-budget)

Non-URL logic that does not fit the table stays in code as `href_*` handler
rows (same match order as before): `href_spr` (dict-conditional edition),
`href_spr1` (pwg-only gate), `href_rajatar` (taranga 7/8 → Calcutta scans),
`href_rvprat_roman` (roman numeral → app2), `href_med` (letter-class +
bare-`MED.` headword lookup), `href_chr` (pw-only gate), `href_dhatup`
(section-0 guard), `href_bhagp` (skandha-dependent edition), `href_vedic`
(computed hymn-file hrefs + rschl→rgorr switch).

## The ṚV. PRĀT. roman rule: fixed in H7962 (behavior change)

The historic pattern `"|^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+)|"` was `|`-delimited
while its `(TIŚ|T)` alternation also used `|`, so PHP read `"T)"` as modifiers
(`Unknown modifier 'T'`) and the rule had **never** matched: roman citations
like `ṚV. PRĀT. IV` rendered without links although the code intends
`rvps/app2/?<ipage>` (ipage = `romanToInt(roman)`). H7948 preserved the pattern
verbatim for byte parity (corpus row `rvprat_roman` pinned `(null)`); H7962
fixed it by switching **only** the delimiter to `~`
(`"~^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+)~"` — pattern body and `(TIŚ|T)` capture
semantics unchanged).

This CHANGES rendered URLs — links appear where none did. The complete,
machine-enforced change surface is `intentional_divergences.tsv` (38 rows:
2 named corpus + 36 fuzz; every row `(null)` → `rvps/app2/?<ipage>`); it is
the review artifact. Values are pinned in `roman_expected.tsv` and re-derived
by `check_divergence.php`'s independent oracle.

**URL-change review (H7962):** `romanToInt` values for numerals I–L (1..50) are
verified by `roman_expected.tsv` check 4; the `rvps/app2/?<ipage>` page
convention is verified against the live rvps scan site (app2 pages are the
roman-numbered scan pages — `?4` shows page IV). Blast radius over real
citation data (csl-orig `v02/{pwg,pw,pwkvn}/*.txt`): exactly ONE roman-shaped
citation exists — PWG `<ls>ṚV. PRĀT. XIII, N. 2</ls>` (newly links to
`app2/?13`); the remaining 1 039 PWG + 91 PW + 11 PWKVN `ṚV. PRĀ(TIŚ|T).`
citations are numeric-shaped and go through the unchanged `app1` numeric rule.

The `PHP Warning: Unknown modifier 'T'` lines printed while check 4 runs the
OLD side are expected: the fixture demonstrably hits the preserved broken
pattern there.

_Dr. Mārcis Gasūns_
