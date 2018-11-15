<?php
/* dictinfo.php. 
 06-28-2018
*/
class DictInfo {
 public $dict;
 public $dictupper;
 # $english is flag indicating whether to transcode headwords
 # false means 'yes, transcode'; true means 'no, do not transcode'
 public $english;  
 # $webpath is relative path to the 'web' directory for this dictionary
 public $webpath;
 public $webparent;
 public $sqlitedir;
 #public $sqlitefile;  // path to primary sqlite data (e.g. gradb.sqlite)
 #public $abfile;      // path to abbreviation sqlite file (e.g.graab.sqlite)
 #public $bibfile;     // path to bibliography file for pw, pwg
 public $advsearchfile;  // path to query_dump file used by webtc2 display.
 public $transcodefile; // path to transcoder.php
 public $year = '${dictyear}';  # used in get_cologne_webPath method
 public function __construct($dict) {
  $this->dict=strtolower($dict);
  $this->dictupper=strtoupper($dict);
  $this->english = in_array($this->dictupper,array("AE","MWE","BOR"));
  $dir = dirname(__FILE__); //directory containing this php file
  $dir1 = "$dir/"; # Note: $dir does not end in '/'
  $this->webpath = realpath("$dir/../");
  $this->webparent = realpath("$dir/../../");
  # go from webparent to web. This for dev convenience.
  # Suppose a dev version of 'web' is installed in 'web1', and that
  # web1 and web are siblings.
  # Suppose web1 does not have the sqlite and webtc2 data files.
  # Then, the next formulations use these files from the 'web' directory.
  #  
  #$this->sqlitefile = "{$this->webpath}/sqlite/{$this->dict}.sqlite";
  #$this->sqlitefile = "{$this->webparent}/web/sqlite/{$this->dict}.sqlite";
  $this->sqlitedir = "{$this->webparent}/web/sqlite";
  $this->advsearchfile = "{$this->webparent}/web/webtc2/query_dump.txt";
  #$this->abfile = "{$this->webparent}/web/sqlite/{$this->dict}ab.sqlite";
  #$this->bibfile = "{$this->webparent}/web/sqlite/{$this->dict}bib.sqlite";
  $this->transcodefile = "{$this->webpath}/utilities/transcoder.php";
 }
 public function get_cologne_webPath() {
  // 04-17-2018
  // used by servepdf.php
  // Cologne scan directory 
  $cologne_scandir = "//www.sanskrit-lexicon.uni-koeln.de/scans";
  $path = $cologne_scandir . "/{$this->dictupper}Scan/{$this->year}/web";
  return $path;
 }
 public function get_year() {
  return $this->year;
 }
 public function get_webPath() {
  return $this->webpath;
 }

}

?>
