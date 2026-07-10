## apidev_readme.

Several php modules are 'shared' between csl-websanlexicon/v02/ and
csl-apidev. The script apidev_copy.sh actively copies three of them:
basicadjust.php, basicdisplay.php, and getword_data.php.
A fourth module, dal.php, is also nominally shared but rarely changes,
so its copy line is left commented out in the script.

These modules are in directory csl-websanlexicon/v02/makotemplates/web/webtc/.

When one of these modules is changed in csl-websanlexicon,
then the changed version should be copied to csl-apidev.

For convenience, they may be copied by executing the script in
a local installation.
```
sh apidev_copy.sh
```
