<?php
	#
	# $Id: graphs.php,v 1.7 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$Title = 'Statistics - everyone loves a graph';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');
?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

<TR>
	<? echo freshports_PageBannerText("Statistics - everyone loves a graph"); ?>
</TR>

<TR><TD>
<P>
All graphs are at most 4 hours old.  The data used in these graphs are compiled by a large team of 
trained worms.  As such, they are liable to be filled with errors and riddled with castings.  You
are advised not to make life decisions based on this information.
</P>
<P>
If you have suggestions for graphs, please raise an issue.
</P>

<h3>NOTE that many graphs are clickable and will take you to the category, port, etc.</h3>

<HR>

<?php
  if ($ShowAds) echo '<CENTER>' . Ad_728x90() . '</CENTER>';
?>

</TD></TR>

<TR><TD>

<TABLE class="fullwidth borderless">
<TR>
<TD class="graph-sidebar">
<?
	$id = $_REQUEST["id"] ?? '';
	$sql = "select id, title, is_clickable from graphs order by title";
	$title = "graph goes here!";
	$result = pg_query($db, $sql);
	if ($result) {
		$numrows = pg_num_rows($result);
		if ($numrows) { 
			echo '<UL>';
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				echo '<LI><A HREF="' . $_SERVER["PHP_SELF"] . '?id=' . $myrow["id"] . '">' . $myrow["title"] . '</A></LI>' . "\n";
				if ($myrow["id"] == $id) {
					$title = htmlentities($myrow["title"]);
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
		echo '<p>There was unfortunately an error while fetching the list of graphs from the database.</p>';
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
			<INPUT NAME="graph"  TYPE="image" SRC="/graphs/graph.php?id=<? echo $id; ?>" TITLE="<? echo $title; ?>" ALT="<? echo $title; ?>">
			</FORM>
			<?
		} else {
			?>
			<IMG SRC="/graphs/graph.php?id=<? echo htmlentities($id); ?>" TITLE="<? echo $title; ?>" ALT="<? echo $title; ?>">
			<?
		}
	}
?>
</TD>
</TR>
</TABLE>


</TD></TR>

</TABLE>
</td>

  <td class="sidebar">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TR>
</TABLE>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
