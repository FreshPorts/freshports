<?
	# $Id: footer.php,v 1.5.2.16 2003-07-04 14:59:18 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
?>
<HR>
<TABLE WIDTH="98%" BORDER="0">
<?
GLOBAL $ShowPoweredBy;

if (IsSet($ShowPoweredBy)) {
?>
<TR>

<TD align="center">

<A HREF="http://www.freebsd.org/"><IMG SRC="/images/pbfbsd2.gif"
ALT="powered by FreeBSD" BORDER="0" WIDTH="171" HEIGHT="64"></A>

&nbsp;

<A HREF="http://www.phorum.org/"><IMG SRC="/phorum/images/phorum.gif"
ALT="powered by phorum" BORDER="0" WIDTH="200" HEIGHT="50"></A>

&nbsp;

<A HREF="http://www.php.net/"><IMG SRC="/images/php-med-trans-light.gif"
ALT="powered by php" BORDER="0" WIDTH="95" HEIGHT="50"></A>

</TD></TR>
<TR><TD align="center">


<A HREF="http://www.postgresql.org/"><IMG SRC="/images/postgresql-powered_button4.gif"
ALT="powered by PostgreSQL" BORDER="0" WIDTH="182" HEIGHT="41"></A>


&nbsp;&nbsp;&nbsp;

<A HREF="http://www.apache.org/"><IMG SRC="/images/apache_pb.gif" 
ALT="powered by apache" BORDER="0" WIDTH="259" HEIGHT="32"></A>

<HR>

</TR>

<?
}
?>

<TR><TD>
<table width="100%">
<tr><td align="left">
<p>

<a href="http://validator.w3.org/check/referer"><img border="0" 
src="/images/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>

&nbsp;

<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px"
src="/images/vcss" alt="Valid CSS!"></a>

&nbsp;

<a href="http://feeds.archive.org/validator/check?url=http://<?php echo $_SERVER['HTTP_HOST']; ?>/news.php"><img 
src="/images/valid-rss-dylan4.gif" alt="[Valid RSS]" title="Validate my RSS feed" width="88" height="31"></a>

</p>
</td><td align="right" valign="top">

<? echo freshports_copyright(); ?>

</td></tr>
</table>
</TD></TR>
</TABLE>

<?
	GLOBAL $ShowAds;
	if ($ShowAds) {
		Burst_468x60_Below();
	}
?>
