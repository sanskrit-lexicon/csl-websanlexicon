# coding=utf-8
"""webbackup_py3_smoke.py - H7958 (10-11-2026)
Behavior smoke for the frozen webbackup/ build scripts after their
Python 2 -> 3 port.

These four files are the 2014 per-dict backup copies of the site build
scripts (frozen archive, H084); the live copies live in csl-pywork, whose
P10/P11 defects were fixed there under H3633/H3638 and are deliberately
NOT touched here. This smoke asserts only that the port is faithful:

  1. all four files compile under the running Python 3;
  2. webbackup/pw and webbackup/pwg stay byte-identical twins;
  3. init_query.query_construct emits 'key :: sanskrit<TAB>data' rows;
  4. make_input.make emits 'key1<TAB>lnum<TAB>&#nnnn;' rows - the entity
     TEXT, never a Python 3 bytes repr (the one deliberate preservation
     of the port).

Offline, stdlib-only. Exit 0 = pass. Wired as lane 5 of
tests/local_gate.sh and mirrored in .github/workflows/ci.yml (G88 gate).
"""
import importlib.util
import pathlib
import py_compile
import re
import sys
import tempfile

sys.dont_write_bytecode = True  # keep the repo tree clean

ROOT = pathlib.Path(__file__).resolve().parent.parent
INIT_QUERY = [
    "webbackup/ccs/00/webtc2/init_query.py",
    "webbackup/pw/00/webtc2/init_query.py",
    "webbackup/pwg/00/webtc2/init_query.py",
]
MAKE_INPUT = "webbackup/vcp/00/sqlite/make_input.py"
ALL_FILES = INIT_QUERY + [MAKE_INPUT]


def fail(msg):
    print("SMOKE FAIL:", msg)
    sys.exit(1)


def load(relpath, name):
    spec = importlib.util.spec_from_file_location(name, ROOT / relpath)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def check_compile():
    with tempfile.TemporaryDirectory() as td:
        for f in ALL_FILES:
            cfile = pathlib.Path(td) / (f.replace("/", "_") + "c")
            try:
                py_compile.compile(str(ROOT / f), cfile=str(cfile), doraise=True)
            except py_compile.PyCompileError as e:
                fail("py_compile %s: %s" % (f, e))


def check_twins():
    a = (ROOT / INIT_QUERY[1]).read_bytes()
    b = (ROOT / INIT_QUERY[2]).read_bytes()
    if a != b:
        fail("pw and pwg init_query.py are no longer byte-identical twins")


def check_init_query_rows():
    fixture = ("<H1><key1>k1</key1> <body>gloss one <s>rAma</s> more</body> <L>1</L>\n"
               "<H1><key1>k2</key1> <body>gloss two <s>sIta</s> x</body> <L>2</L>\n")
    for i, f in enumerate(INIT_QUERY):
        mod = load(f, "init_query_%d" % i)
        with tempfile.TemporaryDirectory() as td:
            fin = pathlib.Path(td) / "in.txt"
            fout = pathlib.Path(td) / "out.txt"
            fin.write_text(fixture, encoding="utf-8")
            mod.query_construct(str(fin), str(fout))
            rows = fout.read_text(encoding="utf-8").rstrip("\n").split("\n")
        if len(rows) != 2:
            fail("%s: expected 2 rows, got %r" % (f, rows))
        for row in rows:
            if not re.match(r"^\S+ :: ", row) or "\t" not in row:
                fail("%s: row not 'key :: sanskrit<TAB>data': %r" % (f, row))
        if not rows[0].startswith("k1 :: ") or not rows[1].startswith("k2 :: "):
            fail("%s: key column wrong: %r" % (f, rows))


def check_make_input_entities():
    mod = load(MAKE_INPUT, "make_input")
    with tempfile.TemporaryDirectory() as td:
        fin = pathlib.Path(td) / "in.txt"
        fout = pathlib.Path(td) / "out.txt"
        fin.write_text("<H1><key1>k1</key1> <body>\u00fc-Test</body> <L>12</L>\n",
                       encoding="utf-8")
        mod.make(str(fin), str(fout))
        out = fout.read_text(encoding="utf-8")
    if not out.startswith("k1\t12\t"):
        fail("make_input row shape wrong: %r" % out)
    if "&#252;" not in out:
        fail("make_input lost the &#nnnn; entity: %r" % out)
    if "b'" in out:
        fail("make_input leaked a bytes repr: %r" % out)


def main():
    check_compile()
    check_twins()
    check_init_query_rows()
    check_make_input_entities()
    print("webbackup py3 smoke OK: %d files compile, pw==pwg, init_query rows, "
          "make_input entities" % len(ALL_FILES))


if __name__ == "__main__":
    main()
