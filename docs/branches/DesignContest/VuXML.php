<html>
<head>
<title>FreshPorts - VuXML strategy</title>
<body>

<h1>FreshPorts - VuXML strategy</h1>

<p>
This page documents the strategy used when processing 
<a href="http://www.vuxml.org/freebsd/">VuXML</a> data.

<p>
<h2>Major Goal</h2>
The major goal is to flag records [within the Commit History for a given port] that
are affected by a VuXML entry.  The key to this is the ability to parse the XML
provided in <a href="<?php echo FRESHPORTS_FREEBSD_CVS_URL; ?>/ports/security/vuxml/vuln.xml?rev=.">ports/security/vuxml/vuln.xml</a>
and then associate that information with a given commit.

<h2>Processing Strategy</h2>

<p>
Storing all of the relevant VuXML data within FreshPorts gives us the most
flexibility and also divides up the process into two distinct stages:

<ol>
<li>Parse the VuXML data and load it into FreshPorts
<li>Find commits that are affected by a given VuXML entry and mark it as 
affected.
</ol>

<h2>Parsing the XML</h2>

<p>
Matthew Seaman has written a script (scripts/vuxml_parsing.pm) that provides us
with the information we need.

<p>
The data within the VuXML file is parsed only for package information. We do
not collect operating system information.

<p>
We have taken the initial approach that we will delete all previous VuXML
information when processing a newly committed vuln.xml file.  This simplifies
the code.  We may wish to rethink this approach at a later date.

<h2>Marking the commits - some database background</h2>

<p>
Technically speaking, we are not marking the commits.  We are marking the 
commit history of parts that have been affected by a given VuXML entry.

<p>
FreshPorts stores each commit in the <b>commit_log</b> table.  The <b>ports</b>
contains a row for each port.  The <b>commit_log_ports</b> table relates a
given commit to the ports it touches.  It is this data which forms the basis
for the <b>Commit History</b> for a port.

<p>
The <b>commit_log_ports</b> table also records
the PORTVERSION, PORTREVISION, and PORTEPOCH values of the port as a result
of that commit. It is this information which will be used to test against the
VuXML information.


<h2>Marking the commits - a proposed algorithm</h2>
<p>
Once the data is loaded into FreshPorts, we can obtain the names and ranges
like this:

<blockquote><pre class="code">
1 - for each range record
2 -     select all commit log records for the name provided
3 -     while name stays the same
4 -         test the version in thie commit log record
5 -         if affected, flag the record
6 -     end while
7 -     Add entries to commit_log_ports_vuxml table
8 - end for
</pre></blockquote>

<h2>Going into details</h2>
<p>
There is room for optimization here. Here are some notes based upon first
impressions.

<p>
The combination of port_version, port_revision, and port_epoch will be
known as the <b>PackageVersion</b>.

<ul>
<li>2 - Select distinct on <b>PackageVersion</b>.  This will
reduce the number of rows fetched and thereby the number of calls to 
pkg_version.
<p>
Here is the basic data for one package, without optmizing the data:

<blockquote><pre class="code">
freshports.org=# SELECT *
freshports.org-#   FROM commit_log_ports
freshports.org-#  WHERE port_id = (SELECT id
freshports.org(#   FROM ports
freshports.org(#  WHERE package_name = 'leafnode');
 commit_log_id | port_id | needs_refresh | port_version | port_revision | port_epoch | package_name
---------------+---------+---------------+--------------+---------------+------------+--------------
         24968 |     334 |             0 |              |               | 0          |
         16694 |     334 |             0 |              |               | 0          |
         13070 |     334 |             0 |              |               | 0          |
          6675 |     334 |             0 |              |               | 0          |
          4403 |     334 |             0 |              |               | 0          |
           387 |     334 |             0 |              |               | 0          |
         32745 |     334 |             0 | 1.9.22       | 0             | 0          |
         30477 |     334 |             0 | 1.9.21       | 0             | 0          |
         42376 |     334 |             0 | 1.9.24       | 0             | 0          |
         50748 |     334 |             0 | 1.9.26       | 0             | 0          |
         47813 |     334 |             0 | 1.9.25       | 0             | 0          |
         47864 |     334 |             0 | 1.9.25       | 0             | 0          |
         47866 |     334 |             0 | 1.9.25       | 0             | 0          |
         51127 |     334 |             0 | 1.9.27       | 0             | 0          |
         57455 |     334 |             0 | 1.9.29       | 0             | 0          |
         61982 |     334 |             0 | 1.9.31       | 0             | 0          |
         68185 |     334 |             0 | 1.9.33       | 1             | 0          |
         68220 |     334 |             0 | 1.9.33       | 1             | 0          |
         67873 |     334 |             0 | 1.9.33       | 0             | 0          |
         69092 |     334 |             0 | 1.9.34       | 0             | 0          |
         69481 |     334 |             0 | 1.9.34       | 0             | 0          |
         69666 |     334 |             0 | 1.9.35       | 0             | 0          |
         70534 |     334 |             0 | 1.9.36       | 0             | 0          |
         76142 |     334 |             0 | 1.9.37       | 0             | 0          |
         76703 |     334 |             0 | 1.9.38       | 0             | 0          |
         78226 |     334 |             0 | 1.9.39       | 0             | 0          |
         78720 |     334 |             0 | 1.9.40       | 0             | 0          |
         80775 |     334 |             0 | 1.9.41       | 0             | 0          |
         85050 |     334 |             0 | 1.9.42       | 0             | 0          |
         93590 |     334 |             0 | 1.9.43       | 0             | 0          |
        100888 |     334 |             0 | 1.9.45       | 0             | 0          |
        105183 |     334 |             0 | 1.9.46       | 0             | 0          |
        108885 |     334 |             0 | 1.9.47       | 0             | 0          |
        109162 |     334 |             0 | 1.9.48       | 0             | 0          |
        109250 |     334 |             0 | 1.9.49       | 0             | 0          |
        115340 |     334 |             0 | 1.9.50       | 0             | 0          |
        115537 |     334 |             0 | 1.9.51       | 0             | 0          |
        122575 |     334 |             0 | 1.9.52       | 0             | 0          |
        124505 |     334 |             0 | 1.9.52       | 1             | 0          |
        126058 |     334 |             0 | 1.9.53       | 0             | 0          |
        127860 |     334 |             0 | 1.9.54       | 0             | 0          |
        127905 |     334 |             0 | 1.9.54       | 0             | 0          |
        128426 |     334 |             0 | 1.9.54       | 1             | 0          |
        130595 |     334 |             0 | 1.10.0       | 0             | 0          |
        132193 |     334 |             0 | 1.10.1       | 0             | 0          |
        136422 |     334 |             0 | 1.10.2       | 0             | 0          |
        137625 |     334 |             0 | 1.10.3       | 0             | 0          |
        140096 |     334 |             0 | 1.10.4       | 0             | 0          |
        141873 |     334 |             0 | 1.10.5       | 0             | 0          |
(49 rows)

freshports.org=#
</pre></blockquote>

<p>
We might be able to reduce the number of rows fetched with this approach:

<blockquote><pre class="code">
freshports.org=# SELECT distinct CLP.port_id, CLP.port_version, CLP.port_revision, CLP.port_epoch
freshports.org-#   FROM commit_log_ports CLP, ports P
freshports.org-#  WHERE CLP.port_id    = P.id
freshports.org-#    AND P.package_name = 'leafnode';
 port_id | port_version | port_revision | port_epoch
---------+--------------+---------------+------------
     334 | 1.10.0       | 0             | 0
     334 | 1.10.1       | 0             | 0
     334 | 1.10.2       | 0             | 0
     334 | 1.10.3       | 0             | 0
     334 | 1.10.4       | 0             | 0
     334 | 1.10.5       | 0             | 0
     334 | 1.9.21       | 0             | 0
     334 | 1.9.22       | 0             | 0
     334 | 1.9.24       | 0             | 0
     334 | 1.9.25       | 0             | 0
     334 | 1.9.26       | 0             | 0
     334 | 1.9.27       | 0             | 0
     334 | 1.9.29       | 0             | 0
     334 | 1.9.31       | 0             | 0
     334 | 1.9.33       | 0             | 0
     334 | 1.9.33       | 1             | 0
     334 | 1.9.34       | 0             | 0
     334 | 1.9.35       | 0             | 0
     334 | 1.9.36       | 0             | 0
     334 | 1.9.37       | 0             | 0
     334 | 1.9.38       | 0             | 0
     334 | 1.9.39       | 0             | 0
     334 | 1.9.40       | 0             | 0
     334 | 1.9.41       | 0             | 0
     334 | 1.9.42       | 0             | 0
     334 | 1.9.43       | 0             | 0
     334 | 1.9.45       | 0             | 0
     334 | 1.9.46       | 0             | 0
     334 | 1.9.47       | 0             | 0
     334 | 1.9.48       | 0             | 0
     334 | 1.9.49       | 0             | 0
     334 | 1.9.50       | 0             | 0
     334 | 1.9.51       | 0             | 0
     334 | 1.9.52       | 0             | 0
     334 | 1.9.52       | 1             | 0
     334 | 1.9.53       | 0             | 0
     334 | 1.9.54       | 0             | 0
     334 | 1.9.54       | 1             | 0
     334 |              |               | 0
(39 rows)

freshports.org=#
</pre></blockquote>

<p>
You will notice that we are now doing a join on the ports table.  This caters
for ports such as <a href="/print/acroread/">acroread</a> and 
<a href="/print/acroread5/">acroread5</a>.

<p>
The reason we are fetching these rows is so we can invoke pkg_version(1)
with their values.  This allows us to determine if a given 
<b>PackageVersion</b> is affected by a VuXML entry.

<li>4 - the test is done by invoking pkg_version -t and examining the result.

<p>
Here is a short extract from some sample code output:

<blockquote><pre class="code">
We have a new package name: 'DarwinStreamingServer'
*** Working on le 4.1.3g
'8347', '4.1.1', '0', '0'
'/usr/local/sbin/pkg_version -t 4.1.1 4.1.3g' gives '<'
'8347', '4.1.2', '0', '0'
'/usr/local/sbin/pkg_version -t 4.1.2 4.1.3g' gives '<'
'8347', '4.1.2', '1', '0'
'/usr/local/sbin/pkg_version -t 4.1.2_1 4.1.3g' gives '<'
'8347', '4.1.3', '0', '0'
'/usr/local/sbin/pkg_version -t 4.1.3 4.1.3g' gives '<'
'8347', '4.1.3g', '0', '0'
'/usr/local/sbin/pkg_version -t 4.1.3g 4.1.3g' gives '='
'8347', '5.0', '0', '0'
'/usr/local/sbin/pkg_version -t 5.0 4.1.3g' gives '>'
'8347', '5.0', '1', '0'
'/usr/local/sbin/pkg_version -t 5.0_1 4.1.3g' gives '>'
'8347', '5.0.1.1', '0', '0'
'/usr/local/sbin/pkg_version -t 5.0.1.1 4.1.3g' gives '>'
'8347', '5.0.1.1', '1', '0'
'/usr/local/sbin/pkg_version -t 5.0.1.1_1 4.1.3g' gives '>'
We have a new package name: 'ImageMagick'
*** Working on lt 6.0.4.2
</pre></blockquote>

<p>
We are checking for all versions that are <= 4.1.3.g.  In this case,
affected versions will have result '<' or '='.

<p>
The steps involved in testing the versions are:

<ol>
<li>compare op1/v1
<li>if available, compare op2/v2
</ol>

<p>
The available operators are: <, <=, =, >, and >=.

<p>
"pkg_version -t" returns one of <, =, or >.

<li>5 - store each vuxml id affecting this <b>PackageVersion</b>.
<li>7 - when updating commit_log_ports_vuxml, something like this might be
useful:
<blockquote><pre class="code">
INSERT INTO commit_log_ports_vuxml (commit_log_id, port_id, vuxml_id)
SELECT commit_log_id,
       port_id,
       659 as vuxml_id
  FROM commit_log_ports
 WHERE port_id       = 334
   AND port_version  = '1.9.25'
   AND port_revision = '0'
   AND port_epoch    = '0';
</pre></blockquote>

<p>
This will need to be repeated for each <b>PackageVersion</b>.
</ul>

<h2>What else?</h2>
<p>
That's it.  Sounds simple.  Right?

<h2>Historical EPOCH</h2>

<p>
I'll tell what's up.  PORTEPOCH on historical commits.  Now that we have the code written
that updates commit_log_ports_vuxml, we are running into a problem with 'pkg_version -t'
being supplied with the wrong data, which causes the code to mark the wrong commits 
as being affected.  This problem appears to occur only on ports that have a non-zero
PORTEPOCH.

<h3>The plan</h3>
<p>
We know what ports have PORTEPOCH:

<blockquote><pre class="code">
freshports.org=# select count(*) from ports where portepoch != '0';
 count
-------
   246
(1 row)

freshports.org=#
</pre></blockquote>

<p>
For a given port, we can see where the PORTVERSIONs have gone backwards:

<blockquote><pre class="code">
freshports.org=#  select CLP.*
freshports.org-#    from commit_log_ports CLP, commit_log CL
freshports.org-#   where port_id = (select id from ports_active where name = 'scrollkeeper')
freshports.org-#     AND CLP.commit_log_id = CL.id
freshports.org-#  ORDER BY CL.commit_date desc;
 commit_log_id | port_id | needs_refresh | port_version | port_revision | port_epoch | package_name
---------------+---------+---------------+--------------+---------------+------------+--------------
        136719 |    5173 |             0 | 0.3.14       | 1             | 0          |
        134479 |    5173 |             0 | 0.3.14       | 1             | 0          |
        120086 |    5173 |             0 | 0.3.14       | 1             | 0          |
        119323 |    5173 |             0 | 0.3.14       | 1             | 0          |
        113057 |    5173 |             0 | 0.3.14       | 1             | 0          |
        111215 |    5173 |             0 | 0.3.14       | 0             | 0          |
        111840 |    5173 |             0 | 0.3.14       | 0             | 0          |
        105035 |    5173 |             0 | 0.3.14       | 0             | 0          |
        102907 |    5173 |             0 | 0.3.12       | 4             | 0          |
        101412 |    5173 |             0 | 0.3.12       | 4             | 0          |
        101380 |    5173 |             0 | 0.3.12       | 3             | 0          |
         92391 |    5173 |             0 | 0.3.12       | 2             | 0          |
         92221 |    5173 |             0 | 0.3.12       | 1             | 0          |
         83303 |    5173 |             0 | 0.3.12       | 1             | 0          |
         77278 |    5173 |             0 | 0.3.12       | 1             | 0          |
         77135 |    5173 |             0 | 0.3.12       | 1             | 0          |
         76924 |    5173 |             0 | 0.3.12       | 0             | 0          |
         76520 |    5173 |             0 | 0.3.12       | 0             | 0          |
         76228 |    5173 |             0 | 0.3.11       | 8             | 0          |
         71534 |    5173 |             0 | 0.3.11       | 8             | 0          |
         66305 |    5173 |             0 | 0.3.11       | 8             | 0          |
         65348 |    5173 |             0 | 0.3.11       | 7             | 0          |
         65013 |    5173 |             0 | 0.3.11       | 6             | 0          |
         64703 |    5173 |             0 | 0.3.11       | 6             | 0          |
         63626 |    5173 |             0 | 0.3.11       | 5             | 0          |
         52718 |    5173 |             0 | 0.3.11       | 4             | 0          |
         52705 |    5173 |             0 | 0.3.11       | 3             | 0          |
         50551 |    5173 |             0 | 0.3.11       | 2             | 0          |
         49410 |    5173 |             0 | 0.3.11       | 2             | 0          |
         48239 |    5173 |             0 | 0.3.11       | 1             | 0          |
         47310 |    5173 |             0 | 0.3.11       | 1             | 0          |
         45848 |    5173 |             0 | 0.3.11       | 1             | 0          |
         45510 |    5173 |             0 | 0.3.11       | 0             | 0          |
         44355 |    5173 |             0 | 0.3.11       | 0             | 0          |
         43795 |    5173 |             0 | 0.3.11       | 0             | 0          |
         43479 |    5173 |             0 | 0.3.11       | 0             | 0          |
         41741 |    5173 |             0 | 0.3.9        |               | 0          |
         40451 |    5173 |             0 | 0.3.9        | 0             | 0          |
         39869 |    5173 |             0 | 0.3.9        | 0             | 0          |
         38883 |    5173 |             0 | 0.3.9        | 0             | 0          |
         38822 |    5173 |             0 | 0.3.9        | 0             | 0          |
         37805 |    5173 |             0 | 0.2          | 0             | 0          |
         37804 |    5173 |             0 | 0.2          | 0             | 0          |
<b>         36042 |    5173 |             0 | 0.2          | 0             | 0          |</b>
         35969 |    5173 |             0 | 0.3.6        | 0             | 0          |
         15736 |    5173 |             0 |              |               | 0          |
         13030 |    5173 |             0 |              |               | 0          |
         12799 |    5173 |             0 |              |               | 0          |
         12147 |    5173 |             0 |              |               | 0          |
         12145 |    5173 |             0 |              |               | 0          |
         12060 |    5173 |             0 |              |               | 0          |
(51 rows)

freshports.org=#
</pre></blockquote>

<p>
The bold line indicates where the version went down from the previous commit.  Commits after
that one should have a PORTEPOCH != '0'.  It is important to scan upwards here, not down.
If we scan up, we find the first change.  By comparing that PORTEPOCH to the current PORTEPOCH,
we can detect if there has been more than one PORTEPOCH change.
<p>
Here is how we can track down the PORTEPOCH value:

<blockquote><pre class="code">
freshports.org=# select category from ports_active where name = 'scrollkeeper';
 category
----------
 textproc
(1 row)
</pre></blockquote>

<p>
We now know the category.

<blockquote><pre class="code">
freshports.org=# select pathname_id('ports/textproc/scrollkeeper/Makefile');
 pathname_id
-------------
       58214
(1 row)
</pre></blockquote>

<p>
We now know the element id for the Makefile for this port.

<blockquote><pre class="code">
freshports.org=# select * from commit_log_elements where commit_log_id = 36042 and element_id = 58214;
   id   | commit_log_id | element_id | revision_name | change_type
--------+---------------+------------+---------------+-------------
 145950 |         36042 |      58214 | 1.7           | M
(1 row)
</pre></blockquote>

<p>
And now we know the CVS revision for the Makefile which was created by this commit.

<p>
This URL gets us that revision:

<a href="<?php echo FRESHPORTS_FREEBSD_CVS_URL; ?>/~checkout~/ports/textproc/scrollkeeper/Makefile?rev=1.7&amp;content-type=text/plain"><?php echo FRESHPORTS_FREEBSD_CVS_URL; ?>/~checkout~/ports/textproc/scrollkeeper/Makefile?rev=1.7&amp;content-type=text/plain</a>

<p>
From that, we can get PORTEPOCH.  If that is not equal to the current value of 
ports.port_epoch, we know there has been more
than one change of PORTEPOCH, and we need to keep scanning.  If not, we can set
the commit_log_ports records accordingly.  Something like this:

<blockquote><pre class="code">
  update commit_log_ports set port_epoch='1' where port_id = 7366 and
   commit_log_id >= 57525;
</pre></blockquote>

<p>
That's a first crack at how to solve the historical PORTEPOCH issue.

<p>
We might also want to adjust only those ports which have PORTEPOCH != 0 and that have a
vuln entry:

<blockquote><pre class="code">
select distinct name
  from ports_active PA, commit_log_ports_vuxml CLPV
 WHERE PA.id = CLPV.port_id
   AND PA.portepoch != '0';
</pre></blockquote>

<h2>A better idea</h2>

<p>
What we need is a list of all the commits, and the Makefile revision that goes along with that commit.
We can then fetch the Makefile, extract PortEpoch, and assign the value to the database.

<p>
A list of all the commits for port 1277:

<blockquote><pre class="code">
--
-- Gives you all the commits for a port
--

    SELECT P.id,
           CLP.commit_log_id,
           CLP.port_id,
           CLP.port_version,
           CLP.port_revision,
           CLP.port_epoch,
           CL.commit_date,
           element_pathname(P.element_id)
      FROM ports               P,
           commit_log_ports    CLP,
           commit_log          CL
     WHERE P.id              = 1277
       AND P.id              = CLP.port_id
       AND CLP.port_version != ''
       AND CLP.commit_log_id = CL.id;

  id  | commit_log_id | port_id | port_version | port_revision | port_epoch |      commit_date       |       element_pathname
------+---------------+---------+--------------+---------------+------------+------------------------+-------------------------------
 1277 |         62162 |    1277 | 1.2          | 0             | 0          | 2003-01-02 13:24:08-05 | /ports/devel/mingw-bin-msvcrt
 1277 |         74202 |    1277 | 1.2          | 0             | 0          | 2003-04-05 00:09:40-05 | /ports/devel/mingw-bin-msvcrt
 1277 |         69460 |    1277 | 1.2          | 0             | 0          | 2003-02-21 06:23:04-05 | /ports/devel/mingw-bin-msvcrt
 1277 |         98320 |    1277 | 1.2          | 0             | 0          | 2003-10-15 12:51:15-04 | /ports/devel/mingw-bin-msvcrt
 1277 |        112146 |    1277 | 1.2          | 0             | 0          | 2004-01-29 02:24:56-05 | /ports/devel/mingw-bin-msvcrt
 1277 |         97881 |    1277 | 1.2          | 0             | 0          | 2003-10-13 01:44:08-04 | /ports/devel/mingw-bin-msvcrt
(6 rows)
</pre></blockquote>

<p>
This will give you all the commits that affect a Makefile:

<blockquote><pre class="code">
--
-- Gives you all commits that touch Makefiles
--
  SELECT P.id,
         CLP.commit_log_id,
         CLP.port_id,
         CLP.port_version,
         CLP.port_revision,
         CLP.port_epoch,
         CL.commit_date,
         element_pathname(CLE.element_id),
         CLE.revision_name
    FROM ports               P, 
         commit_log_ports    CLP,
         element             E,
         commit_log_elements CLE,
         commit_log          CL
   WHERE P.id              = 1277
     AND P.id              = CLP.port_id
     AND CLE.commit_log_id = CLP.commit_log_id
     AND CLE.element_id    = E.id
     AND E.name            = 'Makefile'
     AND E.parent_id       = P.element_id
     AND CLP.commit_log_id = CL.id
     AND CLP.port_version != ''
ORDER BY CL.commit_date;

  id  | commit_log_id | port_id | port_version | port_revision | port_epoch |      commit_date       |            element_pathname            | revision_name
------+---------------+---------+--------------+---------------+------------+------------------------+----------------------------------------+---------------
 1277 |         62162 |    1277 | 1.2          | 0             | 0          | 2003-01-02 13:24:08-05 | /ports/devel/mingw-bin-msvcrt/Makefile | 1.5
 1277 |         69460 |    1277 | 1.2          | 0             | 0          | 2003-02-21 06:23:04-05 | /ports/devel/mingw-bin-msvcrt/Makefile | 1.6
 1277 |         97881 |    1277 | 1.2          | 0             | 0          | 2003-10-13 01:44:08-04 | /ports/devel/mingw-bin-msvcrt/Makefile | 1.7
 1277 |         98320 |    1277 | 1.2          | 0             | 0          | 2003-10-15 12:51:15-04 | /ports/devel/mingw-bin-msvcrt/Makefile | 1.8
</pre></blockquote>

<p>
What we need is a join of the two result sets:

<blockquote><pre class="code">
--
-- Gives you all commits and mentions makefile revisions
--

--
-- Gives you all the commits for a port
--

    SELECT C.commit_log_id,
           C.port_id,
           C.port_version,
           C.port_revision,
           C.port_epoch,
           C.commit_date,
           C.pathname,
           M.revision_name
FROM
    (SELECT P.id,
           CLP.commit_log_id,
           CLP.port_id,
           CLP.port_version,
           CLP.port_revision,
           CLP.port_epoch,
           CL.commit_date,
           element_pathname(P.element_id) as pathname
      FROM ports               P,
           commit_log_ports    CLP,
           commit_log          CL
     WHERE P.id              = 1277
       AND P.id              = CLP.port_id
       AND CLP.port_version != ''
       AND CLP.commit_log_id = CL.id) AS C left outer join

(  SELECT P.id,
         CLP.commit_log_id,
         CLP.port_id,
         CLP.port_version,
         CLP.port_revision,
         CLP.port_epoch,
         CL.commit_date,
         element_pathname(CLE.element_id),
         CLE.revision_name
    FROM ports               P, 
         commit_log_ports    CLP,
         element             E,
         commit_log_elements CLE,
         commit_log          CL
   WHERE P.id              = 1277
     AND P.id              = CLP.port_id
     AND CLE.commit_log_id = CLP.commit_log_id
     AND CLE.element_id    = E.id
     AND E.name            = 'Makefile'
     AND E.parent_id       = P.element_id
     AND CLP.commit_log_id = CL.id
     AND CLP.port_version != '') AS M

on (M.commit_log_id = C.commit_log_id)

order by C.commit_date;

 commit_log_id | port_id | port_version | port_revision | port_epoch |      commit_date       |           pathname            | revision_name
---------------+---------+--------------+---------------+------------+------------------------+-------------------------------+---------------
         62162 |    1277 | 1.2          | 0             | 0          | 2003-01-02 13:24:08-05 | /ports/devel/mingw-bin-msvcrt | 1.5
         69460 |    1277 | 1.2          | 0             | 0          | 2003-02-21 06:23:04-05 | /ports/devel/mingw-bin-msvcrt | 1.6
         74202 |    1277 | 1.2          | 0             | 0          | 2003-04-05 00:09:40-05 | /ports/devel/mingw-bin-msvcrt |
         97881 |    1277 | 1.2          | 0             | 0          | 2003-10-13 01:44:08-04 | /ports/devel/mingw-bin-msvcrt | 1.7
         98320 |    1277 | 1.2          | 0             | 0          | 2003-10-15 12:51:15-04 | /ports/devel/mingw-bin-msvcrt | 1.8
        112146 |    1277 | 1.2          | 0             | 0          | 2004-01-29 02:24:56-05 | /ports/devel/mingw-bin-msvcrt |
(6 rows)
</pre></blockquote>

<p>
Now we can parse that, fetching the Makefile we need.

<p>
Points to consider:

<ul>
<li>Master ports:
<blockquote><pre class="code">
freshports.org=# select count(*) from ports where portepoch != '0';
 count
-------
   246
(1 row)

freshports.org=# select count(*) from ports where portepoch != '0' and master_port != '';
 count
-------
    27
(1 row)

freshports.org=#
</pre></blockquote>

PORTEPOCH may be set in the master port.

<li>I dunno, there might be something else I've not thought of yet.
</ul>

<h2>23 September 2004 - Ports that don't set their own EPOCH</h2>

<p>
I have the simple script working now.  See output at
<a href="http://beta.freshports.org/tmp/epoch-fetching-slave.txt">http://beta.freshports.org/tmp/epoch-fetching-slave.txt</a>.
There are issues..

<p>
That page lists 
the ports that have a PORTEPOCH, the commits for that port, and the historical 
value of the PORTEPOCH value for that commit.  I do this by literally fetching 
each revision of the Makefile.  FreshPorts knows that revision is associated with 
each commit (that information is in the cvs-all email0.

<p>
Obtaining the PORTEPOCH values is not a simple grep command.  You must do a 
"make -V PORTVERSION". There are 27 ports containing an EPOCH value that are
 also slave ports.  Of these 27, two set their own EPOCH value, the other 25
 get it from the MASTERPORT.  It is thoese 25 ports which are going to be 
tougher.  There are 15 distinct master ports involved (fortunately, none 
of them have their own MASTERPORTs).

<p>
This query returns the master ports mentioned above:

<blockquote><pre class="code">
  SELECT P.id,
         C.name || '/' || E.name as portname,
         P.master_port,
         P.portepoch
    FROM categories C, element E, ports P JOIN
(  SELECT distinct pathname_id('/ports/' || master_port) as mp_element_id
    FROM ports P, element E
   WHERE P.portepoch != '0'
     AND P.element_id = E.id
     AND P.master_port != '') MP
   ON P.element_id = mp_element_id
WHERE E.id = P.element_id
  AND P.category_id  = C.id;
</pre></blockquote>

<p>
Some of those ports in the sub select will
have EPOCH set in their own Makefile.  I found three.  See
<a href="http://beta.freshports.org/tmp/epoch-masters.txt">http://beta.freshports.org/tmp/epoch-masters.txt</a>.


<p>
I'm not yet sure how I'm going to cope with these master ports.  The others
 should be straight forward.  I could take the scripts/Verify/set-historical-epoch.pl
and use the query above.

<p>
[1] FWIW, there are 246 ports with a PORTEPOCH value.  This differs from the 
result of this command, perhaps because not all such ports are in the INDEX 
I'm using (e.g. archivers/bsdtar)

<blockquote><pre class="code">
awk -F\| '$1 ~ /,/ {print $2 "/Makefile"}' /usr/ports/INDEX-
</pre></blockquote>
<hr>
<p align="right">
<small>Last amended: 22 September 2004</small>

</body>
</html>
