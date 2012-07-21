<?php
	#
	# $Id: privacy.php,v 1.4 2012-07-21 23:23:58 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_ConditionalGet(freshports_LastModified());

	freshports_Start('Privacy Policy',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>


<TR>
	<? echo freshports_PageBannerText('Privacy statement'); ?>
</TR>
<TR><TD>

<CENTER>
<?php
	if ($ShowAds) echo Ad_728x90();
?>
</CENTER>

<P>All the information we
    gather is for our own use.  We do not release it to anyone else.</P>
    <P>For example, when you subscribe to our mailing list, we
    keep that to ourselves and nobody else will know.  We don't sell our mailing lists.
      Or any other private information for that matter.</P>
    <P>Most websites gather statistics regarding the number of times a page was accessed.
      We do this.  This means your IP address, or the IP address of your proxy will
    be recorded in our access logs.  We do not release this information to anyone.  
    It wouldn't be much use to anyone anyway.</P>
    <P>The New Zealand Privacy Commissioner has some interesting reading at 
		<A href="http://www.knowledge-basket.co.nz/privacy/top.html">http://www.knowledge-basket.co.nz/privacy/top.html</A>.
		
<p>
We use third-party advertising companies to serve ads when you visit our Web site. These companies may use aggregated information (not including your name, address, email address or telephone number) about your visits to this and other Web sites in order to provide advertisements about goods and services of interest to you. If you would like more information about this practice and to know your choices about not having this information used by these companies, click <a href="http://www.networkadvertising.org/managing/opt_out.asp">here</a>. 
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
