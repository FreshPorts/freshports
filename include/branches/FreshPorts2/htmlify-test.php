<?php
	#
	# $Id: htmlify-test.php,v 1.1.2.3 2003-10-02 14:23:56 dan Exp $
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

EOD;

?>

<html>
<body>
<h2>Testing the html-ify code</h2>
<pre>
<?php print htmlify(htmlspecialchars($str)); ?>
</pre>

<h2>	Other links to check</h2>

<ul>
<li>Ensure all PRs are HTMLified: <a href="/databases/postgresql7/files.php?message_id=200302062046.h16KkqNv024036@repoman.freebsd.org">/databases/postgresql7/files.php?message_id=200302062046.h16KkqNv024036@repoman.freebsd.org</a>
<li>Good test page: <a href="/lang/php4/">/lang/php4/</a>
</ul>

</body>
</html>