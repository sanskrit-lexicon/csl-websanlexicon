<?php
// security_headers.php
//
// Shared defensive HTTP headers for every generated dictionary app.
// require_once this file, before ANY other output, at the top of every
// HTML/JSON-emitting entry point. See docs/ROADMAP_2026_2027.md, Wave 3
// (D4 ruling: "headers in templates, phased CSP").
//
// Because this lives in the template tree, it travels with every
// generate.py regeneration and survives Cologne server-config drift --
// the apps do not depend on Apache/nginx config to get these headers.
//
// Wave 3 stage 1+2 (this file, Q4 2026): baseline hardening headers +
// Content-Security-Policy-Report-Only (measurement only -- nothing here
// blocks a request or breaks a page).
//
// Wave 3 stage 3 (NOT implemented here, deferred): tighten the CSP using
// real Report-Only telemetry from a live deployment, hash/nonce the
// legitimate inline scripts, then flip to an enforcing
// Content-Security-Policy. That requires production traffic this template
// change cannot generate in one sitting -- see ROADMAP Wave 3 point 3.
if (!headers_sent()) {
 header('X-Content-Type-Options: nosniff');
 header('Referrer-Policy: strict-origin-when-cross-origin');
 header('X-Frame-Options: SAMEORIGIN');

 // Report-Only CSP -- deliberately does NOT include 'unsafe-inline' on
 // script-src/style-src. The apps are full of inline <script> blocks,
 // inline onclick= handlers, and inline style= attributes, so a policy
 // that included 'unsafe-inline' would report nothing useful (that
 // directive silently allows all inline content, inline or injected
 // alike). Leaving it out means the browser reports every one of those
 // as a violation -- WITHOUT blocking anything, because this is
 // Report-Only -- which is exactly the telemetry Wave 3 stage 3 needs to
 // plan the hash/nonce migration. Known-legitimate external origins
 // (the jQuery Mobile CDN used by mobile1/, the Cologne scans host used
 // by servepdf.php's PDF/image embed) are allowlisted so only genuinely
 // unexpected external-origin loads show up as noise.
 $csp = "default-src 'self'; "
      . "script-src 'self' https://code.jquery.com; "
      . "style-src 'self' https://code.jquery.com; "
      . "img-src 'self' https://www.sanskrit-lexicon.uni-koeln.de data:; "
      . "font-src 'self'; "
      . "object-src 'self' https://www.sanskrit-lexicon.uni-koeln.de; "
      . "connect-src 'self'; "
      . "base-uri 'self'; "
      . "form-action 'self'; "
      . "frame-ancestors 'self'";
 header("Content-Security-Policy-Report-Only: $csp");
}
