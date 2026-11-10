# golden_link_targets — H7948 golden parity gate

Byte-identical old-vs-new proof for the `basicadjust.php` abbreviation→href
link-target refactor (`BasicAdjust::ls_callback_pwg_href`): the repeated
per-abbreviation `preg_match`/href blocks now live in ONE data-driven table
(`v02/makotemplates/web/webtc/link_targets.php`) iterated by a single loop,
with **zero change to any rendered URL or HTML output**.

Run: `sh tests/golden_link_targets/run_golden.sh` (needs `php` + `git`).

| Check | What it proves |
|---|---|
| 1. fixture provenance | `basicadjust_pre_H7948.php` is a byte-exact copy of `v02/makotemplates/web/webtc/basicadjust.php` at base commit `d090a1076bcd019e595e1cb9f70571f047d6ce04` |
| 2. named corpus (`corpus.tsv`) | every preserved abbreviation case (132 rows: each table row × variant, each kept-in-code handler, dict/key forks, separator/roman/case quirks, precedence and fall-through misses) returns the same href |
| 3. fuzz corpus (`gen_fuzz.php`) | the deterministic cartesian product (85 abbreviations × 16 citation shapes × 3 dicts × 2 keys = 7 968 cases) — any pattern/URL transcription drift surfaces here |

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

## Latent bug preserved on purpose (behavior parity)

The ṚV. PRĀT. roman-numeral rule `"|^ṚV\. PRĀ(TIŚ|T)\. ([IVXLCDM]+)|"` is
`|`-delimited but contains a `|` alternation, so PHP reports
`Unknown modifier 'T'` and the rule has **never** matched (the `rvprat_roman`
corpus row pins `(null)` on both sides). H7948 is structure-only: the pattern
is transcribed verbatim and the bug is left for a separate behavior-change
issue/PR.

_Dr. Mārcis Gasūns_
