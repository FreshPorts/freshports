<?
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

//if ($ShowCategories) {

   if ($myrow["categories"]) {
//      $HTML .= "Also listed in: ";

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
//}

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
      $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' .
               $myrow["category"] . '/' .  $myrow["port"] . '.tar">Download Port</a>';
   }

   if ($myrow["package_exists"] == "Y" && ($ShowPackageLink == "Y" || $ShowEverything)) {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a>';
   }

   if ($myrow["homepage"] && ($ShowHomepageLink == "Y" || $ShowEverything)) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . $myrow["homepage"] . '">Homepage</a>';
   }

   $HTML .= "<p></p></dd>";
   $HTML .= "</dl>" . "\n";
?>
