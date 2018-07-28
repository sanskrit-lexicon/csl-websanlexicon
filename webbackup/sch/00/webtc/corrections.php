<!-- This works:
<a href="https://docs.google.com/forms/d/1InNaDMuakzrKpkSXlzVn0ocnD3My2uBMWypUEebrO4c/viewform?entry.1072768805=PWG,2013"  target="Corrections">Corrections</a>
<a href="https://docs.google.com/forms/d/1InNaDMuakzrKpkSXlzVn0ocnD3My2uBMWypUEebrO4c/viewform?entry.1072768805=PWG&entry.1617637348&entry.196160492&entry.1206918996&entry.598438288&entry.1252484998&entry.1607694510"  target="Corrections">Corrections</a> 
-->
<!-- Jan 23, 2014
  Generic function returns the href for the Google form
  'Sanskrit-Lexicon Correction Form'
  Usage: The caller 
-->
<?php
function correction_googleform_href($dictid) {
 $d = urlencode($dictid);
 //$d = $dictid;
 //$href = "https://docs.google.com/forms/d/1InNaDMuakzrKpkSXlzVn0ocnD3My2uBMWypUEebrO4c/viewform?entry.1072768805=$d";
 $href = "http://www.sanskrit-lexicon.uni-koeln.de/scans/PWGScan/2013/web/webtc/corr2.php?dict=$d";
 return $href;
}
function old_correction_googleform_href($dictid) {
 // works with google form directly
 $d = urlencode($dictid);
 //$d = $dictid;
 $href = "https://docs.google.com/forms/d/1InNaDMuakzrKpkSXlzVn0ocnD3My2uBMWypUEebrO4c/viewform?entry.1072768805=$d";
 return $href;
}
?>

