<?

// path to the CVS repository
$freshports_CVS_URL = "http://www.FreeBSD.org/cgi/cvsweb.cgi/ports/";


// common things needs for all freshports php3 pages

function freshports_Start($ArticleTitle, $Description, $Keywords) {
}


function freshports_Category_Name($CategoryID, $db) {
   $sql = "select name from categories where id = $CategoryID";

//echo $sql;

   $result = mysql_query($sql, $db);
   if (!$result) {
      echo "error " . mysql_error();
      exit;
   }

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

function freshports_YNToCheckbox($Value) {
// this function takes a Y/N value and converts it to
// HTML suitable for a checkbox.
   $HTML = 'value="ON"';
   if ($Value == "Y") {
      $HTML .= " checked";
   }

   return $HTML;
}

function freshports_ONToYN($Value) {
   if ($Value == "ON") {
      $YN = "Y";
   } else {
      $YN = "N";
   }

   return $YN;
}


function freshports_PortDetails($myrow, $ShowDeletedDate, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink) {
//
// This php3 fragment does the basic port information for a single port.
// I'd show you what code is expected in $myrow, but I can't be bothered
// right now
//
   $MarkedAsNew = "N";
   $HTML .= "<dl>";

   $HTML .= "<b>" . $myrow["port"];
   if (strlen($myrow["version"]) > 0) {
      $HTML .= ' ' . $myrow["version"];
   }

   $HTML .= "</b>";

   // indicate if this port needs refreshing from CVS
   if ($myrow["status"] == "D") {
      $HTML .= ' <font size="-1">[deleted - port removed from ports tree]';
      if ($ShowDeletedDate == "Y") {
         $HTML .= ' on ' . $myrow["updated"];
      }
      $HTML .= '</font>';
   }
   if ($myrow["needs_refresh"]) {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }

   if (!$HideCategory) {
      $URL_Category = "category.php3?category=" . $myrow["category_id"];
      $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';
   }

//   $HTML .= $myrow["date_created_formatted"] ."::". $myrow["updated"] ."--". $DaysMarkedAsNew;
   if ($myrow["date_created"] > Time() - 3600 * 24 * $DaysMarkedAsNew) {
      $MarkedAsNew = "Y";
      $HTML .= " <img src=\"/images/new.gif\" width=28 height=11 alt=\"new!\" hspace=2 align=absmiddle>";
   }

   if ($MarkedAsNew == "Y" || $ShowPortCreationDate) {
      if ($myrow["date_created_formatted"] != $myrow["updated"] || !($ShowLastChange == "Y" || $ShowEverything) || $ShowPortCreationDate) {
         $HTML .= ' <font size="-1">(' . $myrow["date_created_formatted"] . ")</font>";
      }
   }

   $HTML .= "<dd>";
   # show forbidden and broken
   if ($myrow["forbidden"]) {
      $HTML .= '<img src="images/forbidden.gif" alt="Forbidden" width="20" height="20" hspace="2">' . $myrow["forbidden"] . "<br><br>";

   }
   if ($myrow["broken"]) {
      $HTML .= '<img src="images/broken.gif" alt="Broken" width="17" height="16" hspace="2">' . $myrow["broken"] . "<br><br>"; ;
   }

   // description
   if ($myrow["short_description"] && ($ShowShortDescription == "Y" || $ShowEverything)) {
      $HTML .= $myrow["short_description"];
      $HTML .= "<br>\n";
   }

   // maintainer
   if ($myrow["maintainer"] && ($ShowMaintainedBy == "Y" || $ShowEverything)) {
      $HTML .= '<i>';
      if ($myrow["status"] == 'A') {
         $HTML .= 'Maintained';
      } else {
         $HTML .= 'was maintained'; 
      }
      $HTML .= ' by:</i> <a href="mailto:' . $myrow["maintainer"];
      $HTML .= '?cc=ports@FreeBSD.org&amp;subject=FreeBSD%20Port:%20' . $myrow["port"] . "-" . $myrow["version"] . '">';
      $HTML .= $myrow["maintainer"] . '</a></br>' . "\n";
  }

   // there are only a few places we want to show the last change.
   // such places set $GlobalHideLastChange == "Y"
   if ($GlobalHideLastChange != "Y") {
      if ($ShowLastChange == "Y" || $ShowEverything) {
         if ($myrow["last_change_log_id"] != 0) {
            $HTML .= 'last change committed by ' . $myrow["committer"];  // separate lines in case committer is null
 
            $HTML .= ' on <font size="-1">' . $myrow["updated"] . '</font><br>' . "\n";
 
            $HTML .= $myrow["update_description"] . "<br>" . "\n";
         } else {
            $HTML .= "no changes recorded in FreshPorts<br>\n";
         }
      }
   }

   if ($myrow["categories"]) {
      // remove the primary category
      $Categories = str_replace($myrow["category"], '', $myrow["categories"]);
      $Categories = str_replace('  ', ' ', $Categories);
      if ($Categories) {
         $HTML .= "<i>Also listed in:</i> ";
         $CategoriesArray = explode(" ", $Categories);
         $Count = count($CategoriesArray);
         for ($i = 0; $i < $Count; $i++) {
            $Category = $CategoriesArray[$i];
            $CategoryID = freshports_CategoryIDFromCategory($Category, $db);
            if ($CategoryID) {
               // this is a real category
               $HTML .= '<a href="category.php3?category=' . $CategoryID . '">' . $Category . '</a>';
            } else {
               $HTML .= $Category;
            }
            if ($i < $Count - 1) {
               $HTML .= " ";
            }
         }
      $HTML .= "<br>\n";
      }
   }

/*
echo 'build = ' . $myrow["depends_build"] . "<br>\n";
echo 'run   = ' . $myrow["depends_run"] . "<br>\n";
*/

if ($ShowDepends) {
   if ($myrow["depends_build"]) {
      $HTML .= "<i>required to build:</i> ";

      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $myrow["depends_build"]);
      
      // split each depends up into different bits
      $depends = explode(' ', $temp);
      $Count = count($depends);
      for ($i = 0; $i < $Count; $i++) {
          // split one depends into the library and the port name (/usr/ports/<category>/<port>)

          $DependsArray = explode(':', $depends[$i]);

          // now extract the port and category from this port name
          $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

          $HTML .= '<a href="port-description.php3?port=' . $DependsPortID . '">' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<br>\n";
   }

   if ($myrow["depends_run"]) {
      $HTML .= "<i>required to run:</i> ";
      // sometimes they have multiple spaces in the data...
      $temp = str_replace('  ', ' ', $myrow["depends_run"]);

      // split each depends up into different bits
      $depends = explode(' ', $temp);
      $Count = count($depends);
      for ($i = 0; $i < $Count; $i++) {
          // split one depends into the library and the port name (/usr/ports/<category>/<port>)

          $DependsArray = explode(':', $depends[$i]);

          // now extract the port and category from this port name
          $CategoryPort = str_replace('/usr/ports/', '', $DependsArray[1]) ;
          $CategoryPortArray = explode('/', $CategoryPort);
          $DependsPortID = freshports_PortIDFromPortCategory($CategoryPortArray[0], $CategoryPortArray[1], $db);

          $HTML .= '<a href="port-description.php3?port=' . $DependsPortID . '">' . $CategoryPortArray[1]. '</a>';
          if ($i < $Count - 1) {
             $HTML .= ", ";
          }
      }
      $HTML .= "<br>\n";
   }

}

   if (!$HideDescription && ($ShowDescriptionLink == "Y" || $ShowEverything)) {
      // Long descripion
      $HTML .= '<a HREF="port-description.php3?port=' . $myrow["id"] .'">Description</a>';

      $HTML .= ' <b>:</b> ';
   }

   if ($ShowChangesLink == "Y" || $ShowEverything) {
      // changes
      $HTML .= '<a HREF="' . $freshports_CVS_URL .
               $myrow["category"] . '/' .  $myrow["port"] . '">Changes</a>';
   }

   // download
   if ($myrow["status"] == "A" && ($ShowDownloadPortLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp5.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' .
               $myrow["category"] . '/' .  $myrow["port"] . '">Download Port</a>';
   }

   if ($myrow["package_exists"] == "Y" && ($ShowPackageLink == "Y" || $ShowEverything)) {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp5.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a>';
   }

   if ($myrow["homepage"] && ($ShowHomepageLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . $myrow["homepage"] . '">Homepage</a>';
   }

   $HTML .= "<p></p></dd>";
   $HTML .= "</dl>" . "\n";

   return $HTML;
}

?>
