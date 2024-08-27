<?php

require_once('../classes/vuxml_ranges.php');
        
$ranges = new VuXML_Ranges(null);

echo $ranges->TextToMath('le') .  ' should be <=' . "\n";
echo $ranges->TextToMath('lt') . '  should be <'  . "\n";
echo $ranges->TextToMath('gt') . '  should be >'  . "\n";
echo $ranges->TextToMath('ge') .  ' should be >=' . "\n";
echo $ranges->TextToMath('eq') . '  should be ='  . "\n";
