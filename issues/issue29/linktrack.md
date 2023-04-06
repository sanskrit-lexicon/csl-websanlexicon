## atharva veda
* Sample URL: https://sanskrit-lexicon.github.io/avlinks/avhymns/av14.002.html#av14.002.71
* gra {AV. X, Y, Z}  {AV. 14,2,71}
* pw, pwkvn, pwg, sch, mw `<ls>AV. 14,2,71</ls>`
* ap90 `<ls>Av. 14,2,71</ls>`
* mw 
## rgveda
* Sample URL: https://sanskrit-lexicon.github.io/rvlinks/rvhymns/rv10.086.html#rv10.086.08
* gra {912,8} ->10,86,8
* pw, pwkvn, pwg, sch `<ls>ṚV. 10,86,8</ls>`
* mw `<ls>RV. 10,86,8</ls>`
* ap90 `<ls>Rv. 10,86,8</ls>`

## Pāṇini aṣṭādhyāyī 
* Sample URL: https://ashtadhyayi.com/sutraani/8/4/5
* pw, pwkvn, pwg, sch `<ls>P. 8,4,5</ls>`
* ap90 `<ls>P. VIII,4,5</ls>`
* mw `<ls>Pāṇ. viii,4,5</ls>`

## Boehtlingk, Indische Sprüche, 2nd edition
* Sample URL: https://funderburkjim.github.io/boesp-prep/web1/boesp.html?7813
* pw, sch `<ls>Spr. 7813.</ls>`
* pwg `<ls>Spr. (II) 7813</ls>`
  * Note: `<ls>Spr. 7813</ls>` refers to 1st edition, and is not linked. 
* pwkvn `<ls>Spr. 7813.</ls>`
  * NOTE: basicadjust.php does not currently construct the link, although pwkvn.txt has the ls markup.

## Boehtlingk, Chrestomathie, 2nd edition
* Sample URL:  https://sanskrit-lexicon-scans.github.io/bchrest/index.html?123
* pw `<ls>Chr. 123.</ls>`

## rāmāyaṇa Gorresio edition
* Sample URL:  https://sanskrit-lexicon-scans.github.io/ramayanagorr?7,3,15
* pw, pwkvn, pwg `<ls>GORR. 7,3,15</ls>`  OR `<ls>R. GORR. 7,3,15</ls>`
* sch `<ls>R. Gorr. 7,3,15</ls>`
* mw `<ls>R. vii,3,15</ls>`  -> `<ls>R. 7,3,15</ls>`
   * alternates:   `<ls>R. (G) x,y,z</ls>`,  `<ls>R. (G.) x,y,z</ls>`, `<ls>R. [G] x,y,z</ls>`,  `<ls>R. ed Gorresio x,y,z</ls>`, 

## rāmāyaṇa Schlegel edition
The link target has only kandas 1 and 2.
* Sample URL:  https://sanskrit-lexicon-scans.github.io/ramayanaschl?2,3,15
* pw, pwkvn, pwg, sch  `<ls>R. 2,3,15</ls>`
  * Note: links are generated to the Schlegel edition *even if the kanda is NOT 1 or 2.*
     For example `<ls>R. 4,10,4</ls>` under agaRayant in sch.
     Thus handling of links for `<ls>R. X,Y,Z</ls>` in the various dictionaries needs to be 
     reexa,omed and untangled in basicadjust.php.

## mahābhārata Calcutta edition
* Sample URL: https://sanskrit-lexicon-scans.github.io/mbhcalc?5.1246
* pw. pwkvn, pwg `<ls>MBH. 5,1246</ls>`
  * sch  has ls markup, but no hyperlink constructed as of now
* mw `<ls>MBh. iii, 1246</ls>`
  * also  `MBh. (ed. Calc.)`  but no hyperlink constructed as of now  (example EndrAgnya).

## harivaṃśa Calcutta edition
* Sample URL: https://sanskrit-lexicon-scans.github.io/hariv?1300
* pw, pwkvn, pwg `<ls>HARIV. 1300</ls>`
* sch, mw `<ls>Hariv. 1300</ls>`
