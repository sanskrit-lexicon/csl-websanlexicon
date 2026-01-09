01-08-2026 @funderburkjim


cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02/issues/issue53 

https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/53


changes to code:
1.  webtc1/index.php
  comment out " getWord_keyboard(false,false);"
  Reason: The getWord_keyboard function is in main.js
  It is called in main.js by keydown_return function.
  Thus it was being called twice. This call in index.php not needed.
2. listhierview.php
  comment-out the "rev1, sup1" section. See shadow-revsup
  
=======================================================
changes to mw.txt
  
cp /c/xampp/htdocs/cologne/csl-orig/v02/mw/mw.txt temp_mw_0.txt
cp temp_mw_0.txt temp_mw_1.txt

1. Remove '<listinfo n="rev1"/>' (2)
2. Remove '<listinfo n="sup1"/>' (7)
3. add "<info hui="a"/> to DvAnta L=100924.1
4. remove '<listinfo n="sup"/>' from <L>26223<pc>150,3<k1>ArAva<k2>A-rAva<h>1<e>2

** https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/45
  Display of Revision symbol (in webtc1) for anAgAmin
  It currently shows in webtc1
** https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/46
  anarman

p. 26,2
anarma/n, mfn. =an-arva/n, q.v. AV. vii,7,1
p. 1311,1
anarma/n w.r. for an-arvan, AV. vii,7,1
a-narman mfn. 'not (merely) jocular', sarcastic, ironical, MBh.

<L>5238.2<pc>1311,1<k1>anarman<k2>a-narman<e>1  
L=5238.2 = p. 1311,1 

<L>5238.2<pc>1311,1<k1>anarman<k2>a-narman<e>1

Make changes in temp_mw_1.txt

When this editing is done, continue with installation details
---------------------------------------------
  
# remake xml from temp_mw_1.txt and check
cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02/issues/issue53
cp temp_mw_1.txt /c/xampp/htdocs/cologne/csl-orig/v02/mw/mw.txt
cd /c/xampp/htdocs/cologne/csl-pywork/v02
sh generate_dict.sh mw  ../../mw
sh xmlchk_xampp.sh mw
# ok, as expected
# return here
cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02/issues/issue53

----------------------
python diff_to_changes_dict.py temp_mw_0.txt temp_mw_1.txt change_mw_1.txt
9 changes written to change_mw_1.txt

#grep 'pc:' 20251126.mw_todo_misc.Cat-B.AB.response.txt > mw_printchange.txt
================================================
INSTALLATION
sync to github:

------------------
# csl-orig
cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02/issues/issue53
diff temp_mw_1.txt /c/xampp/htdocs/cologne/csl-orig/v02/mw/mw.txt | wc -l
#0  as expected
cd /c/xampp/htdocs/cologne/csl-orig/
git pull
git add .
git commit -m "Remove shadow rev1, sup1
Ref: https://github.com/sanskrit-lexicon/csl-corrections/issues/111"

git push

Note: the commit comment above is wrong;
it should have been to
Ref: https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/53
------------------------

# csl-websanlexicon

cd /c/xampp/htdocs/cologne/csl-websanlexicon
git pull
git add .
git commit -m "Remove shadow rev1, sup1. Remove extra call to getWord_keyboard(false,false)
Ref: https://github.com/sanskrit-lexicon/csl-websanlexicon/issues/53"

git push
cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02/issues/issue53


---------------------------------------------------
# sync to Cologne, pull changed repos, redo display
---------------
csl-orig #pull
csl-websanlexicon #pull

---------------
# update displays for mw
cd csl-pywork/v02
sh generate_dict.sh mw  ../../MWScan/2020/

-----------------------------------------------------
THE END

