<?
	# $Id: missing-port.php,v 1.1.2.2 2001-12-29 20:37:23 dan Exp $
	#
	# Copyright (c) 2001 DVL Software Limited


function freshports_Parse404CategoryPort($REQUEST_URI, $db) {

#	echo "you asked for $REQUEST_URI<BR>";

	$result = 0;
	$url_Array = explode('/', $REQUEST_URI);
	if (array_count_values($url_Array) >= 1) {
		$CategoryName = $url_Array[1];
		if (array_count_values($url_Array) >= 2) {
			$PortName = $url_Array[2];
		}

#		echo "\$CategoryName = '$CategoryName'<BR>";
#		echo "\$PortName     = '$PortName'<BR>";


		$CategoryID = freshports_CategoryId($CategoryName, $db);

#		echo "\$CategoryName = '$CategoryName' ($CategoryID)<BR>";
#		echo "\$PortName     = '$PortName'<BR>";

		if (IsSet($PortName)) {
			$element = new Element($db);
			$element->FetchByName("/ports/$CategoryName/$PortName");

			if (IsSet($element->id)) {
				$port = new Port($db);
				$port->FetchByPartialName("/ports/$CategoryName/$PortName");

			}
		}


		if (IsSet($CategoryID)) {
#			echo "<A HREF=\"/category.php3?category=$CategoryID\">this link</A> should take you to the category details<BR>";
			if (IsSet($port->id)) {
#				echo "This is where you'd see details for port = '$port->id'<BR>";
#				echo "<A HREF=\"/port-description.php3?port=$port->id\">this link</A> should take you to the port details<BR>";
#				echo "and short_description = $port->short_description";

				freshports_PortDescription($port);
				$result = 1;

			} else {
#				if (IsSet($PortName)) {
#					echo "no port found like that in this category";
#				}

				require("missing-category.php");
				freshports_Category($CategoryID, $db);
			}
		} else {
			echo "no category '$CategoryName' found";
		}
	}

	return $result;
}


function freshports_PortDescription($port) {
	header("HTTP/1.1 200 OK");
	$Title = $port->category . "/" . $port->port;
	freshports_Start($Title,
	        		"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>

<TABLE WIDTH="100%" BORDER="0">
<tr>
  <td>
<p>This page contains the description of a single port.</p>

<p>I've just added <i>Also listed in</i>.  Some ports appear in more than one category.  
If there is no link to a category, that is because that category
is a virtual category, and I haven't catered for those yet. But <a href="changes.php3">I plan to</a></p>
<p>
<img src="/images/new.gif"  alt="new feature" border="0" width="28" height="11" hspace="2">Click on 
<img src="/images/logs.gif" alt="Files within this port affected by this commit" border="0" WIDTH="17" HEIGHT="20" hspace="2"> 
to see what files changed for this port in that commit.</p>
</td>
</tr>
<tr><td valign="top" width="100%">
<TABLE WIDTH="100%" BORDER="0">
<tr>
    <td colspan="3" bgcolor="#AD0040" height="29"><font color="#FFFFFF" size="+2">freshports - 
<?
   echo $Title;
?> 
 </font></td>
</tr>
<tr><td colspan="3" valign="top" width="100%">

<?
	GLOBAL $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription;


	$ShowCategories		= 1;
GLOBAL	$ShowDepends;
$ShowDepends		= 1;
$DaysMarkedAsNew= $DaysMarkedAsNew= $GlobalHideLastChange= $ShowChangesLink= $ShowDescriptionLink= $ShowDownloadPortLink= $ShowHomepageLink= $ShowLastChange= $ShowMaintainedBy= $ShowPortCreationDate= $ShowPackageLink= $ShowShortDescription =1;
$HideDescription = 1;
$ShowEverything  = 1;
$ShowShortDescription = "Y";
$ShowMaintainedBy     = "Y";
$GlobalHideLastChange = "Y";
$ShowDescriptionLink  = "N";

	$HTML .= freshports_PortDetails($port, $port->dbh, $DaysMarkedAsNew, $DaysMarkedAsNew, $GlobalHideLastChange, $HideCategory, $HideDescription, $ShowChangesLink, $ShowDescriptionLink, $ShowDownloadPortLink, $ShowEverything, $ShowHomepageLink, $ShowLastChange, $ShowMaintainedBy, $ShowPortCreationDate, $ShowPackageLink, $ShowShortDescription);
	echo $HTML;

   echo '<DL><DD>';
   echo '<PRE>' . convertAllLinks(htmlspecialchars($port->long_description)) . '</PRE>';
   echo "\n</DD>\n</DL>\n</TD>\n</TR>";


#	echo 'about to call freshports_PortCommits #############################';

	freshports_PortCommits($port);

?>

</TABLE>
</TD>

</TD>
</TR>
</TABLE>
<?

}

function freshports_CategoryId($category, $database) {
	#
	# we could improve efficiency here with a cache
	# if we had need...
	#
	$sql = "select * from categories where name = '$category'";
	$result = pg_exec($database, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows == 1) {
			$myrow = pg_fetch_array ($result, 0);
			$CategoryID = $myrow["id"];
		}
	} else {
		echo 'pg_exec failed: ' . $sql;
	}

	return $CategoryID;
}



?>