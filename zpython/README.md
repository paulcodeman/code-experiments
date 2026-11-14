# ZeroPython archive

Historical experiments translating Python code and AST data toward C--/KolibriOS.
This directory preserves the original project rather than a supported Python compiler.

## Files

- `function-annotations-example.py` — small annotated function used as AST input (formerly `aaa.py`).
- `export-ast-json.py` — converts that input through the external `ast2json` module (formerly `asd.py`).
- `zp.php`, `zpython.php`, `module/` — PHP experiments and translation backends.
- `src/ast.py`, `include/` — supporting Python code and KolibriOS headers.
- `example/simple/` — historical HTML/Python example.
- `src/cmm`, `test.kex` — archived binaries; they were not executed during documentation work.
- `dom.py` — historical empty placeholder, retained without inventing an implementation.

## AST export example

From this directory, with Python and the third-party `ast2json` module already installed:

```sh
python export-ast-json.py
```

The script reads `function-annotations-example.py` relative to the current working
directory. The renamed files do not change this convention. Translation, binary
builds and compatibility with modern Python are unverified. The source snapshot
remains available through `source/zpython/main`; see the root migration report.
