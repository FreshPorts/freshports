<?php
	#
	# $Id: about.php,v 1.3 2012-07-21 23:23:57 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	$Title = 'About this site';
	freshports_Start($Title,
					$Title,
					"FreeBSD, index, applications, ports");

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>

<tr>
	<?php echo freshports_PageBannerText("About this site"); ?>
</tr>

<tr><td class="textcontent">
<P>
We have a few notes about this website.
</P>

<?php
	if ($ShowAds) echo '<CENTER>' . Ad_728x90() . '</CENTER>';
?>

</td></tr>
<tr>
	<?php
	echo freshports_PageBannerText("What is a port?"); 
	?>
</tr>

<tr><td class="textcontent">

<P>A port is the term used to describe a collection of files which makes it extremely
easy to install an application.  As it says in the <a href="https://www.freebsd.org/ports/">
FreeBSD Ports description</a>: <em>Installing an application is as simple as downloading
the port, unpacking it and typing <b>make</b> in the port directory</em>. If you want an application, 
the port is the Way To Go(TM)</P>

<P>So off you go to the ports tree to install your favourite port.  It's quite easy. It's simple.
And you love that new application.  And you want to know when the port is updated.  That's where
we come in.</P>

<P>For more information about the Ports tree, see <a href="https://www.freebsd.org/ports/">https://www.freebsd.org/ports/</a>.</P>

</td></tr>
<tr>
	<?php
	echo freshports_PageBannerText("What is $FreshPortsTitle");
	?>
</tr>

<tr><td class="textcontent">

<P><?php echo $FreshPortsTitle; ?> lists the changes made to the ports tree. If you wish, <?php echo $FreshPortsTitle; ?> can email you 
when your favourite port has been updated.
</P>

<P>
<?php echo $FreshPortsTitle; ?> is not the place to report errors or request changes.  You should do that on the 
<a href="<?php echo MAILTO; ?>:freebsd-ports&#64;freebsd.org">FreeBSD Ports mailing list</a>.  We do not maintain ports.  We do not
create ports.  We do not fix ports.  We just tell you what others have been doing to the Ports tree.
</P>

</td></tr>
<tr>
	<?php
	echo freshports_PageBannerText("OK, whose bright idea was this?");
	?>
</tr>

<tr><td class="textcontent">
<P>This site was created by Dan Langille.  His other web feats include 
<a href="https://www.freebsddiary.org/">The FreeBSD Diary</a>, <a href="https://www.racingsystem.com">The Racing System</a>, 
<a href="https://www.bsdcan.org/">BSDCan</a>, and an ability
to avoid reading the inane comments on <a href="https://slashdot.org">slashdot</a>.
But Dan didn't create the site all by himself.  Have a look at <a href="authors.php">
About the Authors</a> for details of who else helped.</P>
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
	GLOBAL $ShowPoweredBy;
	$ShowPoweredBy = 1;
?>

<table class="fullwidth borderless">
<tr><td>
<?php echo freshports_ShowFooter(); ?>
</td></tr>
</table>

</BODY>
</HTML>
