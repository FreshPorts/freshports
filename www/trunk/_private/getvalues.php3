<script language="php">

$Debug=0;

$FormatDateDefault	= "%W, %b %e";
$FormatTimeDefault	= "%H:%i";
$DaysMarkedAsNewDefault	= 10;


// there are only a few places we want to show the last change.
// such places set $GlobalHideLastChange == "Y"   
$GlobalHideLastChange   = "Y";

$DaysToShow  = 20;
$MaxArticles = 40;
$DaysNew     = 10;

$MaxNumberOfPorts	= 20;
$ShowShortDescription	= "Y";
$ShowMaintainedBy	= "Y";
$ShowLastChange		= "Y";
$ShowDescriptionLink	= "Y";
$ShowChangesLink	= "Y";
$ShowDownloadPortLink	= "Y";
$ShowPackageLink	= "Y";
$ShowHomepageLink	= "Y";
$FormatDate		= $FormatDateDefault;
$FormatTime		= $FormatTimeDefault;
$DaysMarkedAsNew	= $DaysMarkedAsNewDefault;
$EmailBounceCount	= 0;

$UserName		= "";
$UserID			= "";

// This is used to determine whether or not the cach can be used.
$DefaultMaxArticles = $MaxArticles;

if (!empty($visitor)) {
   $sql = "select * from users ".
         "where cookie = '$visitor'";

   if ($Debug) {
      echo "sql=$sql<br>\n";
   }

   $result = mysql_query($sql, $db) or die("getvalues query failed " . mysql_error());

   if ($result) {
      $myrow = mysql_fetch_array($result);
      if ($myrow) {
         $UserName		= $myrow["username"];
         $UserID		= $myrow["id"];
         $emailsitenotices_yn	= $myrow["emailsitenotices_yn"];
         $email			= $myrow["email"];
         $watchnotifyfrequency	= $myrow["watchnotifyfrequency"];

         $MaxNumberOfPorts	= $myrow["max_number_of_ports"];
         $ShowShortDescription	= $myrow["show_short_description"];
         $ShowMaintainedBy	= $myrow["show_maintained_by"];
         $ShowLastChange	= $myrow["show_last_change"];
         $ShowDescriptionLink	= $myrow["show_description_link"];
         $ShowChangesLink	= $myrow["show_changes_link"];
         $ShowDownloadPortLink	= $myrow["show_download_port_link"];
         $ShowPackageLink	= $myrow["show_package_link"];
         $ShowHomepageLink	= $myrow["show_homepage_link"];

/*
         if ($myrow["days_marked_as_new"]) {
            $DaysMarkedAsNew	= $myrow["days_marked_as_new"];
         } else {
            $DaysMarkedAsNew	= $DaysMarkedAsNewDefault;
         }
*/

/*
         if ($myrow["format_date"]) {
            $FormatDate		= $myrow["format_date"];
         }

         if ($myrow["format_time"]) {
            $FormatTime		= $myrow["format_time"];
         }
*/
         if ($emailsitenotices_yn == "Y") {
            $emailsitenotices_yn = "ON";
         } else {
            $emailsitenotices_yn = "";
         }
/*
         $SampleFormatDate	= $myrow["sample_date"];
         $SampleFormatTime	= $myrow["sample_time"];
*/

         $EmailBounceCount	= $myrow["emailbouncecount"];
 
//        echo "visitor = $visitor<br>";

         // record their last login
         $sql = "update users set lastlogin = '" . date("Y/m/d", time()) . "'" .
                " where id = $UserID";
//        echo $sql, "<br>";
         $result = mysql_query($sql, $db);
      } else {
         $errors = "Sorry, but that login doesn't exist according to me.";
      }
   }
   if ($Debug) {
      echo "UserName = $UserName\nUserID=$UserID\n";
   }
}
</script>
