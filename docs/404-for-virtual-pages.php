<?php
	#
	# $Id: 404-for-virtual-pages.php,v 1.3 2007-10-25 11:57:23 dan Exp $
	#
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	
	$Title = 'Using 404 errors to serve up virtual pages and virtual categories';
?>
<html>
<HEAD>
<TITLE><?php echo $Title; ?></TITLE>

<?php
	freshports_style();
?>
</HEAD>
 

<body>

<H1><?php echo $Title; ?></H1>

<p>
<h2>About this document</h2>
<p>
This page documents how <a href="http://www.FreshPorts.org/">FreshPorts</a> can use the
404 error process to serve up virtual pages.

<p>
It is assumed that the reader is familiar with the <a href="http://www.FreeBSD.org/ports/">FreeBSD
ports tree</a> and how it works.

<h2>Background</h2>

<p>
The FreeBSD ports tree contains several thousand port skeletons.  These skeletons are used to
download, compile, and installed software from source.  For ease of use, these ports are divided 
into different directories called categories.

<p>
FreshPorts uses the <a href="http://www.PostgreSQL.org/">PostgreSQL</a> RDBMS.

<p>
FreshPorts allows a user to broswe through the website in much the same way as they might
move around in a directory tree.  In effect, the website mirrors what the user might find on
disk but with enhanced information and presentation.

<p>
For example, on disk the user would find the following:

<blockquote><pre class="code">
[dan@m20:/usr/ports] $ ls
INDEX           distfiles       news
INDEX-5         editors         palm
INDEX.db        emulators       picobsd
LEGAL           finance         portuguese
MOVED           french          print
Makefile        ftp             russian
Mk              games           science
README          german          security
Templates       graphics        shells
Tools           hebrew          sysutils
archivers       hungarian       textproc
astro           irc             ukrainian
audio           japanese        vietnamese
benchmarks      java            www
biology         korean          x11
cad             lang            x11-clocks
chinese         mail            x11-fm
comms           math            x11-fonts
converters      mbone           x11-servers
databases       misc            x11-toolkits
deskutils       multimedia      x11-wm
devel           net
[dan@m20:/usr/ports] $</pre></blockquote>

<p>
Most of the above directories are categories.  Exceptions include INDEX, Mk, and README.
For this dicussion, we will ignore the non-categories which appear in the /usr/ports directory.

<p>
For example, the shells directory contains:

<blockquote><pre class="code">
[dan@m20:/usr/ports/shells] $ ls
44bsd-csh       mudsh           scponly
Makefile        nologinmsg      tcsh
bash1           osh             vshnu
bash2           pash            wapsh
es              pdksh           zsh
esh             perlsh          zsh+euc_hack
fd              pkg             zsh-devel
flash           rc
ksh93           sash
[dan@m20:/usr/ports/shells] $</pre></blockquote>

None of the above directories actually appear on the webserver.
The website takes advantage of the 
<a href="http://httpd.apache.org/docs/mod/core.html#errordocument">ErrorDocument</a>
directive in the <a href="http://httpd.apache.org/">Apache</a> webserver.  This is
the entry found within the FreshPorts website definition:

<blockquote><pre class="code">ErrorDocument   404 /missing.php</pre></blockquote>

Should an page be requested which is not found, <code class="code">missing.php</code>
is invoked.  The code within this page determines which category and/or port is being 
requested.  This is accomplished using <code class="code">$_SERVER['REQUEST_URI']</code>
and database queries to establish which category and/or port is being requested.

<h2>Processing the REQUEST_URI</h2>

<p>
The process used to determine the category and/or port needs to be improved and simplified.
The process can be express by this pseudo-code:

<blockquote><pre class="code">
read list of categories
IF category exists THEN
   IF port name is supplied THEN
      IF port exists THEN
         display details for port
      ELSE
        display 'port not found' page
      END IF
   ELSE
      display ports within this category
   END IF
ELSE
   display 'category not found' page
END IF
</pre></blockquote>

<h2>Virtual categories</h2>

So far we have dealt only with categories which correspond to a physical subdirectory within
the ports tree.  There are some categories, referred to as <i>virtual</i> categories
that do not have a corresponding subdirectory in the ports tree.  For more information on virtual
categories, please see <a href="http://www.freebsd.org/doc/en_US.ISO8859-1/books/porters-handbook/makefile-categories.html#PORTING-CATEGORIES">Current list of categories</a>
in the <a href="http://www.freebsd.org/doc/en_US.ISO8859-1/books/porters-handbook/index.html">FreeBSD Porter's Handbook</a>.

<p>
Here is entry from the Makefile for <a href="http://www.FreshPorts.org/databases/zpygresqlda/">databases/zpygresqlda</a>
which specifies the categories for that port:

<blockquote><pre class="code">CATEGORIES=     databases www zope</pre></blockquote>

<p>
The first category listed is the subdirectory which contains the files for this port.
The other categories may be either real or virtual, but the first category must
always be the <i>real</i> or <i>physical</i> directory.

<p>
The physical directory for each port is stored within the ports table.
The table also contains a <code class="code">categories</code> field which contains the value
extracted from the Makefile (e.g. for this port, the field would contain 
<code class="code">databases www zope</code>).  Have a look at 
<a href="http://www.FreshPorts.org/databases/zpygresqlda/">databases/zpygresqlda</a> to see how this
information is used.  Look for the entry titled <i>Also listed in</i>.

<p>
The proposed solution for 



<h2>Existing implementation of categories</h2>

The FreshPorts database contains a <code class="code">ports</code> table and 
a <code class="code">categories</code> table. The system
caters for a port which resides within one physical category but does not 
cater for virtual categories.  
In brief, these tables look like this:

<blockquote>
<h3>Categories table</h3>
<table border="1" cellspacing="0" cellpadding="3">
<tr><td><b>Field name</b></td><td><b>type</b></td></tr>
<tr><td>id</td><td>int</td></tr>
<tr><td>name</td><td>text</td></tr>
<tr><td>is_primary</td><td>boolean</td></tr>
</table>

<h3>Ports table</h3>
<table border="1" cellspacing="0" cellpadding="3">
<tr><td><b>Field name</b></td><td><b>type</b></td></tr>
<tr><td>id</td><td>int</td></tr>
<tr><td>category_id</td><td>int</td></tr>
</table>

</blockquote>

<p>
The <code class="code">category_id</code> field in the <code class="code">ports</code> table contains 
a foreign key reference to the <code class="code">categories</code> table.

<h2>Proposed implementation of virtual categories</h2>

It is proposed that we introduce a new table, <code class="code">ports_category</code> which 
will look similar to this:

<blockquote>
<h3>ports_category</h3>
<table border="1" cellspacing="0" cellpadding="3">
<tr><td><b>Field name</b></td><td><b>type</b></td><td><b>referential integrity</b></td></tr>
<tr><td>port_id</td><td>int</td><td>linked to ports table</td></tr>
<tr><td>category_id</td><td>id</td><td>linked to categories table</td></tr>
</table>
</blockquote>

<p>
For databases/zpygresqlda, there will be three entries in this table, one for each category.

<p>
This table will be populated via a rule (as opposed to a trigger) on the ports table.  For each
update, insert, and delete on the ports table, the rule will amend the ports_category table
accordingly.

<p>
The same rule could be used to maintain the <code class="code">categories</code> table.  This would
ensure that new virtual categories are added/removed as/when necessary.

<p>
This solution should make it fairly each to serve up virtual categories.  More investigation is needed
to consider the possible implications for other parts of the system.

<h2>Where to from here</h2>

<p>
Feedback please, preferably by posting in the 
<a href="http://www.freshports.org/phorum/read.php?f=1&i=504&t=504">Website Feedback</a> or via
email to the webmaster of this domain.  
<p>
<?
echo freshports_ShowFooter();
?>

</body>
</html>