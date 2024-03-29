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

<tr>
	<?php echo freshports_PageBannerText("Statistics - everyone loves a graph"); ?>
</tr>

<tr><td>
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

</td></tr>

<tr><td>

<table class="fullwidth borderless">
<tr>
<td class="graph-sidebar">
<?php
	$id = $_REQUEST["id"] ?? '';
	$id = intval($id);
	$sql = "select id, title, is_clickable from graphs order by title";
	$title = "graph goes here!";
	$result = pg_query($db, $sql);
	if ($result) {
		$numrows = pg_num_rows($result);
		if ($numrows) { 
			echo '<UL>';
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				echo '<li><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $myrow["id"] . '">' . $myrow["title"] . '</a></li>' . "\n";
				if ($myrow["id"] == $id) {
					$title = htmlentities($myrow["title"]);
					$is_clickable = $myrow["is_clickable"];
				}
#				echo $myrow["id"] .  ' '  . $myrow["is_clickable"] . '<br>';
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
</td>
<td>
<?php
	# $is_clickable will get set if graph == $id is found
	if ($id && IsSet($is_clickable)) {
		if ($is_clickable == "t" ) {
			?>
			<FORM ACTION="/graphs/graphclick.php" METHOD="get">
			<INPUT TYPE="hidden" NAME="id"    VALUE="<?php echo $id; ?>">
			<INPUT NAME="graph"  TYPE="image" SRC="/graphs/graph.php?id=<?php echo $id; ?>" title="<?php echo $title; ?>" alt="<?php echo $title; ?>">
			</FORM>
			<?php
		} else {
			?>
			<IMG SRC="/graphs/graph.php?id=<?php echo htmlentities($id); ?>" title="<?php echo $title; ?>" alt="<?php echo $title; ?>">
			<?php
		}
	} else {
		echo "Oh. This is rather embarassing.  I have no idea how this could have happened. ";
		echo "I do hope you will understand.  Please don't tell anyone.  But I don't have any ";
		echo "data to show you.  For you see, I know nothing about that graph id.";
	}
?>
</td>
</tr>
</table>


</td></tr>

</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
