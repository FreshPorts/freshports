<?
// function SplitPackage($pkg,$arr) {{{
/**
* Take a package name (basename-version) and split it 
* @param
* @return
*
*/
function SplitPackage($pkg,&$arr) {
	if (preg_match("/(.+)-(.*)/",$pkg,$arr)) {
		return $arr;
	}
	$arr[] = $pkg;
	return $arr;
}/*}}}*/
// function ReadINDEX() {{{
/**
* Read in the INDEX file
*
* @return The index array
*
*/
function ReadINDEX() {
	# Get the latest from fetch ftp://ftp.freebsd.org/pub/FreeBSD/ports/ports/INDEX
	# as needed
	$gIndexFile = "./INDEX";
	$iFid = fopen($gIndexFile,'r');
	if (!$iFid) { return false; }
	while(!feof($iFid)) {
		$buffer = fgets($iFid,4096);
		list ($pkg,$port,$rest) = preg_split("/\|/",$buffer,3);
		if (!$pkg) { 
			continue;
		}

		$rv = array();
		SplitPackage($pkg,$rv);
		if ( count($rv) == 0) {
			echo ("Error Parsing INDEX file");
			return false;
		}
		$pkg = strtoupper($pkg);
		$base = strtoupper($rv[1]);
	
		$indexArray[$pkg]['PORT'] = substr($port,11);
		//$indexArray[$pkg]['BASE'] = $base;
		// Also index the basename of the port for use when versions do not match
		$indexArray[$base][] = $indexArray[$pkg];
	}
	return $indexArray;
}
// }}}
// function ProcessPackages {{{
/**
* Process the package file (this will be slimmed down, when there is no more debug
*
* @param	$filename - name of file to process
* @return	an array of ports
*
*/
function ProcessPackages($filename,$userID,$clean) {
	// profiling info {{{
	$starttime = time();
	// }}}
	global $gDBG;

	$ok = true;
	$indexArray = ReadINDEX();
	if (!$indexArray) { return false; }
	$gMyPorts = array();
	$tempArray = array();

	$gFid = fopen($filename,'r');
	if (!$gFid) { return false; }

	while(!feof($gFid)) {
		$buffer = fgets($gFid,4096);
		if (!$buffer) {
			continue;
		}
		// Get the package name
		$pkg = strtoupper(array_shift(preg_split("/[\s]+/",$buffer,2)));
	
		$rv = array();
		SplitPackage($pkg,$rv);
		if ( count($rv) == 0) {
			return false;
		}
		$base = $rv[1];
			
		// if our package is in the INDEX and we haven't already found it
		$port = 'ports/' . $indexArray[$pkg]['PORT'];
		if ($indexArray[$pkg] && !in_array($port,$gMyPorts)) {
			$gMyPorts[] = $port;
		} elseif ($base && $indexArray[$base]) {
			// The package is not in the INDEX.  Maybe our version is out dated.
			// Check for the basename in the INDEX.
	
			// If there is only one base match, then assume that's what they want
			if (count($indexArray[$base]) == 1 ) {
				$port = 'ports/' . $indexArray[$base][0]['PORT'];
				if (!in_array($port,$gMyPorts)) {
					$gMyPorts[] = $port;
				}
			} else {
				// There are multiple ports with the same basename, add them to the guess list
				foreach($indexArray[$base] as $port) {
					if(!in_array($port['PORT'],$gMyPorts) && !in_array($port['PORT'],$tempArray)) {
						$tempArray[] = $port['PORT'];
					}
				}
			}
		}
	}

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

	$ok = GetWatchID($userID,$watchID);
	if (!$ok) {
		echo "Could not find watch_id for user $userID\n";
		return false;
	}

	if ($clean) {
		$ok = CleanPorts($watchID);
	}

	$ok = $ok && AddPortsToList(array_merge($gMyPorts,$guessPorts),$userID);
	if (!$ok) {
		echo "Error adding ports.\n";
	  	if ($gDBG != 1) {
			echo "Rerun with the '-d' option for details.\n";
		}
	}

	// profiling info {{{
	$endtime = time();
	$total = $endtime - $starttime;
	print("Total Time Processing:  $total seconds");
	// }}}
	return $returnArray;
}
// }}}
?>
