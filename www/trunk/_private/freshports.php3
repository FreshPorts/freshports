<?

// common things needs for all freshports php3 pages

function freshports_Category_Name($CategoryID, $db) {
   $sql = "select name from categories where id = $CategoryID";

//echo $sql;

   $result = mysql_query($sql, $db);

   $myrow = mysql_fetch_array($result);

//echo $myrow["name"];

   return $myrow["name"];
}


function freshports_MainWatchID($UserID, $db) {

   $sql = "select watch.id ".
          "from watch ".
          "where watch.owner_user_id = $UserID ".
          "and   watch.system        = 'FreeBSD' ".
          "and   watch.name          = 'main'";


   $result = mysql_query($sql, $db);

//   echo "freshports_MainWatchID sql = $sql<br>\n";

   if(mysql_numrows($result)) {
//      echo "results were found for that<br>\n";
      $myrow = mysql_fetch_array($result);
      $WatchID = $myrow["id"];
   }

   return $WatchID;
}

function freshports_echo_HTML($text) {
//   echo $text;
   return $text;
}

function freshports_echo_HTML_flush() {
   echo $HTML_Temp;
}

function freshports_in_array($value, $array) {
  $Count = count($array);
  for ($i = 0; $i < $Count; $i++) {
     if ($array[$i] == $value) {
         return 1;
     }
  }

  return 0;
}

function freshports_PortIDFromPortCategory($category, $port, $db) {
/*
   echo "category = $category<br>\n";
   echo "port     = $port<br>\n";
*/
   $sql = "select ports.id " . 
          "from ports, categories ".
          "where ports.primary_category_id = categories.id " .
          "  and ports.name                = '$port' " .
          "  and categories.name           = '$category'";

   $result = mysql_query($sql, $db);
   if(mysql_numrows($result)) {
      $myrow = mysql_fetch_array($result);
      $PortID = $myrow["id"];
   }

   return $PortID;
}

function freshports_CategoryIDFromCategory($category, $db) {
   $sql = "select categories.id from categories where categories.name = '$category'";

   $result = mysql_query($sql, $db);
   if(mysql_numrows($result)) {
      $myrow = mysql_fetch_array($result);
      $CategoryID = $myrow["id"];
   }
   
   return $CategoryID;
}

function freshports_SideBarHTML($Self, $URL, $Title) {
//   echo "PHP_SELF = $Self and URL=$URL";
   if ($Self == $URL || ($Self == '/index.php3' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . '">' . $Title . '</a>';
   }

   return $HTML;
}

function freshports_SideBarHTMLParm($Self, $URL, $Parm, $Title) {
//   echo "PHP_SELF = $Self and URL=$URL";
   if ($Self == $URL || ($Self == '/index.php3' && $URL == '/')) {
      $HTML = $Title;
   } else {
      $HTML = '<a href="' . $URL . $Parm . '">' . $Title . '</a>';
   }
      
   return $HTML;
}

?>
