<?php
  if($GLOBALS["message"]){
    echo "<p><div align=\"center\" id=\"message\">$GLOBALS[message]</div>\n";
    $GLOBALS["message"]="";
  }
?>
</body>
</html>