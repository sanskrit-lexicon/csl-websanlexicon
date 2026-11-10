# cp makotemplates/web/webtc/dal.php  ../../csl-apidev/ # normally no change to make
echo "copying basicadjust.php, basicdisplay.php and getword_data.php csl-websanlexicon to csl-apidev"
cp /c/xampp/htdocs/cologne/csl-websanlexicon/v02/makotemplates/web/webtc/basicadjust.php /c/xampp/htdocs/cologne/csl-apidev/
cp /c/xampp/htdocs/cologne/csl-websanlexicon/v02/makotemplates/web/webtc/basicdisplay.php /c/xampp/htdocs/cologne/csl-apidev/

cp /c/xampp/htdocs/cologne/csl-websanlexicon/v02/makotemplates/web/webtc/getword_data.php /c/xampp/htdocs/cologne/csl-apidev/

# H7948: the data-driven link-target table consumed by basicadjust.php
# (required via __DIR__ — must ship beside basicadjust.php on BOTH sides)
echo "copying link_targets.php csl-websanlexicon to csl-apidev"
cp /c/xampp/htdocs/cologne/csl-websanlexicon/v02/makotemplates/web/webtc/link_targets.php /c/xampp/htdocs/cologne/csl-apidev/

# H7901: the Open Graph include (canonical at makotemplates/web/, NOT webtc/)
echo "copying og_meta.php csl-websanlexicon to csl-apidev"
cp /c/xampp/htdocs/cologne/csl-websanlexicon/v02/makotemplates/web/og_meta.php /c/xampp/htdocs/cologne/csl-apidev/

