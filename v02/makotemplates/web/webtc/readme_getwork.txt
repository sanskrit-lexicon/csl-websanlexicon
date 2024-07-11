
07-08-2024

getword.php  The main entry point for generating html from xml.
  Involved in main_webtc.js function 'getword'
  calls getwordClass in getwordClass.php
  Echoes $table1
getwordClass.php
  $temp = new Getword_data($basicOption);  [class Getword_data]
    
  $matches = $temp->matches
    Array of html string [variable $htmlmatches in Getword_data code]
  $this->table1 = $this->getword_html();
  getword_html:
   $table = $this->getwordDisplay($getParms,$matches);
   $table1 transcodes the table
  getwordDisplay:
   For each $dbrec in $matches:
    $dispItem = new DispItem($dict,$dbrec);
    DO some adjustments to each of the html strings.



getword_data.php
  $xmlmatches = $dal->get1_mwalt($key); ## from xxx.xml
  $xmldata array of the 'data' fields of xmlmatches.
  $adjxml = new BasicAdjust($getParms,$xmldata)
    This adjusts each of the $xmldata records
    
    getword_data_html_adapter applied to each
      This done by BasicDisplay(...)
   
