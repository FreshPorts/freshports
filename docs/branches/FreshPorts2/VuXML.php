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
provided in <a href="http://cvsweb.freebsd.org/ports/security/vuxml/vuln.xml?rev=.">ports/security/vuxml/vuln.xml</a>
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

<h2>Marking the commits</h2>

<p>
Technically speaking, we are not marking the commits.  We are marking the 
commit history of parts that have been affected by a given VuXML entry.

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

<p>
There is room for optimization here.  Some notes based upon first impressions.

<ul>
<li>The combination of port_version, port_revision, and port_epoch will be 
known as the <b>PackageVersion</b>.
<li>2 - Select distinct on <b>PackageVersion</b>.  This will
reduce the number of rows fetched and thereby the number of calls to 
pkg_version.
<p>
Here is the basic data for one package:

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
We might be able to reduce the number of rows with this approach:

<blockquote><pre class="code">
freshports.org=# SELECT distinct port_id, port_version, port_revision, port_epoch
freshports.org-#   FROM commit_log_ports
freshports.org-#  WHERE port_id = (SELECT id
freshports.org(#                     FROM ports
freshports.org(#                    WHERE package_name = 'leafnode');
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
</pre></blockquote>

<li>4 - the test is done by invoking pkg_version -t and testing the result.
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

<p>
That's it.  Sounds simple.  Right?

</body>
</html>
