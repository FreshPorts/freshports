<?
	# $Id: graphs.php,v 1.5.2.7 2002-04-23 17:09:41 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");
	require("./include/getvalues.php");

	freshports_Start("Statistics - everyone loves a graph",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD VALIGN=TOP WIDTH="100%">
<TABLE WIDTH="100%" BORDER="0">
<TR>
	<? freshports_PageBannerText("Statistics - everyone loves a graph"); ?>
</TR>

<TR><TD>
<P>
All graphs are at most 4 hours old.  The data used in these graphs are compiled by a large team of 
trained worms.  As such, they are liable to be filled with errors and riddled with castings.  You
are advised not to make life decisions based on this information.
</P>
<P>
If you have suggestions for graphs, please submit them via the forum.
</P>

<HR>
</TD></TR>

<TR><TD>

<TABLE WIDTH="100%" BORDER="0">
<TR>
<TD WIDTH="300" VALIGN="top">
<?
	$sql = "select id, title, is_clickable from graphs order by title";
	$result = pg_exec($db, $sql);
    if ($result) {
    	$numrows = pg_numrows($result);
		if ($numrows) { 
			echo '<UL>';
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				echo '<LI><A HREF="' . $PHP_SELF . '?id=' . $myrow["id"] . '">' . $myrow["title"] . '</A></LI>';
				if ($myrow["id"] == $id) {
					$is_clickable = $myrow["is_clickable"];
				}
#				echo $myrow["id"] .  ' '  . $myrow["is_clickable"] . '<BR>';
			}
			echo '</UL>';
		} else {
			echo "Oh. This is rather embarassing.  I have no idea how this could have happened. ";
			echo "I do hope you will understand.  Please don't tell anyone.  But I don't have any ";
			echo "data to show you.  For you see, nobody has bothered to populate the graphs table.";
		}
	} else {
	}
?>
</TD>
<TD>
<?
	if ($id) {
		if ($is_clickable == "t" ) {
			?>
			<FORM ACTION="/graphs/graphclick.php" METHOD="get">
			<INPUT TYPE="hidden" NAME="id"    VALUE="<? echo $id; ?>">
			<INPUT NAME="graph"  TYPE="image" SRC="/graphs/graph.php?id=<? echo $id; ?>">
			</FORM>
			<?
		} else {
			?>
			<IMG SRC="/graphs/graph.php?id=<? echo $id; ?>">
			<?
		}
	}
?>
</TD>
</TR>
</TABLE>


</TD</TR>

</TABLE>
</TD>
  <TD valign="top">
    <?
       include("./include/side-bars.php");
    ?>
 </TD>
</TR>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

</body>
</html>
