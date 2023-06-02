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
 public $year = '${dictyear}';  # template variable used in get_cologne_webPath method
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
 public function get_parent_dirpfx($base) {
  $dirpfx = "../../"; // apidev  Not portable
  #$ds = DIRECTORY_SEPARATOR;
  for($i=1;$i<10;$i++) {
   $d = dirname(__FILE__,$i);
   $b = basename($d);
   if ($b == $base) {
    $d = dirname(__FILE__,$i+1);
    $dirpfx = "$d/";
    break;
   }
  }
  return $dirpfx;
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
 public function get_pdfpages_url() {
  /* Assume this method called only from servepdf, which is in web/webtc folder
  */
  $dbg=false;
  include("dictinfowhich.php");
  $cologne_url = $this->get_cologne_pdfpages_url();
  if ($dictinfowhich == 'cologne') {
   return $cologne_url;
  }
  // otherwise, $dictinfowhich == 'xampp'
  // Try relative url, either in web directory, or parent of web directory
  // 10-26-2019 First choice is cologne/scans/xxx/pdfpages; relative to where
  // We are (in cologne/xxx/web/webtc); so rel path is ../../../scans/xxx/pdfpages
  // dictionary code xxx is available as $this->dict
  // Use relative url if it is a non-empty directory.
<%doc>
 mako comment
  // Since this file is processed as a mako template, we can't use
  // ${this->dict} syntax. So,
</%doc>
  $dict = $this->dict;
  $testpaths = array ( "../../../scans/$dict/pdfpages", "../pdfpages",   "../../pdfpages" );
  foreach($testpaths as $testpath) {
   if (!$this->dir_is_empty($testpath)) {
    return $testpath;
   }
  }
  // Use Cologne url as a fallback
  return $cologne_url;
 } 

 public function dir_is_empty($dir) {
  /* ref: https://stackoverflow.com/questions/7497733/how-can-i-use-php-to-check-if-a-directory-is-empty
   Note this is just a function. Put into this class for convenience of this
   application.  Currently used only by get_pdfpages_dir()
  */
  if (! is_dir($dir)) { 
   return TRUE; 
  }
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      closedir($handle);
      return FALSE;
    }
  }
  closedir($handle);
  return TRUE;
}
 public function get_cologne_pdfpages_url() {
 /* These urls are current as of 08-31-2019
 */
 $cologne_pdfpages_urls = array(
  "ACC"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/ACCScan/ACCScanpdf" ,
  "AE"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/AEScan/AEScanpdf" ,
  "AP"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/APScan/APScanpdf" ,
  "AP90"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/AP90Scan/AP90Scanpdf" ,
  "BEN"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/BENScan/BENScanpdf" ,
  "BHS"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/BHSScan/BHSScanpdf" ,  
  "BOP"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/BOPScan/BOPScanpdf" ,
  "BOR"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/BORScan/BORScanpdf" ,
  "BUR"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/BURScan/BURScanpdf" ,
  "CAE"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/CAEScan/CAEScanpdf" ,
  "CCS"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/CCSScan/CCSScanpng" ,  
  "GRA"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/GRAScan/GRAScanpdf" ,
  "GST"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/GSTScan/GSTScanpdf" ,
  "IEG"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/IEGScan/IEGScanpdf" ,
  "INM"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/INMScan/INMScanpdf" ,
  "KRM"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/KRMScan/KRMScanpdf" ,
  "MCI"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/MCIScan/MCIScanpdf" , 
  "MD"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/MDScan/MDScanjpg" ,
  "MW"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/MWScan/MWScanpdf" ,
  "MW72"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/MW72Scan/MW72Scanpdf" ,
  "MWE"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/MWEScan/MWEScanpdf" ,
  "PD"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PDScan/PDScanpdf" ,
  "PE"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PEScan/PEScanpdf" ,
  "PGN"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PGNScan/PGNScanpdf" ,
  "PUI"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PUIScan/PUIScanpdf" ,
  "PWG"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PWGScan/PWGScanpdf" ,
  "PW"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PWScan/PWScanpng" ,
  "SCH"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/SCHScan/SCHScanpdf" ,
   "SHS"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/SHSScan/SHSScanpdf" ,
  "SKD"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/SKDScan/SKDScanpdf" ,
  "SNP"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/SNPScan/SNPScanpdf" ,
  
  "STC"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/STCScan/STCScanpdf" ,
  "VCP"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/VCPScan/VCPScanpdf" ,
  "VEI"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/VEIScan/VEIScanpdf" ,
  "WIL"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/WILScan/WILScanjpg" ,
  "YAT"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/YATScan/YATScanpdf" ,
  "LAN"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/LANScan/LANScanpdf" ,
  "ARMH"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/ARMHScan/ARMHScanpdf" ,
  "PWKVN"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/PWScan/PWScanpng" ,
  "LRV"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/LRVScan/LRVScanpdf" ,
  "ABCH"=>"//www.sanskrit-lexicon.uni-koeln.de/scans/ABCHScan/pdfpages" ,
 );
 $url = $cologne_pdfpages_urls[$this->dictupper];
 return $url;
 }
}

?>
