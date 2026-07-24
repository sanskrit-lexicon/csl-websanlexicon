<?php  error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING); 
/* queryparm.php  Jul 10, 2015  Contains Queryparm class, which
  converts various $_GET parameters into member attributes. 
  Parameters are for listhier view. Essentially extends the
  Parm class of webtc/parm.php.
  $_GET   Parm attribute   Related attribute
  filter  filter0          filter
  transLit filterin0       filterin
  key     keyin            keyin1, key
  dict    dict             dictinfo   ***
  accent  accent
 *** for individual dictionaries, this parameter is provided to
 the constructor
 Aug 4, 2015 - synonym for $_GET:
  input == transLit
  output == filter
 Jun 2, 2017. changed $_GET to $_REQUEST
*/
require_once('../webtc/dictinfo.php');
require_once('../webtc/parm.php');
#require_once('dbgprint.php');
class Queryparm extends Parm {
 # from Parm
  #public $filter0,$filterin0,$keyin,$dict,$accent;
  #public $filter,$filerin;
  #public $dictinfo,$english;
  #public $keyin1,$key;
 # new for Queryparm
 public $filename,$lastLnum,$max;
 public $opt_sregexp,$opt_sword,$opt_stransLit;
 public $word, $opt_regexp, $sopt_case, $outopt;
 public $opt_swordhw; // both, hwonly, textonly
 public $accent;
 public function __construct($dict = null) {
  // Part 1 of construction identical to Parm class.
  // H1523: Parm::__construct() takes no args since Nov 2020 (dict from
  // dictcode.php). Passing $dict here is a PHP 8 ArgumentCountError.
  // Call sites may still pass $dictcode for historical API shape; ignored.
  parent::__construct();
  #Use parms filter and filterin from Parm 
  #$this->filter = $_REQUEST['filter'];
  #$this->opt_stransLit = $_REQUEST['transLit'];
  $this->opt_stransLit = $this->filterin; # rename filterin to opt_stransLit
  if (isset($_REQUEST['dictionary'])) {
   // The dictionary dump file lives in the app directory; strip any path
   // component (basename) and allowlist the name so ?dictionary= cannot
   // fopen() an arbitrary file (path traversal / LFI).
   $dictfile = basename($_REQUEST['dictionary']);
   if (preg_match('/^[A-Za-z0-9_.-]+$/', $dictfile)) {
    $this->filename = $dictfile;
   }else {
    $this->filename = "query_dump.txt";
   }
  }else {
   $this->filename = "query_dump.txt";
  }
  $this->lastLnum = $_REQUEST['lastLnum']; // file position, for seek&tell
  $this->max = $_REQUEST['max'];
  
  // parms for sanskrit word
  $this->opt_sregexp = $_REQUEST['sregexp'];
  $this->opt_sword = $_REQUEST['sword'];
  
  // parms for non-Sanskrit word
  if (isset($_REQUEST['word'])){
   $this->word = $_REQUEST['word'];
   $this->word = strtolower($this->word);
  }else {
   $this->word="";
  }
  $this->opt_regexp = $_REQUEST['regexp'];
  // H1523: keep scase string semantics (querymodel matchkey: "false" => case-sensitive)
  $this->sopt_case = isset($_REQUEST['scase']) ? $_REQUEST['scase'] : '';
  if (!is_string($this->sopt_case)) { $this->sopt_case = ''; }
  // only outopt4/outopt5 are known UI/API values
  $this->outopt = isset($_REQUEST['outopt']) ? $_REQUEST['outopt'] : 'outopt4';
  if (!in_array($this->outopt, array('outopt4','outopt5'), true)) {
   $this->outopt = 'outopt4';
  }
  $this->opt_swordhw = $_REQUEST['swordhw'];
  if (!in_array($this->opt_swordhw,array('both', 'hwonly', 'textonly'))) {
   $this->opt_swordhw = "hwonly";
  }
  // H1523: bound search strings + whitelist match mode (ReDoS / dump-scan cost)
  if (!is_string($this->opt_sword)) {$this->opt_sword = "";}
  if (mb_strlen($this->opt_sword) > 200) {
   $this->opt_sword = mb_substr($this->opt_sword, 0, 200);
  }
  if (!is_string($this->word)) {$this->word = "";}
  if (mb_strlen($this->word) > 200) {
   $this->word = mb_substr($this->word, 0, 200);
  }
  if (!in_array($this->opt_regexp, array('exact','prefix','suffix','instring','substring'), true)) {
   $this->opt_regexp = "exact";
  }
  // H1523: same whitelist for Sanskrit match mode (invalid fell through to bare
  // "$slpword.*[\\t]" in querymodel).
  if (!in_array($this->opt_sregexp, array('exact','prefix','suffix','instring','substring'), true)) {
   $this->opt_sregexp = "exact";
  }
  if (!($this->filename)) {$this->filename = "query_dump.txt";}
  // H1523: bound Advanced Search page size — untrusted ?max= is cast and
  // clamped so a huge value cannot force multi-million-line scan loops.
  $this->max = intval($this->max);
  if ($this->max < 1) {$this->max = 5;}
  if ($this->max > 100) {$this->max = 100;}
  if (!($this->lastLnum)) {$this->lastLnum = 0;}
  $this->lastLnum = intval($this->lastLnum);
  if ($this->lastLnum < 0) {
      $this->lastLnum=0;
  }
  if ($this->lastLnum > 25000000) {
      $this->lastLnum = 0;
  }  
  #$this->printparms(); # dbg
 }  
 public function printparms() {
  $dbg=true;
  dbgprint($dbg,"queryparms:\n");
  dbgprint($dbg," opt_sword={$this->opt_sword}\n");
  dbgprint($dbg," word={$this->word}\n");
 }

}

?>
