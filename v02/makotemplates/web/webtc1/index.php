<!DOCTYPE html>
<html>
 <head>
 <meta charset="UTF-8" />
  <title>${dicttitle} List</title>
  <link rel="stylesheet" type="text/css" href="../webtc/main.css" />
  <link rel="stylesheet" type="text/css" href="main.css" />
   <link rel="stylesheet" href="../webtc/font.css" type="text/css">
  <link rel="stylesheet" type="text/css" href="keyboard.css"/>

  <script type="text/javascript" src="../js/jquery.min.js"></script>
  <script type="text/javascript" src="transcoderjs/transcoder3.js"> </script>
  <script type="text/javascript" src="transcoderjs/transcoderJson.js"> </script>

  <script type="text/javascript" src="transcoderfield_VKI.js"> </script>
  <script type="text/javascript" src="keyboard.js"></script>
  <script type="text/javascript" src="main.js"> </script>

 </head>
 <body>
 <div id="dictid"> 
     <a href="//www.sanskrit-lexicon.uni-koeln.de/"
	style="background-color:#DBE4ED">
     <img id="unilogo" src="../images/cologne_univ_seal.gif"
            width="60" height="60" alt="University of Cologne"
	  title="Cologne Sanskrit Lexicon"></a>
     <span id="title">${dictname}</span>
 </div>

<div id="preferences">
<input type='button' id='preferenceBtn'  value='Preferences' style='position:relative; bottom: 5px;' />
&nbsp;&nbsp;
<textarea id='key1' name='TEXTAREA'  rows='1' cols='20' onkeydown='keyboard_HandleChar(event);'></textarea>
&nbsp;
<script type="text/javascript">
 function keyboard_HandleChar(event) {
 //console.log('keyboard_handleChar:',event.keyCode);
 if (event.keyCode != 13) return;
 // getWord_keyboard(false,false); removed 01-08-2025, see issues/issue53
 if (event.stopPropagation) 
  event.stopPropagation();
 else event.cancelBubble = true;
 if (event.preventDefault) event.preventDefault();
 else event.returnValue = false;
 }
</script>

&nbsp;
&nbsp;
<a href="/php/correction_form.php?dict=${dictup}" target="Corrections">Corrections</a>

%if dictaccent:
&nbsp; &nbsp;
<select name="accent" id="accent">
 <option value="yes">Show Accents</option>
 <option value="no" selected="selected">Ignore Accents</option>
</select>
%endif

&nbsp;&nbsp;<a href="help/help.html" target="_top">Help</a>

</div>

<div id="disp">
</div>
<div id="displist" class="displist">
</div>
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

</body>
</html>

