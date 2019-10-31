## apidev_readme.

Three php modules are 'shared' between csl-websanlexicon/v02/ and
csl-apidev:
dal.php,  basicadjust.php, and basicdisplay.php.

These modules are in directory csl-websanlexicon/v02/makotemplates/web/webtc/.

When one of these modules is changed in csl-websanlexicon,
then the changed version should be copied to csl-apidev.

For convenience, all three may be copied by executing the script in 
a local installation.
```
sh apidev_copy.sh
```
