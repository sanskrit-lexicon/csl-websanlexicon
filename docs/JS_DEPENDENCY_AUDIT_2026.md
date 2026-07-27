# csl-websanlexicon — Vendored JS Dependency Audit (2026)

_Created: 27-07-2026 · Last updated: 27-07-2026_

Wave 4 deliverable from [docs/ROADMAP_2026_2027.md](https://github.com/sanskrit-lexicon/csl-websanlexicon/blob/main/docs/ROADMAP_2026_2027.md)
("JS dependency audit"). None of these files are covered by Dependabot — there is
no `package.json`/manifest for vendored front-end JS — so this is a manual,
`retire.js`-style pass: inventory every vendored/CDN-loaded JS file, cross-reference
known CVEs, and record a pin/refresh recommendation per file.

## Method

- Versions read directly from file headers/comments on disk.
- CVE cross-reference via public advisory search (NVD/GHSA-backed sources) on
  27-07-2026 — see Sources at the bottom of each row's notes.
- "Exploitable here?" judged against this repo's actual call sites, not just the
  library version in isolation.

## Inventory

| File | Location | Version found | Status |
|---|---|---|---|
| `jquery.min.js` | `v02/makotemplates/web/js/`, `v00/makotemplates/js/` | **3.7.1** | Current, already bumped |
| `js.cookie.min.js` (js-cookie, replaces `jquery.cookie.js`) | `v02/makotemplates/web/js/`, `v00/makotemplates/js/` | was **3.0.5** → bumped to **3.0.8** (this pass) | Patched |
| `orphus.customized.js` | `v02/makotemplates/web/js/` | in-house fork, no upstream version | No CVE surface (bespoke code) |
| jQuery (CDN) | `mobile1/index.php` — `code.jquery.com/jquery-1.8.2.min.js` | **1.8.2** (2012) | Vulnerable, unpatched — see below |
| jQuery Mobile (CDN) | `mobile1/index.php` — `code.jquery.com/mobile/1.2.0/...` | **1.2.0** (2012) | Abandoned upstream, unpatched — see below |

## Per-file findings

### `jquery.min.js` — 3.7.1 — no action needed

Bumped from the previously-vendored 3.5.0 to 3.7.1 in commit `778c5b9`
("fix(security): bump local jQuery 3.5.0 to 3.7.1", H1523, 23-07-2026), prior to
this audit. 3.7.1 is the current stable 3.x release (jQuery 4.0 is still beta as of
this audit) and has no open CVEs against it. This also carries forward the fixes
already present since 3.5.0 for the `htmlPrefilter` XSS pair
(CVE-2020-11022 / CVE-2020-11023). No further action.

### `js.cookie.min.js` — 3.0.5 → 3.0.8 — **patched in this pass**

The vendored copy (introduced by the `jquery.cookie` → `js-cookie` migration,
commit `c5c620b`, H1523, 23-07-2026) was pinned at **js-cookie 3.0.5**, which is
affected by **CVE-2026-46625** (CVSS 7.5, High) — a prototype-pollution bug in the
library's internal `assign()` helper: when an attribute object passed to
`Cookies.set()` originates from `JSON.parse()`, a crafted `"__proto__"` key is
enumerated as an own property and overwrites `Object.prototype`, letting an
attacker inject/override cookie attributes (e.g. force `domain`/`secure` off).
Fixed upstream in **3.0.7** (adds an explicit `"__proto__"` filter in `assign()`);
**3.0.8** is the current release (a same-day-window ES5-compatibility regression
fix on top of 3.0.7, no further security content).

**Exploitability in this codebase:** low as actually used. All three call sites
(`webtc/main_webtc.js`, `webtc2/main.js`, `mobile1/main_mobile.js`) call
`Cookies.set(cookieName, cookieValue, cookieOptions)` with `cookieOptions` a
**hardcoded object literal** (`{expires: 365, path: '/'}`), never a
`JSON.parse()`-derived object — so the specific exploit precondition upstream
describes isn't reachable through current call sites. Bumped anyway because it's a
same-major, no-breaking-API patch release and the fix is free; treat this as
defence-in-depth rather than a live exploit path.

**Action taken:** vendored `js.cookie.min.js` replaced with the official
`js-cookie@3.0.8` minified build (`dist/js.cookie.min.js` from the
[js-cookie](https://github.com/js-cookie/js-cookie) `v3.0.8` release, npm
provenance-attested) in both `v02/makotemplates/web/js/` and
`v00/makotemplates/js/` (kept in lockstep with the jQuery bump's precedent of
patching both template trees).

### `orphus.customized.js` — bespoke, no CVE surface

Not a tracked upstream package — it's an in-house customization of the orphus.ru
text-correction widget (last touched in commit `6fd8528`, "orphus user correction
submission - #54", 12-01-2026) with no version string and no entry in any public
vulnerability database. Prior H1523 work already hardened its server-side contract
(`fix(webtc2): json_encode key/dict for orphus JS`). No further action; if it is
ever replaced with an upstream package, re-run this audit against that package's
CVE history.

### jQuery 1.8.2 (CDN, `mobile1/`) — vulnerable, unpatched by design

`mobile1/index.php` loads `//code.jquery.com/jquery-1.8.2.min.js` directly from
the CDN — a separate, older jQuery than the vendored 3.7.1 used by `webtc`/`webtc2`.
jQuery 1.8.2 is affected by multiple CVEs that are still live because this file is
never upgraded in place:

- **CVE-2020-11022 / CVE-2020-11023** — XSS via `.html()`/`.append()`/etc. on
  untrusted HTML containing `<option>` or crafted `style` attributes (fixed 3.5.0).
- **CVE-2019-11358** — prototype pollution via `$.extend(true, ...)` deep merge
  (fixed 3.4.0).
- **CVE-2015-9251** — XSS via cross-domain Ajax responses with no explicit
  `dataType` (fixed 3.0.0).
- **CVE-2012-6708** — XSS via `jQuery(location.hash)`-style selector parsing of
  attacker-controlled `location.hash` (fixed pre-1.9.0b1); directly relevant here
  since jQuery Mobile's routing depends on `location.hash`.

**Recommendation:** do not attempt an in-place bump — 1.8.2 is pinned to match
jQuery Mobile 1.2.0's supported range (jQuery Mobile 1.2.0 was tested against
jQuery 1.8.x only; jumping the core library without jumping the Mobile framework
risks breaking `mobile1/` outright, which is out of this audit's "safe,
non-breaking" mandate). This is an accepted, tracked risk pending a `mobile1/`
framework-level decision (see below) — not a patch/minor version bump this pass
can safely apply.

### jQuery Mobile 1.2.0 (CDN, `mobile1/`) — abandoned upstream, unpatched by design

jQuery Mobile has had no release since 1.4.5 (October 2014) and the project has
been effectively discontinued/archived since; no maintained fork ships current
patches. 1.2.0 (used here) has a known cross-site-scripting issue via improper
escaping of `location.href`/`location.hash`-driven page routing (the same class of
bug as the jQuery-core CVE-2012-6708 above — jQuery Mobile's `changePage` routing
reads directly from the URL fragment).

**Recommendation:** same as jQuery 1.8.2 above — no safe in-place patch exists
(the project is dead upstream). The only real fixes are (a) migrate `mobile1/` off
jQuery Mobile entirely, or (b) retire `mobile1/` if it's low-traffic/legacy, either
of which is a design decision and framework-level rewrite, not a dependency pin —
out of scope for this mechanical audit. Recorded here as the concrete input Wave 4
flagged as pending Wave 3 CSP telemetry (which endpoint traffic actually still
hits `mobile1/`).

## Summary / recommendations going forward

| File | Recommendation |
|---|---|
| `jquery.min.js` | Keep current (3.7.1); no manifest to auto-track — re-check manually each `/js-dep-cve-audit` style pass. |
| `js.cookie.min.js` | Bumped to 3.0.8 this pass; low residual risk given hardcoded call sites. |
| `orphus.customized.js` | No upstream to track; re-audit only if replaced with a real package. |
| CDN jQuery 1.8.2 / jQuery Mobile 1.2.0 (`mobile1/`) | **Known-vulnerable, accepted risk** — needs a human decision on migrate-vs-retire `mobile1/`; not safely patchable in place. Track against Wave 3 CSP telemetry + Wave 4 in the roadmap. |

Since none of these files have a manifest, there is nothing for Dependabot to
watch going forward; the roadmap's own recurring-audit cadence (or a future
`retire.js`/`npm-audit`-style CI job, if a manifest is ever introduced) is the only
mechanism that will catch the next round of drift.

_Dr. Mārcis Gasūns_
