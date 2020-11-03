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
*/
require_once('../webtc/dictinfo.php');
require_once('../webtc/parm.php');
#require_once('dbgprint.php');
class ListParm extends Parm {
 # from Parm
  #public $filter0,$filterin0,$keyin,$dict,$accent;
  #public $filter,$filerin;
  #public $dictinfo,$english;
  #public $keyin1,$key;
 # new for ListParm
 public $direction;
 public $phoneticInput,$serverOptions,$viewAs;

 public function __construct() {
  // Part 1 of construction identical to Parm class
  parent::__construct();  // Parm's constructor
  $dict = $this->dict;  // from Parm constructor
  // direction: either 'UP', 'DOWN', or 'CENTER' (default)
  $direction = $_REQUEST['direction'];
  if(!$direction) {$direction = $argv[2];}
  if (($direction != 'UP') && ($direction != 'DOWN')) {
   $direction = 'CENTER';
  }
  // Two 'styles' are supported, as determined by presence (or absence) of
  //  'keyboard'
  $this->keyboard = $_REQUEST['keyboard'];
  list($this->filter ,$this->filterin) =$this->getParameters_keyboard();
  if (in_array($dict,array('ae','mwe','bor'))) {
   // force filterin to be 'slp1' for dictionaries with english headwords
   $this->filterin = 'slp1';
  }
  // recompute $this->key, 
  $this->key = transcoder_processString($this->keyin1,$this->filterin,"slp1");

 }  
/*
public function unused_getParameters() {
 $filter0 = $_REQUEST['filter'];
 $filterin0 = $_REQUEST['transLit']; 
 if (!$filter0) {$filter0 = "SLP2SLP";}
 if (!$filterin0) {$filterin0 = "SLP2SLP";}
 return array($filter0,$filterin0);
}
*/
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
 return array($filter ,$filterin );
 
}
function getParameters_keyboard_helper($type,$phoneticInput) {
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
