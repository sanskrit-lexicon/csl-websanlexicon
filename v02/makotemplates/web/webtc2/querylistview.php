<?php // querylistview.php 06-30-2018
class QueryListView {
 public $resultarr;
 public $dict;
 public function __construct($queryParms,$model) {
  $meta = '<meta charset="UTF-8">';
  $this->dict = $queryParms->dict;
  $matches = $model->querymatches;
  $lastLnum = $model->lastLnum;
  $this->resultarr = array("$lastLnum");
  if (count($matches) == 0) {
   $errmsg = "<p>No matches found</p>";
   $this->resultarr[] = $errmsg;   
  }else {
   $content = $this->display_outopt4($matches,$model->search_regexp_nonSanskrit);
   $this->resultarr[] = $content;
  }
 }
 public function display_outopt4 ($lines,$search_regexp_nonSanskrit) {
 $nx=0;
 $xmlnew = "<p class='words'>\n";
 foreach($lines as $x) {
  if (preg_match('/^(.*?)\t(.*?)$/',$x,$matches)) {
   $nx++;
   $keypart = $matches[1];
   list($key,$sanskrit) = preg_split('|:|',$keypart);
   $key = trim($key);
   $y = $matches[2];
   if (in_array($this->dict,array('ae','mwe','bor'))) {
    // for English headword, no need to transcode key.
    $xmlnew .= "$nx <!-- $key --><a class='words' onclick='getWord4(\"$nx\");'>$key</a>";
   }else {
    $xmlnew .= "$nx <!-- $key --><a class='words' onclick='getWord4(\"$nx\");'><SA>$key</SA></a>";
   }
   if ($search_regexp_nonSanskrit != null) {
    if (preg_match("/$search_regexp_nonSanskrit/",$x,$matches)) {
     $extra = $matches[1];
     $xmlnew .= "  ($extra)<br/>\n";
    } else {
    $xmlnew .= "<br/>\n";
    }
   }else {
    $xmlnew .= "<br/>\n";
   }
  }
 }
 $xmlnew .= "</p>\n";
 return $xmlnew;
}

}
?>
