<?
	# $Id: pkg_process.inc,v 1.5.2.2 2002-02-26 00:31:17 dan Exp $
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
	$starttime = time();
	// }}}
	global $gDBG;

	$ok			= true;
	$gMyPorts	= array();
	$tempArray	= array();

	$gFid = fopen($filename,'r');
	if (!$gFid) { return false; }

	$begintime = time();
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

		$sql = "select WatchListStagingAddItem($WatchListID, '$pkg', '$base')";
#		echo "\$sql = '$sql'\n<BR>";
		$result = pg_exec($dbh, $sql);
		if ($result && pg_numrows($result)) {
			# do nothing.  All is well
		} else {
			pg_errormessage() . ' sql = $sql';
			exit;
		}
	}

	$sql = "select WatchListStagingProcess($WatchListID)";
	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		# do nothing.  All is well
	} else {
		pg_errormessage() . ' sql = $sql';
		exit;
	}


	
	$sql = " SELECT	port_name                             as port, 
					watch_list_staging_results.element_id as element_id, 
					categories.name                       as category,
					CASE when watch_list_element.element_id is null
						then 0
						else 1
					END as onwatchlist
			   FROM	watch_list_staging, watch_list_staging_results, categories, 
					ports LEFT OUTER JOIN watch_list_element on ports.element_id = watch_list_element.element_id
			  WHERE watch_list_staging.id = watch_list_staging_results.watch_list_staging_id
			    AND ports.element_id      = watch_list_staging_results.element_id
			    AND ports.category_id     = categories.id
			    AND $WatchListID          = watch_list_staging.watch_list_id
			  ORDER BY port_name, categories.name";

#	echo $sql;

	$result = pg_exec($dbh, $sql);
	echo '<TABLE>';
	if ($result) {
		$numrows = pg_numrows($result);
		for ($i = 0; $i < $numrows; $i++) {
			$row = pg_fetch_array($result, $i);
			echo '<TR><TD>' . $row["port"] . '</TD><TD>' . $row["category"] . '</TD><TD>';

#			echo $row["onwatchlist"];
			if ($row["onwatchlist"]) {
				echo 'on watch list already';
			}

			echo '</TD></TR>';
			
		}
	} else {
		echo "nothing found!";
	}

	echo '</TABLE>';

	echo 'that took '. (time() - $begintime) . ' seconds to process ' . $portcount . ' ports';

	return false;  #***********************************

	// profiling info {{{
	$endtime = time();
	$total = $endtime - $starttime;
	print("Total Time Processing:  $total seconds<BR>\n");
	// }}}
	return $returnArray;
}
// }}}
?>
