<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<!DOCTYPE html>
<html>
 <head>
 <meta charset="UTF-8" />
  <title>MWE List</title>
  <link rel="stylesheet" type="text/css" href="main.css" />
  <link rel="stylesheet" type="text/css" href="keyboard.css"/>
  <style type="text/css">
#dictid {  /* override webtc1/main.css */
 font-size: 14pt;
 right: 5px;
 width: 100%; /* override main.css */
}
/* Move dictcit and disp and displist up since dictnav is gone*/
#dictcit,#preferences,.keyboardinput,#accent {top:85px;}
#disp {top:120px;}
#displist {top:120px;}
#title {
font-family: verdana,arial,helvetica,sansserif;
font-size: 14pt;
}
 </style>

  <script type="text/javascript" src="../js/jquery.min.js"></script>
  <script type="text/javascript" src="transcoderjs/transcoder3.js"> </script>
  <script type="text/javascript" src="transcoderjs/transcoderJson.js"> </script>

  <script type="text/javascript" src="transcoderfield_VKI.js"> </script>
  <script type="text/javascript" src="keyboard.js"></script>
  <script type="text/javascript" src="main.js"> </script>

 </head>
 <body>
 <div id="dictid"> 
     <a href="http://www.sanskrit-lexicon.uni-koeln.de/"
	style="background-color:#DBE4ED">
     <img id="unilogo" src="../images/cologne_univ_seal.gif"
            width="60" height="60" alt="University of Cologne"
	  title="Cologne Sanskrit Lexicon"></a>
     <span id="title">Monier-Williams English-Sanskrit Dictionary</span>
 </div>
<!-- put help link as in basic display.
 <div id="dictnav">
 <ul class="nav"
   <li class="nav">
     <a class="nav" href="help/help.html" target="output">Help</a>
   </li>

   <li class="nav">
    &nbsp;
    <a class="nav" href="http://www.sanskrit-lexicon.uni-koeln.de/index.html" target="_top">Home</a>
   </li> 
 </ul>
 </div>
-->


<div id="preferences">
<input type='button' id='preferenceBtn'  value='Preferences' style='position:relative; bottom: 5px;' />
&nbsp;&nbsp;
<textarea id='key1' name='TEXTAREA'  rows='1' cols='20' onkeydown='keyboard_HandleChar(event);'></textarea>
&nbsp;
<script type="text/javascript">
 function keyboard_HandleChar(event) {
 //console.log('keyboard_handleChar:',event.keyCode);
 if (event.keyCode != 13) return;
 getWord_keyboard(false,false); //chg1
 if (event.stopPropagation) 
  event.stopPropagation();
 else event.cancelBubble = true;
 if (event.preventDefault) event.preventDefault();
 else event.returnValue = false;
 }
</script>

&nbsp;
&nbsp;
<a href="/php/correction_form.php?dict=MWE" target="Corrections">Corrections</a>

<!-- there is no accent -->


</div>

<div id="disp">
</div>
<div id="displist" class="displist">
</div>
<script type="text/javascript" src="/js/piwik_analytics.js"></script>
</body>
</html>

