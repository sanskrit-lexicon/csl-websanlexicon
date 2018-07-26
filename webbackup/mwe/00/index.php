<?php
error_reporting( error_reporting() & ~E_NOTICE );
?>
<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8" />
 <title>MWE</title>
 <!--<link rel="stylesheet" href="webtc/main.css" type="text/css" />-->
 <style>
body {
color: black; background-color: #DBE4ED;
font-size: 14pt;
}
#disp {
  position:absolute;
  left: 1%;
  width: 98%;
 background-color: white;
 overflow-y:auto;
 overflow-x:hidden;
 height: 400px;
}
#title {
font-family: verdana,arial,helvetica,sansserif;
font-size: 14pt;
text-align:left;
}
 </style>
</head>
<body>
   <table width="100%"> 
     <tr><td width="10%">
      <a href="http://www.sanskrit-lexicon.uni-koeln.de/">
      <img id="unilogo" src="images/cologne_univ_seal.gif"
           alt="University of Cologne" width="60" height="60"
	   title="Cologne Sanskrit Lexicon"/>
      </a>
      </td>
      <td><span id="title">Monier-Williams English-Sanskrit Dictionary</span></td>
      </tr>
    </table>
 <div id="disp" class="disp">
 <ol><b>Available displays</b>
  <li><a href="webtc/indexcaller.php">Basic display</a></li>
  <li><a href="webtc1/index.php">List display</a></li>
  <li><a href="webtc2/index.php">Advanced Search</a></li>
  <li><a href="mobile1/index.php">Mobile-friendly display</a></li>

 </ol>
 <ol>
  <a href="webtc/download.html">Downloads</a>
 </ol>
 
 <ol ><b>Related material</b>
  <li><a href="http://www.sanskrit-lexicon.uni-koeln.de/scans/csldoc/dictionaries/mwe.html">Front Matter</a></li>
  <li><a href="http://www.sanskrit-lexicon.uni-koeln.de/scans/MWEScan/2013/downloads/mweheader.xml">License of Digital Edition</a></li>
  <li><a href="https://www.worldcat.org/title/dictionary-english-and-sanscrit/oclc/5333096">WorldCat reference</a></li>
  <li>Bibliographic entry: MONIER-WILLIAMS, M. (1851). A dictionary, English and Sanscrit. London, W.H. Allen and Co.</li>
 </ol>

 </div>
<script type="text/javascript" src="/js/piwik_analytics.js"></script>
</body>
</html>
