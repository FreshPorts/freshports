<?
	# $Id: pkg_process.inc,v 1.5.2.3 2002-02-27 02:25:07 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#

#	require($DOCUMENT_ROOT . "/include/common.php");
#	require($DOCUMENT_ROOT . "/include/freshports.php");
#	require($DOCUMENT_ROOT . "/include/databaselogin.php");	
	require($DOCUMENT_ROOT . "/../classes/ports.php");	

$Debug=0;

// {{{ function SplitPackage($pkg, $arr)
/**
* Take a package name (basename-version) and split it 
* @param
* @return
*
*/
function SplitPackage($pkg, &$arr) {

	if (preg_match("/(.+)-(.*)/",$pkg,$arr)) {
		return $arr;
	}

	$arr[] = $pkg;
	return $arr;
}

// }}}
// {{{ function ProcessPackages
/**
* Process the package file (this will be slimmed down, when there is no more debug
*
* @param	$filename - name of file to process
* @return	an array of ports
*
*/
function ProcessPackages($WatchListID, $filename, $userID, $clean, $dbh) {
	// profiling info {{{
	// }}}
	global $gDBG;

	$ok			= true;
	$gMyPorts	= array();
	$tempArray	= array();

	$gFid = fopen($filename,'r');
	if (!$gFid) { return false; }

	$portcount = 0;
	while (!feof($gFid)) {
		$buffer = fgets($gFid,4096);
		if (!$buffer) {
			continue;
		}

		$portcount++;
		$starttime = time();
		// Get the package name
		$pkg = array_shift(preg_split("/[\s]+/",$buffer,2));
	
		$rv = array();
		SplitPackage($pkg,$rv);
		if (count($rv) == 0) {
			return false;
		}
		$base = $rv[1];


		if (IsSet($PortArray[$base])) {
			$PortArray[$base] = $PortArray[$base] + 1;
		} else {
			$PortArray[$base] = 1;
		}

	}

	pg_exec ($dbh, "begin");

	while (list ($port_name, $count) = each ($PortArray)) {

		$sql = "select WatchListStagingAddItem($WatchListID, '$port_name', $count)";
#		echo "\$sql = '$sql'\n<BR>";
		$result = pg_exec($dbh, $sql);
		if ($result && pg_numrows($result)) {
			# do nothing
		} else {
			pg_errormessage() . ' sql = $sql';
			exit;
		}
	}

#	pg_exec ($dbh, "commit");
#	pg_exec ($dbh, "begin");

	# All is well.

				
	$sql = "select WatchListStagingProcess($WatchListID)";
	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		# do nothing.  All is well
	} else {
		pg_errormessage() . ' sql = $sql';
		exit;
	}

	# now add in the stuff from the watch list which isn't in the pkg_info
	$sql = "select WatchListStagingMergeWatchList($WatchListID)";
	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		# all is well, do nothing
	} else {
		pg_errormessage() . ' sql = $sql';
		exit;
	}

	pg_exec ($dbh, "commit");

	UploadDisplayStagingResults($WatchListID, $dbh);	

	return false;  #***********************************

	// profiling info {{{
	$endtime = time();
	$total = $endtime - $starttime;
	print("Total Time Processing:  $total seconds<BR>\n");
	// }}}
	return $returnArray;
}
// }}}

function UploadDisplayStagingResultsMatches($WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT	port_name                             as port, 
					watch_list_staging_results.element_id as element_id, 
					from_pkg_info,
					count_on_pkg_info,
					from_watch_list
			   FROM	watch_list_staging, watch_list_staging_results
			  WHERE $WatchListID          = watch_list_staging.watch_list_id
			    AND watch_list_staging.id = watch_list_staging_results.watch_list_staging_id
			    AND count_on_pkg_info     = 1
			  ORDER BY port";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	?>

	<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
	<TR><TD><B>Port name</B></TD><TD><B>Status</B></TD><TD COLSPAN="2"><B>Source</B></TD><TD>&nbsp;</TD></TR>

	<?
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$row = pg_fetch_array($result, $i);
			echo '<TR><TD>' . $row["port"] . '</TD>';

			echo '<TD>';
			if ($row["element_id"] == '') {
				echo '<FONT COLOR="red">not found</FONT>';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';

			echo '<TD ALIGN="center">';
			if ($row["from_pkg_info"]   == 't') {
				echo 'P';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';


			echo '<TD ALIGN="center">';
			if ($row["from_watch_list"] == 't') {
				echo 'W';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';

			echo '</TR>';
			
		}
	} else {
		echo "nothing found!";
	}

	echo '</TABLE>';

	echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
}

function UploadDisplayStagingResultsMatchesNo($WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT	port_name                             as port, 
					watch_list_staging_results.element_id as element_id, 
					from_pkg_info,
					count_on_pkg_info,
					from_watch_list
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_staging_results
										ON watch_list_staging.id = watch_list_staging_results.watch_list_staging_id
			  WHERE $WatchListID = watch_list_staging.watch_list_id
			    AND watch_list_staging_results.element_id IS NULL
			  ORDER BY port";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	?>

	<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
	<TR><TD><B>Port name</B></TD><TD><B>Status</B></TD><TD COLSPAN="2"><B>Source</B></TD><TD>&nbsp;</TD></TR>

	<?
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$row = pg_fetch_array($result, $i);
			echo '<TR><TD>' . $row["port"] . '</TD>';

			echo '<TD>';
			if ($row["element_id"] == '') {
				echo '<FONT COLOR="red">not found</FONT>';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';

			echo '<TD ALIGN="center">';
			if ($row["from_pkg_info"]   == 't') {
				echo 'P';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';


			echo '<TD ALIGN="center">';
			if ($row["from_watch_list"] == 't') {
				echo 'W';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';

			echo '</TR>';
			
		}
	} else {
		echo "nothing found!";
	}

	echo '</TABLE>';

	echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
}

function UploadDisplayStagingResultsMatchesDuplicates($WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT	port_name                             as port, 
					watch_list_staging_results.element_id as element_id, 
					from_pkg_info,
					count_on_pkg_info,
					from_watch_list
			   FROM	watch_list_staging, watch_list_staging_results
			  WHERE $WatchListID          = watch_list_staging.watch_list_id
			    AND watch_list_staging.id = watch_list_staging_results.watch_list_staging_id
			    AND count_on_pkg_info     > 1
			  ORDER BY port";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	?>

	<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
	<TR><TD><B>Port name</B></TD><TD><B>Count</B></TD><TD COLSPAN="2"><B>Source</B></TD><TD>&nbsp;</TD></TR>

	<?
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$row = pg_fetch_array($result, $i);
			echo '<TR><TD>' . $row["port"] . '</TD>';

			echo '<TD ALIGN="center">';
			echo $row["count_on_pkg_info"];
			echo '</TD>';

			echo '<TD ALIGN="center">';
			if ($row["from_pkg_info"]   == 't') {
				echo 'P';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';


			echo '<TD ALIGN="center">';
			if ($row["from_watch_list"] == 't') {
				echo 'W';
			} else {
				echo '&nbsp;';
			}
			echo '</TD>';

			echo '</TR>';
			
		}
	} else {
		echo "nothing found!";
	}

	echo '</TABLE>';

	echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
}

?>
