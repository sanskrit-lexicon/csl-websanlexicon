<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<!DOCTYPE html>
<html>
 <head>
 <meta charset="UTF-8" />
   <title>${dicttitle} Simple</title>
    <link rel="stylesheet" href="main.css" type="text/css">
   <link rel="stylesheet" href="font.css" type="text/css">
  <script type="text/javascript" src="../js/jquery.min.js"></script>
  <script type="text/javascript" src="../js/jquery.cookie.js"></script>

  <script type="text/javascript" src="main_webtc.js"> </script>
<style>
#title {
font-family: verdana,arial,helvetica,sansserif;
font-size: 14pt;
}
</style>
 </head>
 <body>
 <table width="100%">
  <tr>
    <td width="10%">
      <a href="//www.sanskrit-lexicon.uni-koeln.de/">
      <img id="unilogo" src="../images/cologne_univ_seal.gif"
           alt="University of Cologne" width="60" height="60" 
           title="Cologne Sanskrit Lexicon"/>
      </a>
      </td>
      <td>
        <span id="title">${dictname}</span>
      </td>
      </tr>
    </table>
<?php init_inputs(); ?>
  <table width="100%" cellpadding="5">
   <tr>
   <td>recherche:&nbsp;
<?php
global $inithash;
 $init=$inithash['word'];
 echo '<input type="text" name="key" size="20" id="key" ';
 echo "value=\"$init\" />\n";
?>
   </td>
   <td>entrée:&nbsp;
    <select name="transLit" id="transLit">
<?php
global $inithash;
 $init=$inithash['translit'];
 output_option("hk","Kyoto-Harvard",$init);
 output_option("slp1","SLP1",$init);
 output_option("itrans","ITRANS",$init);
?>
    </select>
   </td>
  </tr>

  <tr>
   <td>
 <input type="button" onclick="getWord();" value="Chercher" id="searchbtn" />

   </td>
   <td>sortie:
    <select name="filter" id="filter">
<?php
global $inithash;
$init = $inithash['filter'];
output_option("deva","Devanagari Unicode",$init);
 output_option("hk","Kyoto-Harvard",$init);
 output_option("slp1","SLP1",$init);
 output_option("itrans","ITRANS",$init);
 output_option("roman","Roman Unicode",$init);
?>
    </select>

   </td>
   <td>
   <table><tr>
   
   <td><a href="/php/correction_form.php?dict=${dictup}" target="Corrections">Corrections</a></td>
  
<!--
   <td>
     <a href="download_fr.html" target="output"><b>Téléchargements</b></a>
   </td>
-->
   <td><a href="help_fr.html" target="_top">Aide</a></td>
   </tr></table>
  </td>
  </tr>
 </tbody>
</table>
 <div id="disp" class="disp">
 </div>
   <input name="input" id="input_input" value="hk" style="visibility:hidden" />
   <input name="output" id="input_output" value="deva" style="visibility:hidden" />
 <?php 
 // set invisible 'indexcaller' 
 $x = $_GET['translit'];
 if (!$x) {$x = $_GET['input'];}
 $y = $_GET['filter'];
 if (!$y) {$y = $_GET['output'];}
 if ($x ||$y) {
  $val="YES";
 }else {
  $val="NO";
 }
 $id = "indexcaller";
 echo "<input name=\"$id\"  id=\"$id\" value=\"$val\"  style=\"visibility:hidden\" />";
 ?>

</body>
</html>
<?php 
function init_inputs() {
// from GET parameters, initialize $inithash
global $inithash;
$inithash=array();
 // word = citation
 $x = $_GET['word'];
 if (!$x) {$x = $_GET['citation'];}
 if (!$x) {$x = $_GET['key'];}
 if (!$x) {$x = "";}
 $inithash['word'] = $x;

 // translit = input
 $x = $_GET['translit'];
 if (!$x) {$x = $_GET['input'];}
 if (!$x) {$x = "";}
 $translit0 = $x;
 // filter = output
 $x = $_GET['filter'];
 if (!$x) {$x = $_GET['output'];}
 if (!$x) {$x = "";}
 $filter0=$x;

 // normalization of translit and filter.
 // translit0 may have substrings HK,SLP,IT which are converted
 // to translit = hk,slp1,itrans
 // filter0 may have substring HK,SLP2,IT,DEVA,ROMAN, which are converted
 // to filter = hk,slp1,itrans,deva,roman
 $x = strtoupper($translit0);
 if (preg_match('/SL/',$x)) {
  $x="slp1";
 }else if (preg_match('/IT/',$x)) {
  $x="itrans";
 }else {
  $x="hk";
 }
 $translit = $x;
 // normalization of filter, using old parameters
 // slp1 is default
 $x = strtoupper($filter0);
 if (preg_match('/SL/',$x)) {
  $x="slp1";
 }else if (preg_match('/IT/',$x)) {
  $x="itrans";
 }else if (preg_match('/DEVA/',$x)) {
  $x="deva";
 }else if (preg_match('/HK/',$x)) {
  $x="hk";
 }else {
  $x="roman";
 }
 $filter = $x;
// 
 // initializing $inithash
 $inithash['translit'] = $translit;
 $inithash['filter'] = $filter;

}

 function output_option ($value,$display,$initvalue) {
  echo "  <option value='$value'";
  if ($initvalue == $value) {
   echo " selected='selected'";
  }
  echo ">$display</option>\n";
}

?>
