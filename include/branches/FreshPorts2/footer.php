<?
	# $Id: footer.php,v 1.5.2.4 2002-02-21 14:53:17 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
?>
<HR>
<TABLE WIDTH="98%" BORDER="0">
<?

if ($ShowPoweredBy) {
?>
<TR>

<TD align="center">

<A HREF="http://www.freebsd.org/"><IMG SRC="/images/pbfbsd2.gif"
ALT="powered by FreeBSD" BORDER="0" WIDTH="171" HEIGHT="64"></A>

&nbsp;

<A HREF="http://www.postgresql.org/"><IMG SRC="/images/postgresql-powered_button4.gif"
ALT="powered by PostgreSQL" BORDER="0" WIDTH="182" HEIGHT="41"></A>

&nbsp;

<A HREF="http://www.php.net/"><IMG SRC="/images/php-med-trans-light.gif"
ALT="powered by php" BORDER="0" WIDTH="95" HEIGHT="50"></A>

</TD></TR>
<TR><TD align="center">

<A HREF="http://www.apache.org/"><IMG SRC="/images/apache_pb.gif" 
ALT="powered by apache" BORDER="0" WIDTH="259" HEIGHT="32"></A>

&nbsp;&nbsp;&nbsp;

<A HREF="http://www.phorum.org/"><IMG SRC="/phorum/images/phorum.gif"
ALT="powered by phorum" BORDER="0" WIDTH="88" HEIGHT="31"></A>

</TR>

<?
}
?>

<TR><TD align="right">
<SMALL><A HREF="/legal.php" target="_top">Copyright</A> 2000,2001 <A HREF="http://www.dvl-software.com/">DVL Software Limited</A>.
All rights reserved.</SMALL>
</TD></TR>
</TABLE>

<?
	diary_ads_Random();
?>
