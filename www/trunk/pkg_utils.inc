<?
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

// function &DBConnect() {{{
/**
*
* @param
* @return
*
*/
function &DBConnect() {
	global $database;
	if (!$database) {
		$database = pg_connect("dbname=FreshPorts2 user=jcampbell password=manuka");
	}
	return $database;
}
// }}}
// function Cleanup() {{{
function Cleanup() {
	global $database;
	if ($database) {
		pg_exec ($database, "end");
	}
}
//}}}
// function IsLoginValid($user,$pw,&$retval) {{{
/**
*
* @param
* @return
*
*/
function IsLoginValid($user,$pw,&$retval) {
	$retval = false;
	$query = "select IsLoginValid('$user','$pw')";
	$database =& DBConnect();

	if ($database) {
		$result = pg_exec($database,$query);

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
// function GetWatchID($userID,&$watchID) {{{
/**
*
* @param
* @return
*
*/
function GetWatchID($userID,&$watchID) {
	$retval = false;
	$query = "select id from watch_list where user_id = $userID";
	$database =& DBConnect();

	if ($database) {
		$result = pg_exec($database,$query);

		if ($result && pg_numrows($result)) {
			$retval = true;
			$row = pg_fetch_array($result,0);
			$watchID = $row[0];
		}
	}
	else {
		echo "Error:  No Connection";
	}
	return $retval;
}
// }}}
// function CleanPorts($watchID) {{{
/**
*
* @param
* @return
*
*/
function CleanPorts($watchID) {
	$retval = true;
	$query = "delete from watch_list_element where watch_list_id = $watchID";
	$database =& DBConnect();

	echo "Cleaning out old ports\n";
	if ($database) {
		$result = pg_exec($database,$query);

		if (!$result) {
			$error = pg_errormessage($database);
			echo "$error\n";
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
// function AddPortsToList($ports,$userID) {{{
/**
*
* @param
* @return
*
*/
function AddPortsToList($ports,$userID) {
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
			$query = "select WatchListAdd($userID,'$port')";
			$result = @pg_exec($database,$query);

			if (!$result) {
				$errorMsg = pg_errormessage($database);
				if (strpos($errorMsg,'Cannot insert a duplicate key')) {
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
	echo "Added $successful new ports\n";
	if ($dups) {
		echo "$dups ports were duplicate and are still registered\n";
	}
	if (count($errorLog) > 0) {
		epp("<pre>");
		epp("ERROR LOG");
		print_r($errorLog);
   		epp("</pre>");
	}
	if (count($errorLog) > 0) {
		return false;
	}
	return true;
}
// }}}
?>
