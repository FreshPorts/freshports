<?
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
// function IsLoginValid($user,$pw) {{{
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
// function GetWatchID($userID) {{{
/**
*
* @param
* @return
*
*/
function GetWatchID($userID) {
	$retval = false;
	$query = "select id from watch_list where user_id = $userID";
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
				$errorLog[$port] = pg_errormessage($database);
			}
			else {
				$successful++;
			}
		}
	} 
	else {
		echo "Error:  No Connection";
	}
	echo "Added $successful Ports\n";

	if (DEBUG && count($errorLog) > 0) {
		echo("<pre>");
		echo "ERROR LOG";
		print_r($errorLog);
   		echo("</pre>");
	}
	if (count($errorLog) > 0) {
		return false;
	}
	return true;
}
// }}}
?>
