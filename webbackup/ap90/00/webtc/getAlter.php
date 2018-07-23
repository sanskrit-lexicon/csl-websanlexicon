<?php
function getAlter($data) {
 if (!preg_match('|<key1>(.*?)</key1>.*?<body *ref="(.*?)"|',$data,$m)) {
  // Not applicable. Return $data unchanged
  return $data;
 }
 $key = $m[1];  // Key of the child
 $L0 = $m[2];  // Parent L-number
 // Try to get the parent data
 $results = dal_ap902($L0,$L0);
 if (count($results) == 1) {
  $line = $results[0];
  list($key0,$lnum0,$data0) = $line;
  $data1 = getAlter1($data,$data0);
  return $data1;
 }
 // Problem getting parent.
 $newbody="<body><b>$L0 not found</b></body>";
 // Replace body of data with newbody
 $data1 = preg_replace('|<body.*?</body>|',$newbody,$data);
 return $data1;
}
function getAlter1($data,$data0) {
 // $data is (possibly) a child of $data0
 // In this case, we alter the <body> of $data using the body of $data0
 // Otherwise, just return $data
 // First, is $data a child (of something)
 if (!preg_match('|<key1>(.*?)</key1>.*?<body *ref="(.*?)"|',$data,$m)) {
  // Not applicable. Return $data unchanged
  return $data;
 }
 $key = $m[1];  // Key of the child
 $L0 = $m[2];  // Parent L-number
 if (! preg_match('|<key1>(.*?)</key1>.*?<body>(.*?)</body>.*?<L.*?>(.*?)</L>|',$data0,$m0)) {
  return $data;
 }
 $key0 = $m0[1];
 $body0 = $m0[2];
 $Lnum0 = $m0[3]; // should equal $L0
 if ($Lnum0 != $L0) {
  echo "WARNING: $key,$Lnum0,$L0\n";
  return $data;
 }
 $newbody = "<body>(<s>$key</s> is alternate of <s>$key0</s>)<br/> " .
               $body0 . "</body>";
 $data1 = preg_replace('|<body.*?</body>|',$newbody,$data);
 return $data1;
}

?>
