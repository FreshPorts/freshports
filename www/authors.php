<?php
	#
	# $Id: authors.php,v 1.4 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'The Authors';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(); ?>

  <tr>
	<?php echo freshports_PageBannerText("About the authors"); ?>
  </tr>
<tr><td>
<?php
	if ($ShowAds) echo '<CENTER>' . Ad_728x90() . '</CENTER>';
?>

<p><a href="https://www.langille.org/" rel="noopener noreferrer">Dan Langille</a> thought up the idea, found the data sources, bugged people to 
write scripts, and did the html and database work. But he certainly didn't 
do it alone.</p>

<p>
The details of day-to-day changes, bug fixes, challenges, and new features
are documented on the <a href="https://news.freshports.org/" rel="noopener noreferrer">news blog</a>.

<H2>FreshPorts 2</H2>

<P>I apologize as I have not been keeping this list up to date and therefore I fear I have
   missed people but I don't know who.  Please let me know if you should be here.</P>

<UL>
<LI>Adam Herzog wrote the XML DTD and the perl script which converts the raw email to XML.</LI>
<LI>Marcin Gryszkalis did the underlying work for the the <a href="/graphs.php">graphs</a>.  He
    also helped out with the htmlifying of the log message (so you can click on PR and email and URLs).</LI>

<LI>Jonathan Sage helped to reclaim some missing ports by writing some perl code to pull
    things out of CVS.</LI>

<li>Dan Peterson showed me the wonders of <a href="https://cr.yp.to/daemontools.html" rel="noopener noreferrer">Daemon Tools</a>
    which handles the processing of incoming messages and refreshes the main web page.</li>

<li>Josef Karthauser for helping me through the cvs-all log format and for greatly simplifying the
	job of FP2.</li>

<li>Titus Manea again always has great ideas.  His knowledge base of *nix far exceeds my own.</li>

<li>Ade Lovett for grilling me about my need for daemons and leading me to discover Daemon Tools via Dan P.
	And for his mega-commits which prompted me to show abbreviated commits.</li>

</UL>


<H2>FreshPorts (original)</H2>

<UL>

<li>Olaf wrote did the perl script for the log catcher.</li>

<li>Titus Manea wrote the awk code for the log catcher and the log munger.</li>

<li>Adriel helped me with perl syntax.</li>

<li>Will Andrews talked over data sources with me.</li>

<li>John Polstra and Satoshi Asami provided insight into cvs and ports as well
as encouragement.</li>

<li>Jeremy Shaffner hung around, criticized, and suggested security improvements.</li>

<li>Brian Mitchell did some prototype coding for me.</li>

<li>David Bushong did a FreshBSD site which is a freshmeat-look site.</li>

<li>lzh on undernet #perl helped me with my perl knowledge.  Some of his examples
form the basis for some of the most important parts of the system.  Aquitaine
also showed me the PERL dbi->quote() function.</li>

<li>John Beige did the logo you see at the top of the page.</li>

<li>Wolfram Schneider's <a href="https://www.freebsd.org/cgi/ports.cgi" rel="noopener noreferrer">FreeBSD Ports Changes</a>
page provided much of the basis for this site.</li>
	
<li>Jay gave me the box on which FreshPorts runs.  Thanks.</li>

<li>Will Andrews told me about <code class="code">make -V</code>.
This eventually led to the sanity checks that annoy Ports committers
and ensure users encounter fewer problems.

<li>And various people on undernet's #nz.general and #freebsd helped me with
scripts and ideas.  That's not to mention that channel on efnet which I won't 
name just so it stays a secret.</li>

</UL>

</td>
</tr>
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
