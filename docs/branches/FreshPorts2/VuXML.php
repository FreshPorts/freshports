<html>
<head>
<title>FreshPorts - VuXML strategy</title>
<body>
<p>
This page documents the strategy used when processing 
<a href="http://www.vuxml.org/freebsd/">VuXML</a> data.

<p>
The goal is to flag records [within the Commit History for a given port] that
are affected by a VuXML entry.  The key to this is the ability to parse the XML
provided in <a href="http://cvsweb.freebsd.org/ports/security/vuxml/vuln.xml?rev=.">ports/security/vuxml/vuln.xml</a>.
Matthew Seaman has written a script (scripts/vuxml_parsing.pm) that provides us
with the information we need.

<p>
The data within the VuXML file is parsed only for package information. We do
not collected operating system information.

<p>
We have taken the initial approach that we will delete all previous VuXML
information when processing a newly committed vuln.xml file.  This simplifies
the code.  We may wish to rethink this approach at a later date.

<p>
Once the data is loaded into FreshPorts, we can obtain the names and ranges
like this:

<blockquote><pre class="code">
freshports.org=# SELECT V.vid,
freshports.org-#        VN.name,
freshports.org-#        VR.range_version_start  AS v1,
freshports.org-#        VR.range_operator_start AS op1,
freshports.org-#        VR.range_operator_end   AS v2,
freshports.org-#        VR.range_version_end    AS op2
freshports.org-#   FROM vuxml_ranges VR, vuxml_names VN, vuxml_affected VA, vuxml V
freshports.org-#  WHERE V.id                 = VA.vuxml_id
freshports.org-#    AND VN.vuxml_affected_id = VA.id
freshports.org-#    AND VR.vuxml_name_id     = VN.ID
freshports.org-#  ORDER BY V.ID, VN.name;
                 vid                  |            name             |    v1     | op1 | v2 |          op2
--------------------------------------+-----------------------------+-----------+-----+----+-----------------------
 253ea131-bd12-11d8-b071-00e08110b673 | gallery                     |           |     | lt | 1.4.3.2
 6f955451-ba54-11d8-b88c-000d610a3b12 | squid                       |           |     | lt | 2.5.5_9
 f7a3b18c-624c-4703-9756-b6b27429e5b0 | leafnode                    | 1.9.20    | ge  | lt | 1.9.30
 7b0208ff-3f65-4e16-8d4d-48fd9851f085 | leafnode                    | 1.9.3     | ge  | le | 1.9.41
 a051a4ec-3aa1-4dd1-9bdc-a61eb5700153 | leafnode                    |           |     | le | 1.9.47
 5d36ef32-a9cf-11d8-9c6d-0020ed76ef5a | subversion                  |           |     | lt | 1.0.2_1
 8d075001-a9ce-11d8-9c6d-0020ed76ef5a | neon                        |           |     | lt | 0.24.5_1...
</pre></blockquote>

<p>
There are about 230 such rows at the time of writing.

<p>
The approach we will take to flagging commit history records will be to read
the above rows into a script, then, for each name found, read all the relevant
commit history entries, test the version, and mark the commits as necessary.

<p>
The psuedo code for this might be:

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
known as the package version.
<li>2 - Select distinct on package version.  This will
reduce the number of rows fetched and thereby the number of calls to 
pkg_version.
<p>
Here is the basic data for one package:

<blockquote><pre class="code">
freshports.org=# SELECT *
freshports.org-#   FROM commit_log_ports
freshports.org-#  WHERE port_id = (SELECT id
freshports.org(#                     FROM ports
freshports.org(#                    WHERE package_name = 'tnftpd');
 commit_log_id | port_id | needs_refresh | port_version | port_revision | port_epoch | package_name
---------------+---------+---------------+--------------+---------------+------------+--------------
        108327 |   11386 |             0 | 2.0b3        | 0             |            |
        111249 |   11386 |             0 | 2.0b3        | 1             |            |
        111250 |   11386 |             0 | 2.0b3        | 1             |            |
        111673 |   11386 |             0 | 2.0b3        | 1             |            |
        113007 |   11386 |             0 | 20031217     | 0             |            |
        120146 |   11386 |             0 | 20031217     | 0             |            |
        127931 |   11386 |             0 | 20031217     | 1             |            |
        139235 |   11386 |             0 | 20031217     | 1             |            |
        140506 |   11386 |             0 | 20031217     | 1             |            |
        140776 |   11386 |             0 | 20040810     | 0             |            |
(10 rows)
</pre></blockquote>

<p>
We might be able to reduce the number of rows with this approach:

<blockquote><pre class="code">
freshports.org=# SELECT distinct port_id, port_version, port_revision, port_epoch
freshports.org-#   FROM commit_log_ports
freshports.org-#  WHERE port_id = (SELECT id
freshports.org(#                     FROM ports
freshports.org(#                    WHERE package_name = 'tnftpd');
 port_id | port_version | port_revision | port_epoch
---------+--------------+---------------+------------
   11386 | 2.0b3        | 0             | 0
   11386 | 2.0b3        | 1             | 0
   11386 | 20031217     | 0             | 0
   11386 | 20031217     | 1             | 0
   11386 | 20040810     | 0             | 0
(5 rows)
</pre></blockquote>

<li>4 - the test is done by invoking pkg_version -t and testing the result.
<li>5 - store each vid affecting this package version.
<li>7 - when updating commit_log_ports_vuxml, something like this might be
useful:
<blockquote><pre class="code">
INSERT INTO commit_log_ports_vuxml (commit_log_id, port_id, vuxml_id)
SELECT commit_log_id,
       port_id,
       VUXML_ID
  FROM commit_log_ports
 WHERE port_id       = PORT_ID
   AND port_version  = PORT_VERSION
   AND port_revision = PORT_REVISION
   AND port_epoch    = PORT_EPOCH;
</pre></blockquote>
</ul>



</body>
</html>
