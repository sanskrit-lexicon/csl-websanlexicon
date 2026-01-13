issues/issue54/readme.txt

Technical details regarding implementing the orphus method of user
correction submission for webtc, webtc1, webtc2 and all dictionaries.

* TODO orphus
In csl-websanlexicon/v02/
** DONE newer version of jquery
Replace makotemplates/web/js/jquery.min.js with csl-apidev/js/jquery.min.js   
** DONE orphus.customized.js
   copy from csl-apidev/js/ to makotemplates/web/js/
** DONE add orphus.customized.js to inventory.txt
   add following line to inventory.txt
   *:web/js/orphus.customized.js:C
** DONE orphus_init_code
<script type="text/javascript" src="../js/orphus.customized.js"></script>
<script type="text/javascript">
  $(window).on("load",function() {
  var correctionsUrl = 'https://www.sanskrit-lexicon.uni-koeln.de/scans/csl-corrections/app/correction_form_response.php';
  <?php 
   require_once("../webtc/parm.php");
   $getParms = new Parm();
   $dict = $getParms->dict;
   $key = $getParms->key;
  ?>
  var key = <?php echo "'$key'";?>;
  var dict = <?php echo "'$dict'";?>;
  //console.log('listview.php script: key=',key,'dict=',dict);
  orphus.init({
    correctionsUrl: correctionsUrl,
    params: {
      entry_hw: key,
      entry_new: '',
      entry_old: '',
      entry_email: '',
      entry_L: '',
      entry_dict: dict,
      entry_comment: '',
    }
  });
 });
</script>


** DONE insert orphus_init_code before </body> in 3 files
*** DONE makotemplaces/web/webtc/indexcaller.php
*** DONE makotemplaces/web/webtc1/index.php
*** DONE makotemplaces/web/webtc2/index.php
** DONE remake dictionary FRI in local installation
in csl-websanlexicon/v02/,
  sh generate_web.sh fri  ../../fri

** DONE Generate a test change for each of the 3 displays:
 use /c/xampp/htdocs/sanskrit-lexicon/CORRECTIONS/daily/dailydown.py
 to retrieve the cfr file and confirm that the (test) changes were posted.
** regenerate the displays in local installation
In csl-websanlexicon/v02/
sh redo_xampp_all.sh

** DONE sync csl-websanlexicon to Github
cd /c/xampp/htdocs/cologne/csl-websanlexicon/v02
git pull # check up to date
git add .
git commit -m "orphus method of correction submission #54"
** sync to Cologne server
 login to cologne, cd to csl-websanlexicon/v02
git pull
sh redo_cologne_all.sh

