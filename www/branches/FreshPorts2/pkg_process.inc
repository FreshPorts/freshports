<?
	# $Id: pkg_process.inc,v 1.5.2.1 2002-02-24 17:13:25 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#

#	require($DOCUMENT_ROOT . "/include/common.php");
#	require($DOCUMENT_ROOT . "/include/freshports.php");
#	require($DOCUMENT_ROOT . "/include/databaselogin.php");	
	require($DOCUMENT_ROOT . "/../classes/ports.php");	

$Debug=0;


// {{{ function SplitPackage($pkg,$arr)
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
//}}}
// {{{ function ReadINDEX()
/**
* Read in the INDEX file
*
* @return The index array
*
*/
function ReadINDEX() {
	# Get the latest from fetch ftp://ftp.freebsd.org/pub/FreeBSD/ports/ports/INDEX
	# as needed

	GLOBAL $DOCUMENT_ROOT;

	$gIndexFile = $DOCUMENT_ROOT . "/../configuration/INDEX";
	$iFid = fopen($gIndexFile,'r');
	if (!$iFid) { return false; }
	while(!feof($iFid)) {
		$buffer = fgets($iFid,4096);
		list ($pkg,$port,$rest) = preg_split("/\|/",$buffer,3);
		if (!$pkg) { 
			continue;
		}

		$rv = array();
		SplitPackage($pkg, $rv);
		if (count($rv) == 0) {
			echo ("Error Parsing INDEX file");
			return false;
		}

		$pkg  = strtoupper($pkg);
		$base = strtoupper($rv[1]);
	
		$indexArray[$pkg]['PORT'] = substr($port,11);
		//$indexArray[$pkg]['BASE'] = $base;
		// Also index the basename of the port for use when versions do not match
		$indexArray[$base][] = $indexArray[$pkg];
	}
	return $indexArray;
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
function ProcessPackages($filename, $userID, $clean, $dbh) {
	// profiling info {{{
	$starttime = time();
	// }}}
	global $gDBG;

	$ok = true;
#	$indexArray = ReadINDEX();
#	if (!$indexArray) { return false; }
	$gMyPorts = array();
	$tempArray = array();

	$gFid = fopen($filename,'r');
	if (!$gFid) { return false; }

    $port = new Port($dbh);

	echo '<TABLE>';

	$begintime = time();
	$portcount=0;
	while(!feof($gFid)) {
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

		echo "<TR><TD>\$pkg = '$pkg'</TD><TD>\$base = '$base'</TD><TD>";

		$numports = $port->FetchByName("'$base'");

		echo "\$numports='$numports' ";
		switch ($numports) {
			case 0:
				echo 'no port found';
				break;

			case 1:
				$port->FetchNth(0);
				echo 'found port id = ' . $port->{id};
				break;

			default:
				echo 'lots found';
		}

		echo '</TD><TD>' . $starttime . '</TD><TD>' . time() . '</TD><TD>' . (time() - $starttime) . '</TD></TR>';
			
		// if our package is in the INDEX and we haven't already found it
###		$port = 'ports/' . $indexArray[$pkg]['PORT'];
###		if ($indexArray[$pkg] && !in_array($port,$gMyPorts)) {
#			$gMyPorts[] = $port;
###		} elseif ($base && $indexArray[$base]) {
#			// The package is not in the INDEX.  Maybe our version is out dated.
#			// Check for the basename in the INDEX.
#	
#			// If there is only one base match, then assume that's what they want
###			if (count($indexArray[$base]) == 1 ) {
###				$port = 'ports/' . $indexArray[$base][0]['PORT'];
#				if (!in_array($port,$gMyPorts)) {
#					$gMyPorts[] = $port;
#				}
#			} else {
#				// There are multiple ports with the same basename, add them to the guess list
###				foreach($indexArray[$base] as $port) {
#					if(!in_array($port['PORT'],$gMyPorts) && !in_array($port['PORT'],$tempArray)) {
#						$tempArray[] = $port['PORT'];
#					}
#				}
#			}
#		}

	}
	echo '</TABLE>';

	echo 'that took '. (time() - $begintime) . ' seconds to process ' . $portcount . ' ports';

	return false;  #***********************************

	// Now remove duplicates from the tempArray
	foreach ($tempArray as $port) {
		$port = 'ports/' . $port;
		if ( !in_array($port, $gMyPorts) ) {
			$guessPorts[] = $port;
		}
	}
	asort($gMyPorts);
	asort($guessPorts);
	$returnArray['FOUND'] = $gMyPorts;
	$returnArray['GUESS'] = $guessPorts;

	$ok = GetWatchID($userID, $watchID);
	if (!$ok) {
		echo "Could not find watch_id for user $userID<BR>\n";
		return false;
	}

#echo "obtained WatchID of '$watchID'<BR>\n";

	if ($clean) {
		$ok = CleanPorts($watchID);
	}

	$ok = $ok && AddPortsToList(array_merge($gMyPorts,$guessPorts), $watchID);
	if (!$ok) {
		echo "Error adding ports.<BR>\n";
	  	if ($gDBG != 1) {
			echo "Rerun with the '-d' option for details.<BR>\n";
		}
	}

	// profiling info {{{
	$endtime = time();
	$total = $endtime - $starttime;
	print("Total Time Processing:  $total seconds<BR>\n");
	// }}}
	return $returnArray;
}
// }}}
?>
