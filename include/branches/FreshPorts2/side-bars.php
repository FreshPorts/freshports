<?
	# $Id: side-bars.php,v 1.4.2.15 2002-04-18 21:33:53 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	$ColumnWidth = 155;

?>


  <table WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF"><BIG><B>Login</B></BIG></font></td>
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

GLOBAL $visitor;
GLOBAL $PHP_SELF;

//echo "OriginLocal = '$OriginLocal'<br>\n";
if ($visitor) {
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


   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/pkg_upload.php',       '',                     "watch list - upload") . '</font><br>';
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/watch-categories.php', '',                     "watch list - Categories") . '</font><br>';
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/watch.php',            '',                     "your watched ports"     ) . '</font><br>';
  } else {
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/login.php',            "?origin=$OriginLocal", "User Login"             ) . '</font><br>';
   echo '<font SIZE="-1">' . freshports_SideBarHTMLParm($PHP_SELF, '/new-user.php',         "?origin=$OriginLocal", "Create account"         ) . '</font><br>';
  }
?>
	<A HREF="/phorum/">Forums</A>
   </td>
   </tr>
   </table>

<BR>

<SCRIPT LANGUAGE="JavaScript">


   function addNetscapePanel() {
      if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function"))
      {
         window.sidebar.addPanel ("FreshPorts",
         "http://test.freshports.org/sidebar.php","");
      }
      else
      {
         var rv = window.confirm ("This page is enhanced for use with Netscape 6.  " + "Would you like to upgrade now?");
         if (rv)
            document.location.href = "http://home.netscape.com/download/index.html";
      }
   }
//-->

</SCRIPT>

<CENTER>
<a NAME="button_image"></a><a href="javascript:addNetscapePanel();"><img SRC="/images/sidebar-add-button.gif" BORDER=0 height=45 width=100>
</CENTER>

<BR>

<table WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
	<tr>
		<td bgcolor="#AD0040" height="30"><font color="#FFFFFF"><BIG><B>Search</B></BIG></font></td>
	</tr>
	<tr>

	<FORM action="/search.php" METHOD="post" NAME="f">
	<TD>
	Enter Keywords:<BR>
	<INPUT NAME="query"  TYPE="text" SIZE="8">&nbsp;<INPUT TYPE="submit" VALUE="go">
	<INPUT NAME="num"    TYPE="hidden" value="10">
	<INPUT NAME="stype"  TYPE="hidden" value="name">
	<INPUT NAME="method" TYPE="hidden" value="match">
	</TD>
	</FORM>
</TR>
</TABLE>

<br>

<table WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
	<tr>
		<td bgcolor="#AD0040" height="30"><font color="#FFFFFF"><BIG><B>Ports</B></BIG></font></td>
	</tr>
	<tr>
	<td valign="top">
<?
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/",                   "Home")            . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/categories.php",    "Categories")      . '</font><br>';
       echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/search.php",        "Search")          . '</font><br>';
?>
   </td>
   </tr>
   </table>
<br>
 <table WIDTH="<? echo $ColumnWidth ?>" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF"><BIG><B>This site</B></BIG></font></td>
        </tr>
        <tr>
    <td valign="top">
<?
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/about.php",          "What is freshports?") . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/authors.php",        "About the Authors")   . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/faq.php",            "FAQ")                 . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/stats/",             "Traffic")                 . '</font><br>';
        echo '<font SIZE="-1">' . freshports_SideBarHTML($PHP_SELF, "/privacy.php",        "Privacy")             . '</font><br>';
?>
    </td>
   </tr>
   </table>
