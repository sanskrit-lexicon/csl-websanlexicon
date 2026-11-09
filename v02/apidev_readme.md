_Created: 31-10-2019 · Last updated: 05-09-2026_

## apidev_readme.

Several php modules are 'shared' between csl-websanlexicon/v02/ and
csl-apidev. The script apidev_copy.sh actively copies three of them:
basicadjust.php, basicdisplay.php, and getword_data.php.
A fourth module, dal.php, is also nominally shared but rarely changes,
so its copy line is left commented out in the script.

These modules are in directory csl-websanlexicon/v02/makotemplates/web/webtc/.

A fifth shared file (H7901, 09-11-2026) lives one level up, at
csl-websanlexicon/v02/makotemplates/web/og_meta.php, and is copied by the
same script to the csl-apidev root: the Open Graph include. It must stay
byte-identical — csl-apidev's twin-drift.yml CI guards that pair mechanically,
so a one-sided edit fails CI instead of drifting silently.

When one of these modules is changed in csl-websanlexicon,
then the changed version should be copied to csl-apidev.

For convenience, they may be copied by executing the script in
a local installation.
```
sh apidev_copy.sh
```

_Dr. Mārcis Gasūns_
