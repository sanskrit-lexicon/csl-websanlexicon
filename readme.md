
### csl-websanlexicon
This repository has code and documentation for installing the web displays
used in the http://www.sanskrit-lexicon.uni-koeln.de/ web displays.

The v00 subdirectory contains the first revision.
It in turn contains a 'makotemplates' directory which contains the
installation code.  The idea is that this directory contains the code
base for the displays in a template form. Given a particular dictionary
code X, the   python program `generate.py` converts
these templates into the php, html, css, and js files tailored to the
characteristics of dictionary X.

Further documentation of this template approach will be developed over time.
  
