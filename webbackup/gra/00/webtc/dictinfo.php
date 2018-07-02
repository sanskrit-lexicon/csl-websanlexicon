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
 public $sqlitefile;  // path to primary sqlite data (e.g. gradb.sqlite)
 public $transcodefile; // path to transcoder.php
 public function __construct($dict) {
  $this->dict=strtolower($dict);
  $this->dictupper=strtoupper($dict);
  $this->english = in_array($this->dictupper,array("AE","MWE","BOR"));
  $dir = dirname(__FILE__); //directory containing this php file
  $dir1 = "$dir/"; # Note: $dir does not end in '/'
  $this->webpath = realpath("$dir/../");
  $this->sqlitefile = "{$this->webpath}/sqlite/{$this->dict}.sqlite";
  $this->transcodefile = "{$this->webpath}/utilities/transcoder.php";
 }

}

?>
