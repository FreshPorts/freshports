<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');

	freshports_ConditionalGet(freshports_LastModified());

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<h1>FreshPorts Encoding</h1>
<p>
The problem is well demonstrated at <a href="http://beta.freshports.org/deskutils/kompose/">http://beta.freshports.org/deskutils/kompose/</a>.

<p>
Note:

<ol>
<li>The short description at the top contains Expos(tm)
<li>The first commit, at the boottom of the page, contains Expose(tm)
</ol>
<p>
I have used simple ASCII in the above notes to simplify things.

<p>
The short description is encoded as ISO-8859-15.  The commit is encoded
as UTF-8.

<p>
The short description is obtained from a "make -V COMMENT".

<p>
The commit messages arrives via an XML file encoded with ISO-8859-1.

<h2>Test iconv output</h2>

<?php

$str = "Full-screen task manager similar to Exposé(tm)";

echo "<p>$str</p>\n";

echo "<p>" . iconv("ISO-8859-15", "UTF-8", $str) . "</p>\n";
echo "<p>" . htmlentities($str) . "</p>\n";


$str = "Antônio Carlos Venâncio Júnior";

echo "<p>$str</p>\n";

echo "<p>" . iconv("ISO-8859-1", "UTF-8", $str) . "</p>\n";
echo "<p>" . htmlentities($str) . "</p>\n";




$Commit = new Commit($db);
$Commit->FetchByMessageId('200408252135.i7PLZjrc085124@repoman.freebsd.org');

echo "<pre>$Commit->commit_description</pre>\n";


$Port = new Port($db);
$Port->Fetch('deskutils', 'kompose');

echo "<pre>$Port->short_description</pre>\n";
echo "<pre>" . iconv("ISO-8859-15", "UTF-8", $Port->short_description) . "</pre>\n";


?>
The end
</body>
</head>