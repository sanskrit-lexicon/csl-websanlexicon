<?php
/* listparm.php  Jul 10, 2015  Contains ListParm class, which
  converts various $_REQUEST parameters into member attributes. 
  Parameters are for listhier view. Essentially extends the
  Parm class of webtc/parm.php.
  $_REQUEST   Parm attribute   Related attribute
  filter  filter0          filter
  transLit filterin0       filterin
  key     keyin            keyin1, key
  dict    dict             dictinfo   ***
  accent  accent
 *** for individual dictionaries, this parameter is provided to
 the constructor
 Aug 4, 2015 - synonym for $_REQUEST:
  input == transLit
  output == filter
 Jun 2, 2017. changed $_REQUEST to $_REQUEST
 11-18-2020 Due to changes in webtc,  it is now necessary to
 modify $_REQUEST  so that webtc/parm constructor will also
 pick up the 'filter' and 'transLit' parameters
 12-01-2022
*/
require_once('../webtc/dictinfo.php');
require_once('../webtc/parm.php');
require_once('../webtc/dbgprint.php');
class ListParm extends Parm {
 # from Parm
  #public $filter0,$filterin0,$keyin,$dict,$accent;
  #public $filter,$filterin;
  #public $dictinfo,$english;
  #public $keyin1,$key;
 # new for ListParm
 public $direction;
 public $phoneticInput,$serverOptions,$viewAs;
 public $keyboard;
 public function __construct() {
  // Part 1 of construction identical to Parm class
  parent::__construct();  // Parm's constructor
  $dict = $this->dict;  // from Parm constructor
  // direction: either 'UP', 'DOWN', or 'CENTER' (default)
  // 11-07-2024. Correct erroneous coding 
  $direction = $this->init_request_listparm('direction',array('UP', 'DOWN', 'CENTER'),'CENTER');
  
  // Two 'styles' are supported, as determined by presence (or absence) of
  //  'keyboard'
  //$this->keyboard = $_REQUEST['keyboard']; // not used 06-19-2024.

  $this->filter = null;
  $this->filterin = null;
  $flag = true;
  if (isset($_REQUEST['filter'])) {
   $this->filter = $_REQUEST['filter'] ;
  } else {
   $flag = false;
  }
  
  if (isset($_REQUEST['filterin'])) {
   $this->filterin = $_REQUEST['filterin'] ;
  } else {
   $flag = false;
  }

  if ($flag) {
   return;
  }
  list($this->filter ,$this->filterin) = $this->getParameters_keyboard();
  if (in_array($dict,array('ae','mwe','bor'))) {
   // force filterin to be 'slp1' for dictionaries with english headwords
   $this->filterin = 'slp1';
  }
  // 11-18-2020
  if (!isset($_REQUEST['filter'])) {
   $_REQUEST['filter'] = $this->filter;
  }
  if (!isset($_REQUEST['transLit'])) {
   $_REQUEST['transLit'] = $this->filterin;
  }
  // recompute $this->key, 
  $this->key = transcoder_processString($this->keyin1,$this->filterin,"slp1");

 }  

public function getParameters_keyboard() {
//inputType = $_REQUEST['inputType'];
//unicodeInput = $_REQUEST['unicodeInput'];

 $phoneticInput = $_REQUEST['phoneticInput'];
 $serverOptions = $_REQUEST['serverOptions'];
 $viewAs = $_REQUEST['viewAs'];
 $this->phoneticInput = $phoneticInput;
 $this->serverOptions = $serverOptions; 
 $this->viewAs = $viewAs;
 // deduce filter  and filterin  from the above
 $filterin = $this->getParameters_keyboard_helper($viewAs,$phoneticInput);
 $filter = $this->getParameters_keyboard_helper($serverOptions,$phoneticInput);
 $dbg = false;
 if ($dbg) {
  dbgprint($dbg,"listparm.php:\n");
  dbgprint($dbg,"viewAs=$viewAs, phoneticInput=$phoneticInput\n");
  dbgprint($dbg,"serverOptions=$serverOptions\n");
  dbgprint($dbg,"filterin=$filterin, filter=$filter\n");  
 }
 return array($filter ,$filterin );
 
}
 public function init_request_listparm($key,$values,$default) {
  //11-07-2024 replace faulty init_request($keys,$default)
  $ans = $default;
  if (isset($_REQUEST[$key])) {
   $val = $_REQUEST[$key];
   if (in_array($val,$values)) {
    $ans = $val;
   }
  }
  return $ans;
 }

public function getParameters_keyboard_helper($type,$phoneticInput) {
 if ($type == 'deva') {return $type;}
 if ($type == 'roman') {return $type;}
 if ($type == 'phonetic') {
  if ($phoneticInput == 'slp1') {return $phoneticInput;}
  if ($phoneticInput == 'hk') {return $phoneticInput;}
  //if ($phoneticInput == 'it') {return $phoneticInput;}
  if ($phoneticInput == 'it') {return 'itrans';}
  if ($phoneticInput == 'wx') {return $phoneticInput;}
 }
 // default: 
 return "slp1";
}

}

?>
