<?
//
// This php3 fragment does the basic port information for a single port.
// I'd show you what code is expected in $myrow, but I can't be bothered
// right now
//
   $HTML .= "<dl>";

   $HTML .= "<b>" . $myrow["port"];
   if (strlen($myrow["version"]) > 0) {
      $HTML .= '-' . $myrow["version"];
   }

   $HTML .= "</b>";

   // indicate if this port needs refreshing from CVS
   if ($myrow["needs_refresh"] == "Y") {
      $HTML .= ' <font size="-1">[refresh]</font>';
   }

   if (!$HideCategory) {
      $URL_Category = "category.php3?category=" . $myrow["category_id"];
      $HTML .= ' <font size="-1"><a href="' . $URL_Category . '">' . $myrow["category"] . '</a></font>';
   }

   $HTML .= "<dd>";

   // description
   $HTML .= $myrow["short_description"] . "<br>\n";

   // maintainer
   $HTML .= 'Maintained by: <a href="mailto:' . $myrow["maintainer"];
   $HTML .= '?cc=ports@FreeBSD.org&amp;subject=FreeBSD%20Port:%20' . $myrow["port"] . "-" . $myrow["version"] . '">';
   $HTML .= $myrow["maintainer"] . '</a></br>' . "\n";

   if ($myrow["committer"]) {
      $HTML .= 'last change committed by ' . $myrow["committer"];  // separate lines in case committer is null

      $HTML .= ' on <font size="-1">' . $myrow["updated"] . '</font><br>' . "\n";

      $HTML .= $myrow["update_description"] . "<br>" . "\n";
   } else {
      $HTML .= "no changes recorded in FreshPorts<br>\n";
   }

   if (!$HideDescription) {
      // Long descripion
      $HTML .= '<a HREF="port-description.php3?port=' . $myrow["id"] .'">Description</a>';

      $HTML .= ' <b>:</b> ';
   }

   // changes
   $HTML .= '<a HREF="http://www.FreeBSD.org/cgi/cvsweb.cgi/ports/' .
            $myrow["category"] . '/' .  $myrow["port"] . '">Changes</a>';
   $HTML .= ' <b>:</b> ';

   // download
   $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/branches/-current/ports/' .
            $myrow["category"] . '/' .  $myrow["port"] . '.tar">Download Port</a>';

   if ($myrow["package_exists"] == "Y") {
      // package
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="ftp://ftp.FreeBSD.org/pub/FreeBSD/FreeBSD-stable/packages/' .
               $myrow["category"] . '/' .  $myrow["port"] . "-" . $myrow["version"] . '.tgz">Package</a>';
   }

   if ($myrow["homepage"]) {
      $HTML .= ' <b>:</b> ';
      $HTML .= '<a HREF="' . $myrow["homepage"] . '">Homepage</a>';
   }

   $HTML .= "<p></p></dd>";
   $HTML .= "</dl>" . "\n";
?>
