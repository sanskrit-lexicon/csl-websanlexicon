# coding=utf-8
"""removedup_smoke.py - H7960 (10-11-2026)
Behavior smoke for v02/imagework/removedup.py after the crash-safe rewrite.

Asserts, offline and stdlib-only:

  1. golden dedup output - `remove` on pdffiles.txt-shaped fixtures writes
     source + dups rows byte-identical to the pre-change dedup rule
     (independent reference implementation below), across the vp/p/x ref
     classes including consecutive-duplicate refs;
  2. stdout of `remove` is line-for-line identical to the pre-change output;
  3. atomic-write path - a failure AFTER the temp file is written but at
     os.replace() leaves the source bytes untouched (and no temp file behind),
     while the dups file has already been written (dups-before-replace
     ordering); a mid-write failure inside atomic_write_lines also preserves
     the source;
  4. the explicit IndexError names the offending input line, and the
     title-less line is still accepted for gra/pw/stc/wil (title='NONE');
  5. the argv CLI - `list | remove <dictlo>` dispatch and the exact usage
     line on stderr with exit 2.

The real ../distinctfiles tree is NEVER touched: every run happens in a
temp directory laid out like the repo's neighbour tree.

Exit 0 = pass. Wired as lane 6 of tests/local_gate.sh and mirrored in
.github/workflows/ci.yml (G88 gate).
"""
import contextlib
import importlib.util
import io
import os
import pathlib
import subprocess
import sys
import tempfile

sys.dont_write_bytecode = True  # keep the repo tree clean

ROOT = pathlib.Path(__file__).resolve().parent.parent
REMOVEDUP = ROOT / "v02" / "imagework" / "removedup.py"

# pdffiles.txt-shaped fixtures: ref:name:title across vp/p/x ref classes,
# with consecutive-duplicate refs.
VP_LINES = [
    "1-3:pb001.pdf:Page 1",
    "1-3:pb001b.pdf:Page 1 repeat",
    "2-5:pb002.pdf:Page 2",
    "2-5:pb002b.pdf:Page 2 repeat a",
    "2-5:pb002c.pdf:Page 2 repeat b",
    "3-1:pb003.pdf:Page 3",
]
PW_LINES = [
    "12:pg12a.pdf",
    "12:pg12b.pdf",
    "13:pg13.pdf",
    "intro:cover.pdf",
]
NODUP_LINES = [
    "4-2:pb004.pdf:Page 4",
    "5-7:pb005.pdf:Page 5",
]


def fail(msg):
    print("SMOKE FAIL:", msg)
    sys.exit(1)


def load_removedup():
    spec = importlib.util.spec_from_file_location("removedup", REMOVEDUP)
    if spec is None or spec.loader is None:
        print("SMOKE FAIL: cannot load removedup module spec from %s"
              % REMOVEDUP)
        raise SystemExit(1)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def reference_dedup(lines):
    """The PRE-change dedup rule, reimplemented independently: keep a record
    unless its ref equals the previous kept record's ref."""
    kept, dups, prev = [], [], None
    for raw in lines:
        line = raw.rstrip("\r\n")
        ref = line.split(":")[0]
        if prev == ref:
            dups.append(line)
        else:
            kept.append(line)
            prev = ref
    return kept, dups


class sandbox:
    """Temp dir laid out like the repo's neighbour ../distinctfiles tree."""

    def __init__(self, fixtures):
        self.td = tempfile.TemporaryDirectory()
        base = pathlib.Path(self.td.name)
        self.work = base / "work"
        self.work.mkdir()
        for dictlo, lines in fixtures.items():
            d = base / "distinctfiles" / dictlo / "web" / "webtc"
            d.mkdir(parents=True)
            (d / "pdffiles.txt").write_text(
                "\n".join(lines) + "\n", encoding="utf-8")
        self._old_cwd = os.getcwd()
        os.chdir(str(self.work))

    def source(self, dictlo):
        return (pathlib.Path(self.td.name) / "distinctfiles" / dictlo
                / "web" / "webtc" / "pdffiles.txt")

    def __enter__(self):
        return self

    def __exit__(self, *exc):
        os.chdir(self._old_cwd)
        self.td.cleanup()

    def temp_leftovers(self, dictlo):
        return [p.name for p in self.source(dictlo).parent.iterdir()
                if p.name.startswith(".removedup-")]


def check_golden(mod, sb):
    """remove: byte-identical source/dups rows vs the pre-change rule +
    hardcoded goldens, and stdout line-for-line identical to pre-change."""
    out = io.StringIO()
    with contextlib.redirect_stdout(out):
        mod.remove_dups("zx1")
    kept, dups = reference_dedup(VP_LINES)
    # hardcoded golden rows (independent of both implementations)
    if kept != ["1-3:pb001.pdf:Page 1",
                "2-5:pb002.pdf:Page 2",
                "3-1:pb003.pdf:Page 3"]:
        fail("golden kept-rows fixture broken: %r" % (kept,))
    if dups != ["1-3:pb001b.pdf:Page 1 repeat",
                "2-5:pb002b.pdf:Page 2 repeat a",
                "2-5:pb002c.pdf:Page 2 repeat b"]:
        fail("golden dups-rows fixture broken: %r" % (dups,))
    src = sb.source("zx1").read_text(encoding="utf-8")
    if src != "".join(l + "\n" for l in kept):
        fail("source rows not byte-identical to pre-change logic: %r" % src)
    dups_txt = (sb.work / "zx1_dups.txt").read_text(encoding="utf-8")
    if dups_txt != "".join(l + "\n" for l in dups):
        fail("dups rows not byte-identical to pre-change logic: %r" % dups_txt)
    want = [
        "zx1 6 #dups= 3 3",
        "3 records without dups written to "
        "../distinctfiles/zx1/web/webtc/pdffiles.txt",
        "3 dups written to zx1_dups.txt",
    ]
    got = out.getvalue().splitlines()
    if got != want:
        fail("remove stdout changed:\n  got %r\n  want %r" % (got, want))
    if sb.temp_leftovers("zx1"):
        fail("temp file left behind after successful atomic write: %r"
             % sb.temp_leftovers("zx1"))
    print("ok golden: source+dups byte-identical, stdout unchanged")


def check_p_class(mod, sb):
    """p/x-class dict incl. a title-less pw line; golden vs reference rule."""
    with contextlib.redirect_stdout(io.StringIO()):
        mod.remove_dups("pw")
    kept, dups = reference_dedup(PW_LINES)
    src = sb.source("pw").read_text(encoding="utf-8")
    if src != "".join(l + "\n" for l in kept):
        fail("p-class source rows changed: %r" % src)
    if (sb.work / "pw_dups.txt").read_text(encoding="utf-8") != \
            "".join(l + "\n" for l in dups):
        fail("p-class dups rows changed")
    print("ok p/x-class fixture (title-less pw lines)")


def check_no_dups(mod, sb):
    out = io.StringIO()
    with contextlib.redirect_stdout(out):
        mod.remove_dups("zx3")
    if out.getvalue().strip() != "zx3 no duplicates":
        fail("no-dups stdout changed: %r" % out.getvalue())
    if sb.source("zx3").read_text(encoding="utf-8") != \
            "".join(l + "\n" for l in NODUP_LINES):
        fail("no-dups run must not touch the source")
    print("ok no-dups path leaves source untouched")


def check_atomic(mod, sb):
    """Simulated failure at os.replace(): source preserved, dups file already
    written (dups-before-replace), no temp file behind."""
    src = sb.source("zx4")
    before = src.read_bytes()
    real_replace = mod.os.replace

    def boom(*a, **k):
        raise OSError("simulated crash at os.replace")

    mod.os.replace = boom
    try:
        with contextlib.redirect_stdout(io.StringIO()):
            try:
                mod.remove_dups("zx4")
                fail("simulated os.replace failure did not propagate")
            except OSError as e:
                if "simulated crash" not in str(e):
                    fail("unexpected error: %s" % e)
    finally:
        mod.os.replace = real_replace
    if src.read_bytes() != before:
        fail("source rows LOST on simulated write failure")
    dups_txt = (sb.work / "zx4_dups.txt").read_text(encoding="utf-8")
    want_dups = "".join(l + "\n" for l in reference_dedup(VP_LINES)[1])
    if dups_txt != want_dups:
        fail("dups file not written BEFORE the source replace: %r" % dups_txt)
    if sb.temp_leftovers("zx4"):
        fail("temp file left behind after failed atomic write: %r"
             % sb.temp_leftovers("zx4"))
    print("ok atomic-write failure: source preserved, dups written first")


def check_atomic_midwrite(mod, sb):
    """Failure INSIDE atomic_write_lines (mid-iteration): source preserved."""
    src = sb.source("zx4")
    before = src.read_bytes()

    def bad_lines():
        yield "1-3:pb001.pdf:Page 1"
        raise IOError("simulated disk-full mid-write")

    try:
        mod.atomic_write_lines(str(src), bad_lines())
        fail("mid-write failure did not propagate")
    except IOError:
        pass
    if src.read_bytes() != before:
        fail("source rows LOST on mid-write failure")
    if sb.temp_leftovers("zx4"):
        fail("temp file left behind after mid-write failure: %r"
             % sb.temp_leftovers("zx4"))
    print("ok mid-write failure: source preserved, temp cleaned up")


def check_indexerror(mod):
    try:
        mod.Image("1-3:pb001.pdf", "mw")
        fail("title-less line for mw must raise IndexError")
    except IndexError as e:
        if "offending line: 1-3:pb001.pdf" not in str(e):
            fail("IndexError does not name the offending line: %s" % e)
    rec = mod.Image("12:pg12.pdf", "pw")  # allowed: pw/stc/wil/gra
    if rec.title != "NONE":
        fail("title-less pw line must get title='NONE', got %r" % rec.title)
    print("ok explicit IndexError names the offending line")


def check_cli(mod):
    seen = []

    def fake_list():
        seen.append(("list", None))

    def fake_remove(dictlo):
        seen.append(("remove", dictlo))

    mod.listdups = fake_list
    mod.remove_dups = fake_remove
    if mod.main(["removedup.py", "list"]) != 0 or seen[-1] != ("list", None):
        fail("argv dispatch: list mode broken")
    if mod.main(["removedup.py", "remove", "pw"]) != 0 or \
            seen[-1] != ("remove", "pw"):
        fail("argv dispatch: remove mode broken")
    err = io.StringIO()
    with contextlib.redirect_stderr(err):
        if mod.main(["removedup.py"]) != 2:
            fail("no-args must exit 2")
        if mod.main(["removedup.py", "bogus"]) != 2:
            fail("bad argv must exit 2")
    want = "usage: removedup.py list | removedup.py remove <dictlo>"
    if err.getvalue().splitlines().count(want) != 2:
        fail("usage line wrong: %r" % err.getvalue())
    p = subprocess.run([sys.executable, str(REMOVEDUP)],
                       capture_output=True, text=True)
    if p.returncode != 2 or want not in p.stderr:
        fail("script entrypoint usage: rc=%s stderr=%r"
             % (p.returncode, p.stderr))
    print("ok argv CLI: list/remove dispatch + usage line (exit 2)")


def main():
    for rel in (str(REMOVEDUP),):
        if not os.path.isfile(rel):
            fail("missing file: %s" % rel)
    mod = load_removedup()
    with sandbox({"zx1": VP_LINES, "pw": PW_LINES,
                  "zx3": NODUP_LINES, "zx4": VP_LINES}) as sb:
        check_golden(mod, sb)
        check_p_class(mod, sb)
        check_no_dups(mod, sb)
        check_atomic(mod, sb)
        check_atomic_midwrite(mod, sb)
    check_indexerror(mod)
    check_cli(mod)
    print("removedup smoke: all checks passed")
    return 0


if __name__ == "__main__":
    sys.exit(main())
