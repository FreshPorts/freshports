<?php
	#
	# $Id: graphs2.php,v 1.2 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start('Statistics - everyone loves a graph',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');
?>
	<script src="/javascript/jquery-3.5.0.min.js"></script>
	<script src="/javascript/jquery.flot.js"></script>
	<script src="/javascript/graphs.js"></script>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>

<TR>
	<? echo freshports_PageBannerText("Statistics - everyone loves a graph"); ?>
</TR>

<TR><TD>
<P>
All of these graphs require javascript.  Please select the graph you would like to view from the dropdown.
</P>
<P>
If you have suggestions for graphs, please submit them via the <a href="<?php echo ISSUES; ?>">issues link</a>.
</P>


<center>
<?php
  if ($ShowAds) echo Ad_728x90();
?>
</center>

</TD></TR>

<tr><td>
<h2>HEADS UP!</h2>

<p>
These graphs are broken. Help is needed to get them working again.
<p>

Some starting points:

<ul>
<li><a href="https://github.com/FreshPorts/freshports/blob/master/www/graphs2.php">This is the source code</a> for this page.</li>
<li><a href="https://github.com/FreshPorts/freshports/blob/1.40/www/graphs2.php">This is the last working version</a> of this page.</li>
<li><a href="https://github.com/FreshPorts/freshports/blob/1.40/www/jquery-1.2.6.min.js">jquery-1.2.6.min.js</a> used by the above.</li>
<li><a href="https://github.com/FreshPorts/freshports/blob/1.40/www/jquery.flot.pack.js">jquery.flot.pack.js</a> used by the above.</li>
<li><a href="https://github.com/FreshPorts/freshports/blob/master/www/javascript/graphs.js">graphs.js</a> as relocated to /javascript/</li>
<li><a href="https://github.com/FreshPorts/freshports/tree/master/www/javascript">the javascript</a> I thought would appropriately replace jquery.flot.pack.js & jquery-1.2.6.min.js</li>
</ul>

Thank you for your help.


<p>
</td></tr>

<TR><TD>

<TABLE WIDTH="100%" BORDER="0">
<TR align="center">
<TD WIDTH="300" VALIGN="top">
<?php
	$sql = "select title, label from graphs where json=true order by title";
	$result = pg_exec($db, $sql);
    if ($result) {
    	$numrows = pg_numrows($result);
		if ($numrows) { 
			echo '<select>';
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				echo '<option value="' . $myrow["label"] . '">' . $myrow["title"] . '</option>' . "\n";
			}
			echo '</select>';
		} else {
			echo "Oh. This is rather embarassing.  I have no idea how this could have happened. ";
			echo "I do hope you will understand.  Please don't tell anyone.  But I don't have any ";
			echo "data to show you.  For you see, nobody has bothered to populate the graphs table.";
		}
	}
?>
</TD>
</TR>
<TR align="center">
<TD>
<div id="title"></div>
<div id="overview" style="width:400;height:100"></div>
<table>
<tr valign="top">
<td>
<div id="list"></div>
</td>
<td>
<div id="holder"></div>
</td>
</tr>
</table>
</TD>
</TR>
</TABLE>


</TD></TR>

</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
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
