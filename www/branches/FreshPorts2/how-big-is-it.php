<?php
	#
	# $Id: how-big-is-it.php,v 1.1.2.16 2005-01-05 23:14:37 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('How big is it?',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	$Total = 0;
	$Date = date('Y/m/d', time() - 86400);

function format_number($Value) {
	return str_replace(' ', '&nbsp;', sprintf('%6s', $Value));
}
	
function StatsSQL($db, $Title, $Date) {
	$sql = "select value, date 
             from daily_stats_data, daily_stats 
            where daily_stats_id = daily_stats.id 
              and daily_stats.title = '$Title' 
              and date = '$Date'";

	$result = pg_exec($db, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			$myrow  = pg_fetch_array ($result, 0);
			$Value  = $myrow[0];
		} else {
			$Value = 'numrows = ' . $numrows . ' ' . $sql;;
		}
	} else {
		$Value = pg_errormessage();
	}

	return $Value;
}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<TR>
	<? echo freshports_PageBannerText("How big is it"); ?>
</TR>

<TR><TD>
<P>
It was a few days ago that I was thinking about search engines crawling through this website.
I began to wonder just how many web pages there are here.  To calculate this total, it's not 
just a simple matter of counting files on disk.  Most of the web pages are created from entries
in the database.  One recent evening, I started to design a formula to find out how many web pages
there are.  Roughly.  This will not be 100% accurate, but it will be close.
</P>
</TD></TR>

<TR>
<td>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Pages on disk"); 
	?>
</TR>

<TR><TD>

<P>
First, let's count the number of pages on disk:
<blockquote><code class="code">
$ ls *.php | wc -l<br>
<?php
$Files = `ls *.php | wc -l`;

echo $Files;
$Total += $Files;
?>
</code></blockquote>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Number of categories"); 
	?>
</TR>

<TR><TD>

<P>
There is a page for each category:
<blockquote><code class="code">
# select count(*) from categories;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Category count', $Date);
$Total += $Value;
echo format_number($Value) . '<br>'

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Number of ports"); 
	?>
</TR>

<TR><TD>

<P>
There are ports, and there are deleted ports. I'll show both:
<blockquote><code class="code">
# select count(*) from ports_all where status = 'A';<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Port count', $Date);
$Total += $Value;
echo format_number($Value) . '<br>'

?>
<br>
# select count(*) from ports_all where status = 'D';<br>
&nbsp;count<br>
-------<br>

<?php

$Value = StatsSQL($db, 'Port count (deleted)', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Number of commits"); 
	?>
</TR>

<TR><TD>

<P>
There is a page for each commit:
<blockquote><code class="code">
# select count(*) from commit_log;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit count (ports)', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Number of ports for each commit"); 
	?>
</TR>

<TR><TD>

<P>
For each commit, you can view the files modified by that commit for a particular port:
<blockquote><code class="code">
# select count(*) from commit_log_ports;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit Port Count', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("How many days?"); 
	?>
</TR>

<TR><TD>

<P>
For each day, there is a page showing the commits for that day.  How many days do we have?
<blockquote><code class="code">
# select count(distinct commit_date) from commit_log;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit days (ports)', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("How many users?"); 
	?>
</TR>

<TR><TD>

<P>
Each user has a page:
<blockquote><code class="code">
# select count(*) from users;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'User count', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("How many watch lists?"); 
	?>
</TR>

<TR><TD>

<P>
For each watch list, there is a page:
<blockquote><code class="code">
# select count(*) from watch_list;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Watch List count', $Date);
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<? 
	echo freshports_PageBannerText("Estimated total"); 
	?>
</TR>

<TR><TD>

<P>
<?php $GooglePages = 8058044651; ?>
That gives a grand total of <?php echo number_format($Total) ?> pages.  On my last count, that's 
about <?php echo number_format($Total / $GooglePages * 100, 6) ?>% of the
web pages on <a href="http://www.Google.com/">Google</a><small><sup><a href="#1">1</a></sup></small>
</P>

<p>
<h2>Notes</h2>
<ul>
<li>These statistics are updated daily.
<li><sup>1</sup><a name="1"></a>The number of Google pages used in this calculation is <?php echo number_format($GooglePages) ?>.
</ul>

</td></tr>


</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	freshports_SideBar();
	?>
  </td>

</TABLE>

<?
freshports_ShowFooter();
?>

</BODY>
</HTML>
