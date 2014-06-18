<?php
	#
	# $Id: htmlify-test.php,v 1.5 2007-10-23 18:55:08 dan Exp $
	#
	# Copyright (c) 2003 DVL Software Limited
	#

	require_once("htmlify.php");

define('URL2LINK_CUTOFF_LEVEL', 130);
define('MAILTO', '&#109;&#97;&#105;&#108;&#116;&#111;');

$str = <<<EOD
this is email dan@langille.org 
this is email <dan@langille.org> 
this is url   http://langille.org/ o ho 
this is url   http://langille.org/ 
this is url   <http://langille.org/> 
PR: 12345,43 23123123
And these too: PR 123
PR 1324
PR 234,394 234
PR 234,394, 234
PR 234, 234, and 234
PR: 234, 234, and 234

port check < 0 or > 10
only with Netscape. You can trace the discussion at: <http://home.jp.FreeBSD.org/cgi-bin/thread?mesid=%3c20020115163006%2e482281905%40taro%2ec%2eu%2dtokyo%2eac%2ejp%3e>

There is "some quoted stuff".  And other 'quoted stuff';

and this is quoted url: "http://test.foo.bar" (yeah!)

Submitted by:   NAKAJI Hiroyuki <nakaji@tutrp.tut.ac.jp>
Obtained from:  http://home.jp.freebsd.org/cgi-bin/showmail/ports-jp/12612

PR:             ports/39731, ports/39732, ports/39733, ports/39734, ports/39735
			  ports/39736, ports/39737, ports/39738, ports/39739 ports/39740
Submitted by:   Scott Flatman <sf@dsinw.com>

PR:             ports/47983 [1], ports/47284 [2], ports/47808 [3]
Submitted by:   maintainer [1]
                Jason C. Wells [2]
                Michel Oosterhof <m.oosterhof@xs4all.nl> [3]

http://test http://test

cutoff (130)
http://this.is.very.long.url.and.it.should.be.truncated.this.is.very.long.url.and.it.should.be.truncated.this.is.very.long.url.and.it.should.be.truncated.com

double match test:
http://this.is.link/x.php?email=somebody@host.com&anotheremail=other@other.org&x=1 !
http://this.is.link.with/test?PR:123/qweeq
http://www.freebsd.org/cgi/query-pr.cgi?pr=ports/39731

Does it handle great then (>) and less than (<) and ampersand (&)?


Make sure that links with trailing commas work OK:  The following is from java/linux-sun-jdk14/Makefile:

IGNORE: You must manually fetch the J2SE SDK self-extracting file for the Linux platform (j2sdk-1_4_2_10-linux-i586.bin) from http://javashoplm.sun.com/ECom/docs/Welcome.jsp?StoreId=22&PartDetailId=j2sdk-1.4.2_10-oth-JPR&SiteId=JSC&TransactionId=noreg, place it in /usr/ports/distfiles and then run make again

Should have a URL: http://www.sql-ledger.org/cgi-bin/nav.pl?page=news.html&title=What's%20New


EOD;

?>

<html>
<body>
<h2>Testing the html-ify code</h2>
<pre>
<?php print htmlify(htmlspecialchars($str), true); ?>
</pre>

<p>
The following should not get a PR type hyperlink:

<?php echo htmlify("http://www.postgresql.org/docs/8.2/static/release-8-2-2.html", false); ?>


<h2>	Other links to check</h2>

<ul>
<li>Ensure all PRs are HTMLified: <a href="/databases/postgresql7/files.php?message_id=200302062046.h16KkqNv024036@repoman.freebsd.org">/databases/postgresql7/files.php?message_id=200302062046.h16KkqNv024036@repoman.freebsd.org</a>
<li>Good test page: <a href="/lang/php4/">/lang/php4/</a>
</ul>

<h2>Does it htmlify the URL and the URL name?</h2>
<ul>
<li>Before: http://www.sql-ledger.org/cgi-bin/nav.pl?page=news.html&title=What's%20New
<li>After: <?php echo htmlify("http://www.sql-ledger.org/cgi-bin/nav.pl?page=news.html&title=What's%20New"); ?>
</ul>

</body>
</html>