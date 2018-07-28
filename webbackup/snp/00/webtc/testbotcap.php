<?php
$xin = <<<EOT
<H1><h><key1>agaru</key1><key2>agaru</key2></h><body><i>agaru</i> (sometimes considered to be a synonym of <i>aguru</i>) <P/>(1) a) Chopra: [<c>COMMIPHORA ROXBURGHII (ARN.) ENGL.</c>] = [<c>C. <lb/>AGALLOCHA (WIGHT ET ARN.) ENGL.</c>] = <c>BALSAMODENDRUM <lb/>ROXBURGHII ARN.</c> = <c>AMYRIS COMMIPHORA ROXB.</c>; <P/>b) KB 1, p. 528-529: <c>COMMIPHORA AGALLOCHA ENGL.</c> = <c>BALSA- <lb/>MODENDRUM ROXBURGHIII ARN.</c> = <c>AMYRIS COMMIPHORA <lb/>ROXB.</c>; the Index Kewensis disagress with Chopra and KB <lb/>and distinguishes: <P/>a) <c>COMMIPHORA MUKUL ENGL.</c> = <c>C. ROXBURGHII ENGL.</c> = <c>BAL- <lb/>SAMODENDRUM ROXBURGHII STOCKS</c> = <c>B. WIGHTII ARN.</c>; <lb/>b) <c>COMMIPHORA ROXBURGHII ALSTON</c> = <c>C. AGALLOCHA ENGL.</c> = <lb/><c>BALSAMODENDRUM AGALLOCHA WIGHT ET ARN.</c> = <c>B. COMMI</c>- [Page-522+ 39] <lb/><c>PHORA ROYLE</c> = <c>B. ROXBURGHII ARN.</c> = <c>AMYRIS COMMI- <lb/>PHORA ROXB.</c>; the confusion in the nomenclature is due to the <lb/>fact that Chopra identifies <c>C. ROXBURGHII ENGL.</c> with <c>C. <lb/>AGALLOCHA ENGL.</c>, distinguished as two species in the Index <lb/>Kewensis, and that he considers <c>C. MUKUL ENGL.</c> as a separate <lb/>species, synonymous with <c>B. MUKUL HOOK. EX STOCKS</c>, while <lb/><c>C. MUKUL ENGL.</c> is a synonym of <c>C. ROXBURGHII ENGL.</c> in the <lb/>Index Kewensis; Hooker describes (1, p. 529): a) <c>BALSAMO- <lb/>DENDRON MUKUL HOOK. EX STOCKS</c> = <c>B. ROXBURGHII STOCKS</c> <lb/>= (?) <c>B. WIGHTII ARN.</c>, and b) <c>B. ROXBURGHII ARN.</c> = <c>AMY- <lb/>RIS COMMIPHORA ROXB.</c> = (?) <c>AMYRIS AGALLOCHA ROXB.</c>; <lb/>Cooke describes (1, p. 212-213) <c>COMMIPHORA MUKUL ENGL.</c> = <lb/><c>BALSAMODENDRON MUKUL HOOK. EX STOCKS</c> = <c>B. ROX- <lb/>BURGHII STOCKS</c> (<c>NON ARNOTT</c>); Duthie (1, p. 139-140) agrees <lb/>with Cooke; <P/>(2) <c>AQUILARIA AGALLOCHA ROXB.</c> (Chopra; IRM 1, p. 89-90; KB 3, <lb/>p. 2171-2172; Nadk. 1, nr. 208; Watt CP, p. 72-74); <P/>(3) <c>AMYRIS AGALLOCHA ROXB.</c> (MW; PW); acc. to HK this is a <lb/>synonym of <c>AQUILARIA AGALLOCHA ROXB.</c>, but the Index Kewensis <lb/>regards it as a separate species; <P/>(4) <c>SAPIUM SEBIFERUM ROXB.</c> (KB 3, p. 2284); <P/>(5) <c>EXCOECARIA AGALLOCHA LINN.</c> (Chopra; KB 3, p. 2285-2286); <P/>(6) <c>DYSOXYLUM MALABARICUM BEDD. EX C. DC.</c> (Avk; Nadk. 1, nr. <lb/>902).</body><tail><L>2</L><pc>521</pc></tail></H1>
EOT;

#$xin = "<c>AMYRIS AGALLOCHA ROXB.</c>";
$line = $xin;
$line = preg_replace_callback('|<c>(.*?)</c>|',"botcap_callback",$line);
echo "$line\n";

function botcap_callback($matches) {
/* Try to mimic capitalization of botanical names.
   First, lower case everything. Then split into words
   and capitalize all words that aren't the second word.
   Also, don't capitalize some 'exceptions', currently 'et'
   This does not currently adequately handle line breaks.
*/
 $x = $matches[1]; // contents of this <c>x</c>
 $x = strtolower($x);
 $parts = preg_split('/(\s)|(<.*?>)|([(])/',$x,NULL,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
 $yarr=array();
 $nword=0;
 for ($i=0;$i<count($parts);$i++) {
  $part = $parts[$i];
  #echo "parts[$i]=$part\n";
  if (preg_match('|^[a-z]|',$part)) {
   $nword = $nword + 1;
   if ($part == 'et') { // latin 'and'
    $yarr[]=$part;
   }else if($nword != 2) {
    $yarr[] = ucfirst($part);
   }else {
    $yarr[]=$part;
   }
  }else {
   $yarr[]=$part;
  }
 }
 $y = join('',$yarr);
 return "<c>$y</c>";
}

?>
