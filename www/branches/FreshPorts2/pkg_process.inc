<?
	# $Id: pkg_process.inc,v 1.5.2.5 2002-02-27 20:13:24 dan Exp $
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

	$starttime = time();
	$portcount = 0;
	while (!feof($gFid)) {
		$buffer = fgets($gFid,4096);
		if (!$buffer) {
			continue;
		}

		$portcount++;
		// Get the package name


		$buffer = trim($buffer);
		if (IsSet($PortArray[$buffer])) {
			$PortArray[$buffer] = $PortArray[$buffer] + 1;
		} else {
			$PortArray[$buffer] = 1;
		}

	}

	pg_exec ($dbh, "begin");

	while (list ($key, $count) = each ($PortArray)) {

		echo "$key<BR>\n";
		# trimming, just in case...
		$CategoryPort = explode("/", trim($key));
		if (count($CategoryPort) != 2) {
			echo "OUCH, more than two values found (" . count($CategoryPort) . ")...";
			print $CategoryPort . "<BR>\n";
		}

		$Category = $CategoryPort[0];
		$Port     = $CategoryPort[1];

		$sql = "select WatchListStagingAddItem($WatchListID, '$Category', '$Port', $count)";
		echo "\$sql = '$sql'\n<BR>";
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

#	# now add in the stuff from the watch list which isn't in the pkg_info
#	$sql = "select WatchListStagingMergeWatchList($WatchListID)";
#	$result = pg_exec($dbh, $sql);
#	if ($result && pg_numrows($result)) {
#		# all is well, do nothing
#	} else {
#		pg_errormessage() . ' sql = $sql';
#		exit;
#	}
#
	pg_exec ($dbh, "commit");

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
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id, 
					watch_list_element.element_id AS onwatchlist
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_element
						ON  watch_list_staging.element_id    = watch_list_element.element_id
						AND watch_list_staging.watch_list_id = watch_list_element.watch_list_id
			  WHERE $WatchListID = watch_list_staging.watch_list_id
			    AND item_count   = 1
				AND watch_list_staging.element_id IS NOT NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
			<TR><TD NOWRAP><B>Port name</B></TD><TD><B>Category</B></TD><TD COLSPAN="2"><B>Source</B></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);
				echo '<TR><TD NOWRAP>';
				echo '<A HREF="/' . $row["category"] . '/' . $row["port"] . '">' . $row["category"] . '/' . $row["port"] . '</A></TD>';

				echo '<TD ALIGN="center">';
				if ($row["from_pkg_info"]   == 't') {
					echo 'P';
				} else {
					echo '&nbsp;';
				}
				echo '</TD>';


				echo '<TD ALIGN="center">';
				if ($row["onwatchlist"]) {
					echo 'W';
				} else {
					echo '&nbsp;';
				}
				echo '</TD>';

				echo '</TR>';
			}
			
			echo '</TABLE>';

			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "Nothing you uploaded was found within our database.";
		}
	} else {
		echo "nothing found!";
	}

}

function UploadDisplayStagingResultsMatchesNo($WatchListID, $dbh) {

	$begintime = time();
	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			   FROM	watch_list_staging
			  WHERE $WatchListID = watch_list_staging.watch_list_id
			    AND item_count   = 1
				AND watch_list_staging.element_id IS NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
			<TR><TD><B>Port name</B></TD><TD><B>Status</B></TD><TD COLSPAN="2"><B>Source</B></TD></TR>

			<?
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
			echo '</TABLE>';

			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "Everything in your uploaded list was located within our database.";
		}
	} else {
		echo "nothing found!";
	}

}

function UploadDisplayStagingResultsMatchesDuplicates($WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_element
						ON  watch_list_staging.element_id    = watch_list_element.element_id
						AND watch_list_staging.watch_list_id = watch_list_element.watch_list_id
			  WHERE $WatchListID = watch_list_staging.watch_list_id
			    AND item_count   > 1
				AND watch_list_staging.element_id IS NOT NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="2" CELLSPADDING="3">
			<TR><TD><B>Port name</B></TD><TD><B>Count</B></TD><TD COLSPAN="2"><B>Source</B></TD></TR>

			<?
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
			echo '</TABLE>';

			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "no Duplicates found";
		}
	} else {
		echo "nothing found!";
	}

}

?>
