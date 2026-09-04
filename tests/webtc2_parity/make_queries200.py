#!/usr/bin/env python3
"""make_queries200.py — H3485: build a deterministic 200-query parity sample.

Extends the H3633 31-query matrix (queries.json) to the 200-query sample the
H3485 acceptance bar requires, derived deterministically from a dictionary
tree's query_dump.txt (no RNG: fixed strides over the dump lines / words).

Usage:
  python3 make_queries200.py <dictdir>/web/webtc2 <output-queries200.json>
"""
from __future__ import print_function

import json
import os
import re
import sys

KEY_RE = re.compile(r'^([^:\t]+) ::')
WORD_RE = re.compile(r"[a-zA-Z]{3,9}")

# The H3633 matrix verbatim: it seeds the sample (ids prefixed h3633-).
SEED_MATRIX = [
    {"sword": "ama", "sregexp": "exact", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "karma", "sregexp": "exact", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "rAma", "sregexp": "exact", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "deva", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "saM", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "kR", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "Ana", "sregexp": "suffix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "tA", "sregexp": "suffix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "karma", "sregexp": "instring", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "atva", "sregexp": "substring", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "tva", "sregexp": "substring", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "zzqqx", "sregexp": "exact", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "rAma", "sregexp": "exact", "swordhw": "both", "transLit": "slp1"},
    {"sword": "rAma", "sregexp": "prefix", "swordhw": "textonly", "transLit": "slp1"},
    {"sword": "a", "sregexp": "exact", "swordhw": "both", "transLit": "slp1"},
    {"sword": "kr*", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "*", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1"},
    {"sword": "kRSNa", "sregexp": "exact", "swordhw": "hwonly", "transLit": "hk"},
    {"word": "fire", "regexp": "exact"},
    {"word": "water", "regexp": "exact"},
    {"word": "ind", "regexp": "prefix"},
    {"word": "di", "regexp": "prefix"},
    {"word": "ing", "regexp": "suffix"},
    {"word": "roun", "regexp": "instring"},
    {"word": "ov", "regexp": "substring"},
    {"word": "diamond", "regexp": "exact"},
    {"word": "FIRE", "regexp": "exact"},
    {"word": "zzqqx", "regexp": "exact"},
    {"word": "the", "regexp": "substring"},
    {"sword": "deva", "sregexp": "prefix", "swordhw": "hwonly", "transLit": "slp1", "max": 5},
    {"word": "fire", "regexp": "exact", "max": 5},
]


def slug(q):
    parts = []
    for f in ("sword", "word", "sregexp", "regexp", "swordhw", "transLit", "max"):
        if f in q and q[f] != "slp1":
            parts.append(str(q[f]))
    return "-".join(p for p in parts if p)


def main():
    webtc2 = sys.argv[1].rstrip("/")
    outpath = sys.argv[2]
    dump = os.path.join(webtc2, "query_dump.txt")

    keys = []
    words = []
    with open(dump, encoding="utf-8") as f:
        for line in f:
            m = KEY_RE.match(line)
            if m:
                k = m.group(1).strip()
                if k and k.isalnum():
                    keys.append(k)
            # definition text = everything after the first tab
            tab = line.find("\t")
            if tab >= 0:
                words.extend(WORD_RE.findall(line[tab:].lower()))

    queries = []
    for i, q in enumerate(SEED_MATRIX):
        rec = dict(q)
        rec["id"] = "h3633-%s" % slug(q)
        queries.append(rec)

    # fixed strides over the corpus (deterministic, spread across the dump)
    key_pool = [keys[i] for i in range(0, len(keys), max(1, len(keys) // 80))][:80]
    word_pool = [w for w in words[i] for i in []]  # placeholder, replaced below
    word_pool = []
    step = max(1, len(words) // 3000)
    seen = set()
    for i in range(0, len(words), step):
        w = words[i]
        if w not in seen:
            seen.add(w)
            word_pool.append(w)
        if len(word_pool) >= 120:
            break

    # 1) Sanskrit exact over the sampled keys (80)
    for k in key_pool:
        queries.append({"id": "gen200-sk-exact-%s" % k, "sword": k,
                        "sregexp": "exact", "swordhw": "hwonly", "transLit": "slp1"})

    # 2) Sanskrit mode x hw rotation over the sampled keys (21)
    modes = ["prefix", "suffix", "instring", "substring"]
    hws = ["hwonly", "both"]
    for j in range(21):
        k = key_pool[j * 4 % len(key_pool)]
        queries.append({"id": "gen200-sk-%s-%s" % (modes[j % 4], k),
                        "sword": k, "sregexp": modes[j % 4],
                        "swordhw": hws[j % 2], "transLit": "slp1"})

    # 3) Sanskrit wildcard shapes (12)
    wilds = [("kr*", "prefix"), ("deva*", "prefix"), ("*tva", "substring"),
             ("k*a", "substring"), ("saM*", "prefix"), ("*ya", "substring"),
             ("dev?", "prefix"), ("ka*", "prefix"), ("*ana", "substring"),
             ("ama|eva", "substring"), ("a*a", "substring"), ("?", "prefix")]
    for w, mode in wilds:
        queries.append({"id": "gen200-sk-wild-%s-%s" % (mode, w.replace("*", "star").replace("?", "qm").replace("|", "or")),
                        "sword": w, "sregexp": mode, "swordhw": "hwonly",
                        "transLit": "slp1"})

    # 4) Non-Sanskrit English: exact over 24 sampled words (24)
    for w in word_pool[:24]:
        queries.append({"id": "gen200-ns-exact-%s" % w, "word": w, "regexp": "exact"})

    # 5) Non-Sanskrit modes over a 10-word slice (20)
    nmodes = ["prefix", "suffix", "instring", "substring"]
    for j in range(20):
        w = word_pool[8 + j * 5 % (len(word_pool) - 8)]
        queries.append({"id": "gen200-ns-%s-%s" % (nmodes[j % 4], w),
                        "word": w, "regexp": nmodes[j % 4]})

    # 6) English case + hyphen + zero-match + max variants (12)
    extra = [
        {"id": "gen200-ns-upper-1", "word": word_pool[0].upper(), "regexp": "exact"},
        {"id": "gen200-ns-upper-2", "word": word_pool[1].upper(), "regexp": "prefix"},
        {"id": "gen200-ns-zero-1", "word": "qqxxzz", "regexp": "exact"},
        {"id": "gen200-ns-zero-2", "word": "xyzzy", "regexp": "substring"},
        {"id": "gen200-sk-zero-1", "sword": "zzzqqqxxx", "sregexp": "exact",
         "swordhw": "hwonly", "transLit": "slp1"},
        {"id": "gen200-sk-zero-2", "sword": "wwww", "sregexp": "prefix",
         "swordhw": "both", "transLit": "slp1"},
        {"id": "gen200-ns-max5", "word": word_pool[2], "regexp": "exact", "max": 5},
        {"id": "gen200-ns-max100", "word": word_pool[3], "regexp": "substring"},
        {"id": "gen200-sk-max5-1", "sword": key_pool[1], "sregexp": "prefix",
         "swordhw": "hwonly", "transLit": "slp1", "max": 5},
        {"id": "gen200-sk-max5-2", "sword": key_pool[2], "sregexp": "substring",
         "swordhw": "both", "transLit": "slp1", "max": 5},
        {"id": "gen200-sk-hk-1", "sword": "kRSNa", "sregexp": "prefix",
         "swordhw": "hwonly", "transLit": "hk"},
        {"id": "gen200-sk-hk-2", "sword": "rAma", "sregexp": "exact",
         "swordhw": "both", "transLit": "hk"},
    ]
    queries.extend(extra)

    # trim/pad to exactly 200
    queries = queries[:200]
    # keep ids unique even when corpus sampling collides
    seen_ids = {}
    for q in queries:
        base = q["id"]
        n = seen_ids.get(base, 0)
        seen_ids[base] = n + 1
        if n:
            q["id"] = "%s-dup%d" % (base, n)
    with open(outpath, "w", encoding="utf-8") as f:
        json.dump(queries, f, ensure_ascii=False, indent=1)
    print("%d queries written to %s" % (len(queries), outpath))


if __name__ == "__main__":
    main()
