<?php
	#
	# $Id: how-big-is-it.php,v 1.7 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'How big is it?';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

	$Total = 0;

function format_number($Value) {
	return str_replace(' ', '&nbsp;', sprintf('%6s', $Value));
}

#
# grabbed from https://ca3.php.net/manual/en/function.number-format.php
# was attributed there to: Thanks to "php dot net at alan-smith dot no-ip dot com" and "service at dual-creators dot de".	
#
function human_readable($size)
{
	$count = 0;
	$format = array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
	while(($size/1024) > 1 && $count < 8) {
		$size=$size/1024;
		$count++;
	}
	if( $size < 10 ) {
		$decimals = 1;
	} else {
		$decimals = 0;
	}
	$return = number_format($size,$decimals,'.',' ')." ".$format[$count];

	return $return;
}

function StatsSQL($db, $Title) {
	$sql = "select value, date 
             from daily_stats_data, daily_stats 
            where daily_stats_id = daily_stats.id 
              and daily_stats.title = $1
         ORDER BY date DESC
            LIMIT 1";

	$result = pg_query_params($db, $sql, array($Title));
	if ($result) {
		$numrows = pg_num_rows($result);
		if ($numrows) {
			$myrow  = pg_fetch_array ($result, 0);
			$Value  = $myrow[0];
		} else {
		    syslog(LOG_NOTICE, $_SERVER["PHP_SELF"] . ' no stats found for ' . $Title . ' numrows = ' . $numrows . ' ' . $sql);
		    $Value  = '';
		}
	} else {
		$Value = pg_last_error($db);
	}

	return $Value;
} 

function DBSize($db) {
	$sql = "select pg_database_size(current_database())";

	$result = pg_query_params($db, $sql, array());
	if ($result) {
		$numrows = pg_num_rows($result);
		if ($numrows) {
			$myrow  = pg_fetch_array ($result, 0);
			$Value  = $myrow[0];
		} else {
			$Value = 'numrows = ' . $numrows . ' ' . $sql;;
		}
	} else {
		$Value = pg_last_error($db);
	}

	return $Value;
}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<tr>
	<?php echo freshports_PageBannerText("How big is it"); ?>
</tr>

<tr><td class="textcontent">

<?php
	if ($ShowAds) echo '<CENTER>' . Ad_728x90() . '</CENTER>';
?>

<P>
It was a few days ago that I was thinking about search engines crawling through this website.
I began to wonder just how many web pages there are here.  To calculate this total, it's not 
just a simple matter of counting files on disk.  Most of the web pages are created from entries
in the database.  One recent evening, I started to design a formula to find out how many web pages
there are.  Roughly.  This will not be 100% accurate, but it will be close.
</P>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Pages on disk"); 
	?>
</tr>

<tr><td class="textcontent">

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

</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Number of categories"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
There is a page for each category:
<blockquote><code class="code">
# select count(*) from categories;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Category count');
$Total += $Value;
echo format_number($Value) . '<br>'

?>
(1 row)<br>
</code></blockquote>
</td></tr>
<tr>
	<?php
	echo freshports_PageBannerText("Number of ports"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
There are ports, and there are deleted ports. I'll show both:
<blockquote><code class="code">
# select count(*) from ports_all where status = 'A';<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Port count');
$Total += $Value;
echo format_number($Value) . '<br>'

?>
<br>
# select count(*) from ports_all where status = 'D';<br>
&nbsp;count<br>
-------<br>

<?php

$Value = StatsSQL($db, 'Port count (deleted)');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Number of files in the ports tree"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
There is a page for each file in the ports tree:
<blockquote><code class="code">
[dan@ngaio:/usr/ports] $ find . | wc -l<br>
<?php
$Value = 115803;
$DateLastChecked =  "2007/02/12 01:58:58"; # default value, found at time of writing.

$PortFileCount = HTML_DIRECTORY . '/PortsTreeCount';
if (file_exists($PortFileCount) && is_readable($PortFileCount) && filesize($PortFileCount) < 1000) {
  $FileContents = trim(file_get_contents($PortFileCount));
  if (is_numeric($FileContents)) {
    if (intval($FileContents) == $FileContents) {
      $Value = intval($FileContents);
      $DateLastChecked = gmdate(LAST_MODIFIED_FORMAT, filemtime($PortFileCount));
    }
  }
}
echo format_number($Value);
?>
<br>
[dan@ngaio:/usr/ports] $
<?php
$Total += $Value;
?>
</code></blockquote>

Count last performed at <?php echo $DateLastChecked; ?>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Number of commits"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
There is a page for each commit:
<blockquote><code class="code">
# select count(*) from commit_log;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit count (ports)');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Number of ports for each commit"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
For each commit, you can view the files modified by that commit for a particular port:
<blockquote><code class="code">
# select count(*) from commit_log_ports;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit Port Count');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("How many days?"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
For each day, there is a page showing the commits for that day.  How many days do we have?
<blockquote><code class="code">
# select count(distinct commit_date) from commit_log;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Commit days (ports)');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("How many users?"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
Each user has a page:
<blockquote><code class="code">
# select count(*) from users;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'User count');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("How many watch lists?"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
For each watch list, there is a page:
<blockquote><code class="code">
# select count(*) from watch_list;<br>
&nbsp;count<br>
-------<br>
<?php

$Value = StatsSQL($db, 'Watch List count');
$Total += $Value;
echo format_number($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>
</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("Estimated total"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
<?php $GooglePages = 8058044651; ?>
That gives a grand total of <?php echo number_format($Total) ?> pages.  On my last count, that's 
about <?php echo number_format($Total / $GooglePages * 100, 6) ?>% of the
web pages on <a href="https://www.Google.com/">Google</a><small><sup><a href="#1">1</a></sup></small>
</P>

<p>
<h2>Notes</h2>
<ul>
<li>These statistics are updated daily.
<li id="1"><sup>1</sup>The number of Google pages used in this calculation is <?php echo number_format($GooglePages) ?>.
</ul>

</td></tr>

<tr>
	<?php
	echo freshports_PageBannerText("How much diskspace?"); 
	?>
</tr>

<tr><td class="textcontent">

<P>
The total space used by the FreshPorts database is:
<blockquote><code class="code">
# select pg_database_size('freshports.org');<br>
&nbsp;pg_database_size<br>
------------------<br>
<?php

$Value = DBSize($db);
echo number_format($Value) . '<br>';

?>
(1 row)<br>
</code></blockquote>

<p>That's bytes...
<p>This value might be easier to parse: <?php echo human_readable($Value); ?>
</td></tr>


</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</table>

<?php
echo freshports_ShowFooter();
?>

</body>
</html>
