  <table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">Login</font></td>
        </tr>
        <tr>

         <td><script language="php">
   switch (basename($PHP_SELF)) {
//      case "watch.php":
//      case "watch-categories.php":
//      case "customize.php":
//      case "port-watch.php":
//         $OriginLocal = '/';
//         break;

      default:
         $OriginLocal = rawurlencode($HTTP_SERVER_VARS["REQUEST_URI"]);
         break;
   }

//echo "OriginLocal = '$OriginLocal'<br>\n";
if ($UserID) {
   echo '<font SIZE="-1">Logged in as ', $UserName, "</font><br>";

   if ($EmailBounceCount > 0) {
      echo '<img src="/images/warning.gif"><img src="/images/warning.gif"><img src="/images/warning.gif"><br>';
      echo '<font SIZE="-1">your email is <a href="bouncing.php?origin=' . $OriginLocal. '">bouncing</a></font><br>';
      echo '<img src="/images/warning.gif"><img src="/images/warning.gif"><img src="/images/warning.gif"><br>';
   }
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/customize.php',        "?origin=$OriginLocal", "Customize"              ) . '</font><br>';


   # for a logout, where we go depends on where we are now
   #
   switch ($PHP_SELF) {
      case "customize.php":
      case "watch-categories.php":
      case "watch.php":
         $args = "?origin=$OriginLocal";
         break;

      default:
         $args = '';
   }
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/logout.php',           $args,                  "Logout"                 ) . '</font><br>';


   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/watch-categories.php', '',                     "watch list - Categories") . '</font><br>';
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/watch.php',            '',                     "your watched ports"     ) . '</font><br>';
  } else {
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/login.php',            "?origin=$OriginLocal", "User Login"             ) . '</font><br>';
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/new-user.php',         "?origin=$OriginLocal", "Create account"         ) . '</font><br>';
  }
?>
   </td>
   </tr>
   </table>
<br>

<table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">Ports</font></td>
        </tr>
        <tr>
    <td valign="top">
<?
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/",                   "Home")            . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/ports-new.php",     "Brand new ports") . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/ports-deleted.php", "Deleted ports")   . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/categories.php",    "Categories")      . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/search.php",        "Search")          . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/stats.php",         "Port statistics") . '</font><br>';
?>
   </td>
   </tr>
   </table>
<br>
 <table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">This site</font></td>
        </tr>
        <tr>
    <td valign="top">
<?
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/about.php",          "What is freshports?") . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/authors.php",        "About the Authors")   . '</font><br>';
#        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/phorum/list.php?f=3", "Feedback Phorum")     . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/inthenews.php",      "In the news")         . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/changes.php",        "Changes")             . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/privacy.php",        "Privacy")             . '</font><br>';
?>
    </td>
   </tr>
   </table>
