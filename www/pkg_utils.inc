<?
	#
	# $Id: pkg_utils.inc,v 1.6 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

$Debug=0;

function dbgonly($s) {
	global $gDBG;
	return ($gDBG) ? $s : '';
}
//function pp($r) {
//    return print_r($r);
//}
//function ppp($r) {
//    return  dbgonly("<pre>DBG: " . pp($r) . "</pre>");
//}
function eppp($r) {
	global $gDBG;
	if ($gDBG) {
		print_r($r);
	}
}
function epp($s) {
	echo dbgonly("<pre>DBG: $s</pre>");
}

// {{{ function &DBConnect()
/**
*
* @param
* @return
*
*/
function &DBConnect() {
	global $db;

	return $db;
}
// }}}
// {{{ function Cleanup()
function Cleanup() {
}
//}}}
// {{{ function IsLoginValid($user,$pw,&$retval)
/**
*
* @param
* @return
*
*/
function IsLoginValid($user, $pw, &$retval) {
	$retval = false;
	$query = "select IsLoginValid('" . pg_escape_string($user) . "','" . pg_escape_string($pw) . "')";
	$database =& DBConnect();

	if ($database) {
		$result = pg_exec($database, $query);

		if ($result && pg_numrows($result)) {
			$row = pg_fetch_array($result,0);
			$retval = $row[0];
		}
	}
	else {
		echo "Error:  No Connection";
	}
	return $retval;
}
// }}}

// {{{ function CleanPorts($watchID)
/**
*
* @param
* @return
*
*/
function CleanPorts($watchID) {
	$retval = true;
	$query = "delete from watch_list_element where watch_list_id = " . pg_escape_string($watchID);
	$database =& DBConnect();

	echo "Cleaning out old ports<BR>\n";
	if ($database) {
		$result = pg_exec($database, $query);

		if (!$result) {
			$error = pg_errormessage($database);
			echo "$error<BR>\n";
			$retval = false;
		}
	}
	else {
		echo "Error:  No Connection";
		$retval = false;
	}
	return $retval;
}
// }}}
// {{{ function AddPortsToList($ports,$userID)
/**
*
* @param
* @return
*
*/
function AddPortsToList($ports, $userID) {
	$source = "AddPortsToList";
	$successful = 0;
	$dups = 0;
	// Connect and add the ports
	$database =& DBConnect();
	if ($database) {
		
		if (!$userID) {
			echo "Missing userID in $source";
			return false;
		}
		// optimize this
		foreach ($ports as $port) {
			$query = "select WatchListAdd(" . pg_escape_string($userID) . ", '" . pg_escape_string($port) . "')";
			$result = @pg_exec($database, $query);

			if (!$result) {
				$errorMsg = pg_errormessage($database);
				if (strpos($errorMsg, 'Cannot insert a duplicate key')) {
					$dups++;
				}
				else {
					$errorLog[$port] = $errorMsg;
				}
			}
			else {
				$successful++;
			}
		}
	} 
	else {
		echo "Error:  No Connection";
	}

	echo "<P>Your pkg_info data has been uploaded and your watch list has been updated as follows:</P>";
	echo "Added $successful new ports<BR>\n";
	if ($dups) {
		echo "$dups ports in your update were already on your watch list.<BR>\n";
	}
	if (count($errorLog) > 0) {
		epp("<pre>");
		epp("ERROR LOG");
		foreach ($errorLog as $error) {
			echo $error . "<BR>\n";
		}
#		print_r($errorLog);
   		epp("</pre>");
	}
	if (count($errorLog) > 0) {
		return false;
	}
	return true;
}
// }}}
?>