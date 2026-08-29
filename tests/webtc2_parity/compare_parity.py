#!/usr/bin/env python3
"""compare_parity.py — H3633 (G3): compare two run_parity.php NDJSON runs.

Usage: compare_parity.py <before.ndjson> <after.ndjson> <label-before> <label-after>

Emits per-record DIFF lines (empty output = identical), plus a summary:
record counts, result-set diffs, and p50/p95 page-1 timings per side.
Exit 0 iff every record's keys/matchwords/lastLnum/status match exactly.
"""
import json
import sys


def load(path):
    recs = {}
    order = []
    with open(path, encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            r = json.loads(line)
            key = (r["id"], r["page"])
            recs[key] = r
            order.append(key)
    return recs, order


def pct(values, p):
    values = sorted(values)
    if not values:
        return float("nan")
    k = max(0, min(len(values) - 1, int(round((p / 100.0) * (len(values) - 1)))))
    return values[k]


def main():
    before, border = load(sys.argv[1])
    after, aorder = load(sys.argv[2])
    lbefore, lafter = sys.argv[3], sys.argv[4]
    diffs = 0
    for key in border:
        if key not in after:
            print(f"DIFF missing in {lafter}: {key}")
            diffs += 1
            continue
        b, a = before[key], after[key]
        for field in ("result", "matchwords", "lastLnum", "status", "n"):
            if b[field] != a[field]:
                print(f"DIFF {key} field={field}")
                print(f"  {lbefore}: {json.dumps(b[field])[:300]}")
                print(f"  {lafter}: {json.dumps(a[field])[:300]}")
                diffs += 1
    for key in aorder:
        if key not in before:
            print(f"DIFF missing in {lbefore}: {key}")
            diffs += 1
    tbefore = [before[k]["ms"] for k in border if k[1] == 1]
    tafter = [after[k]["ms"] for k in border if k[1] == 1]
    print(f"records: {len(border)} (before) / {len(aorder)} (after)")
    print(f"result diffs: {diffs}")
    print(
        f"p50 ms page-1: {pct(tbefore, 50):.1f} ({lbefore}) -> "
        f"{pct(tafter, 50):.1f} ({lafter})"
    )
    print(
        f"p95 ms page-1: {pct(tbefore, 95):.1f} ({lbefore}) -> "
        f"{pct(tafter, 95):.1f} ({lafter})"
    )
    sys.exit(1 if diffs else 0)


if __name__ == "__main__":
    main()
