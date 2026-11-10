<?php
/* gen_fuzz.php — H7948 deterministic fuzz corpus for the link-target
 * golden parity gate. No randomness: the cartesian product of the
 * abbreviation vocabulary x citation shapes x dicts x keys, printed as
 * run_golden.php corpus lines (id <TAB> dict <TAB> key <TAB> code <TAB> data).
 *
 * This is the breadth layer behind corpus.tsv's named cases: any pattern or
 * href transcription drift between the pre-H7948 and refactored chains shows
 * up as a non-empty old-vs-new diff on these inputs.
 */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "cli only\n"); exit(2); }
$abbrevs = array(
 'Spr.', 'Spr. (II)', 'Spr. (I)', 'MBH.', 'MBH. ed. Bomb.', 'MBH. ed. Calc.',
 'HARIV.', 'Verz. d. Oxf. H.', 'Verz. der Oxf. H.', 'KATHĀS.', 'VS.',
 'RĀJA-TAR.', 'RĀJAT.', 'RĀJA-TAR. ed. Calc.', 'RĀJAT. ed. Calc.',
 'ṚV. PRĀT.', 'ṚV. PRĀTIŚ.', 'YĀJÑ.', 'RAGH.', 'RAGH. ed. ST.',
 'RAGH. ed. Calc.', 'RAGH. (ed. Calc.)', 'RAGH. (Calc.)', 'MĀRK. P.',
 'BHAG.', 'H. an.', 'an.', 'ŚĀK.', 'AIT. BR.', 'MĀLAV.', 'PAÑCAT.',
 'PAÑCAT. ed. orn.', 'ed. orn.', 'HIT.', 'AK.', 'COL.', 'COLEBR.',
 'AK. ed. COLEBR.', 'GĪT.', 'NIR.', 'NAIGH.', 'VOP.', 'BHAṬṬ.',
 'KUMĀRAS.', 'ŚAT. BR.', 'TS.', 'TBR.', 'KĀTY. ŚR.', 'PAÑCAR.', 'VIKR.',
 'VIKRAM.', 'N.', 'DAŚ.', 'VID.', 'CAURAP.', 'VIŚV.', 'BHARTṚ.',
 'BHARTṚ. Suppl.', 'MEGH.', 'ŚṚṄGĀRAT.', 'MED.', 'TRIK.', 'HĀR.',
 'H. ś.', 'ś.', 'H.', 'HALĀY.', 'M.', 'VARĀH. BṚH. S.', 'SĀH. D.',
 'Chr.', 'DHĀTUP.', 'BHĀG. P.', 'BHĀG. P. ed. Bomb.', 'R. ed. Bomb.',
 'R.', 'ṚV.', 'AV.', 'P.', 'R. GORR.', 'R. ed. GORR.', 'GORR.', 'R. SCHL.',
);
$shapes = array(
 '', ' 5', ' 5,6', ' 5,6,7', ' 5,6,7,8', ' 7,8', ' 78,1', ' 1 , 2 , 3',
 ' 1, 2, 3', ' 1 2 3', ' I,5', ' IV,2', ' III', ' Pr. 5', ' S. 12', ' 12.',
);
$dicts = array('pwg', 'pw', 'pe');
$keys = array('', 'pAWIna');
$i = 0;
foreach ($abbrevs as $a) {
 foreach ($shapes as $s) {
  foreach ($dicts as $d) {
   foreach ($keys as $k) {
    $i++;
    echo 'fz' . $i . "\t" . $d . "\t" . $k . "\t" . $a . "\t" . $a . $s . "\n";
   }
  }
 }
}
fwrite(STDERR, "fuzz cases: $i\n");
