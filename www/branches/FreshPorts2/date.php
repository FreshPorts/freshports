<?
	# $Id: date.php,v 1.1.2.1 2002-11-27 20:21:46 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");

	require($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");
	require($_SERVER['DOCUMENT_ROOT'] . "/../classes/commits.php");

	freshports_Start("the place for ports",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
	$Debug=0;

	$Date = AddSlashes($_GET["date"]);
	if ($Date == '' || strtotime($Date) == -1) {
		$Date = date("Y-m-d");
	}

?>
<html>
<body>

<?php

echo "That date is " . $Date . '<br>';
echo 'which is ' . strtotime($Date);

$commits = new Commits($db);
$commits->Fetch($Date);

?>



<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>


</body>
</html>


</body>
</html>