<?php

function PathnameDiffers($Path1, $Path2) {
    # if the two paths are different, we might want to redirect
    # compare the two paths, ignoring any trailing slashes
    return rtrim($Path1, '/') != rtrim($Path2, '/');
}

function RedirectIncorrectPathCase($destination, $args = array()) {
	header("HTTP/1.1 301 Moved Permanently");

	$query = '';
	if ($args) {
		$query = '?' . http_build_query($args);
	}

	header('Location: ' . $destination . $query);
	return false;
}

function freshports_Parse404URI($REQUEST_URI, $db) {
	# 
	# We have a pending 404
	# Meaning, the URI did not find a corresponding file on disk in the WWWDIR
	# We examine the URI to see if if matches a database entry.
	# Depending on how many items are in the path, it could be:
	# * a FreeBSD category
	# * a FreeBSD port
	# * something else in the ports tree
	# * not found
	#
	# If we can parse it, then do so and return a false value;
	# otherwise, return a non-false value.
	#
	# false = processed, found something which matches
	# non-false = failed, found nothing, 404
	#

	GLOBAL $User;

	$Debug  = 0;

	# start off assuming a 404 - some non-false value	
	$result = -1;

	$IsCategory = false;
	$IsElement  = false;
	$HasCommitsOnBranch = false;
	$CategoryID = 0;

	if ($Debug) {
		echo 'Debug is turned on '. __FILE__ . '::' . __FUNCTION__ . "Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";
		echo "\$REQUEST_URI='$REQUEST_URI'<br>";
#		phpinfo();
	}

	$URLParts = parse_url($_SERVER['REQUEST_URI']);
	if ($Debug)
	{
		echo 'the URI is <pre>\'' . $_SERVER['REQUEST_URI'] . "'</pre><br>\n";
		echo 'the url parts are';
		echo '<pre>';
        var_dump($URLParts);
		echo "</pre><br>\n";
	}

	# $URLParts contains various items, including path and query, the parts we need to know here.
	# https://www.php.net/manual/en/function.parse-url

	$pathname = $URLParts['path'] ?? '';
	if ($Debug) echo "The pathname is '$pathname'<br>";

	# split the query part of the url into the various arguments
	# this helps us find any branch specification (e.g. branch=2023Q2
	$url_args = array();
	if (IsSet($URLParts['query'])) {
		parse_str($URLParts['query'], $url_args);
	}

	# If not specified, branch is HEAD.
	if (IsSet($url_args['branch'])) {
		# this might still be BRANCH_HEAD
		$Branch = NormalizeBranch(htmlspecialchars($url_args['branch']));
	} else {
		$Branch = BRANCH_HEAD;
	}


#	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');
#
#	$ElementRecord = new ElementRecord($db);

	# first goal, remove leading '/' and leading 'ports/'
	$pathname = ltrim($pathname, '/');
	if ($Debug) echo "The pathname is '$pathname'<br>";

	if (preg_match('|^ports/|', $pathname)) {
		$pathname = substr($pathname, 6);
		if ($Debug) echo "The pathname is '$pathname'<br>";
	}

	# remove trailing /
	$pathname = rtrim($pathname, '/');
	if ($Debug) echo "The pathname is '$pathname'<br>";

	if ($Debug) echo "$pathname='" . $pathname . "'<br>";
	
	# split the path into separate directories along the path
	$path_parts = explode('/', $pathname);
	if ($Debug) echo '<pre>' . var_dump($path_parts) . '</pre>';
	
	if (count($path_parts) == 1) {
		if ($Debug) echo "trying that as a category<br>";
		# if this is a category, this function does not return
		Try_Displaying_Category($db, $path_parts, $url_args, $Branch);
        if ($Debug) echo "'$pathname' on $Branch was not a category<br>";
	}

	if (count($path_parts) == 2) {
		if ($Debug) echo "trying that as a port<br>";
		# if this is a port, this function does not return
		Try_Displaying_Port($db, $path_parts, $url_args, $Branch);
        if ($Debug) echo "'$pathname' on $Branch was was not a port<br>";
	}

	if ($Debug) echo "trying that as an element<br>";
	# if this is an element, this function does not return
	Try_Displaying_Element($db, $path_parts, $url_args, $Branch);
    if ($Debug) echo "'$pathname' on $Branch was not an alement<br>";

	# try case insensitive searching
	if ($Debug) echo "trying a case insensitive search<br>";
	# if a match is found, this function does not return
	Try_Searching_Element_Case_Insensitive($db, $path_parts, $url_args, $Branch);
    if ($Debug) echo "'$pathname' on $Branch was not an element case insenstive<br>";


	if ($Debug) echo 'we hit rock bottom at ' . __FILE__ . '::' . __FUNCTION__;

	return true;
}

function Try_Displaying_Category($db, $path_parts, $url_args, $Branch) {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# branch - head, 2023Q1, for example
	#
	# Do not return from this function if what we find is a category
	#

	GLOBAL $User;

	if (!is_array($path_parts) || count($path_parts) != 1) {
		syslog(LOG_ERR, 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters');
		die(__FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters');
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$category = $path_parts[0];
	$Category = new Category($db);
	$CategoryID = $Category->FetchByName($category);
	if ($CategoryID) {
#		if ($Debug) echo 'This is a category<br>';

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-category.php');
		# We may have to pass in page size / page number from URL
		freshports_CategoryDisplay($db, $Category, 1, $User->page_size, $Branch);
	}
}


function Try_Displaying_Port($db, $path_parts, $url_args, $Branch) {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# Do not return from this function if what we find is a port
	# branch - head, 2023Q1, for example
	#
	#
	GLOBAL $User;

	$Debug = 0;

	# if it's on head, it has commits
	$HasCommitsOnBranch = $Branch == BRANCH_HEAD;

	if (!is_array($path_parts) || count($path_parts) != 2) {
		syslog(LOG_ERR, 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters');
		die(__FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters');
	}

	$category = $path_parts[0];
	$port     = $path_parts[1];
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	$MyPort = new Port($db);
	$MyPort->setDebug($Debug);
	$result = $MyPort->FetchByCategoryPortBranch($category, $port, $Branch, $User->id);
	if (!$result) {
		if ($Debug) echo htmlentities("$category/$port") . ' was not found on branch ' . htmlentities($Branch);
		# try on head, if found, just no commits on branch
		$HasCommitsOnBranch = false;
		$result = $MyPort->FetchByCategoryPortBranch($category, $port, BRANCH_HEAD, $User->id);
		if ($Debug) {
			if ($result) {
				echo 'That port does not exist on the branch, but we found it on ' . BRANCH_HEAD . '<br>';
			} else {
				echo 'That port does not exist on ' . BRANCH_HEAD . ' or on ' . htmlentities($Branch) . '<br>';
			}
		}
	}
	if ($result) {
        if ($Debug) {
            echo 'we are displaying ' . htmlentities("$category/$port") . ' on ' . htmlentities($Branch);
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-port.php');
		# display $result
		# if we fetched you here, we got commits on that branch
        $result = freshports_PortDisplayNew($db, $MyPort, $category, $port, $url_args, $Branch, $HasCommitsOnBranch);
    }
}


function Try_Displaying_Element($db, $path_parts, $url_args, $Branch) {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# Do not return from this function if what we find is a port
	# branch - head, 2023Q1, for example
	#

	$Debug = 0;
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

	$ElementRecord = new ElementRecord($db);
	# this is case sensitive
	$element_id = $ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_HEAD_PREFIX . '/' . implode('/', $path_parts));
	if (!isset($element_id)) {
		if ($Debug) echo 'null returned from ElementRecord->FetchByName<br>';
		# we did not find a match
		return;
	}

	if ($Debug) echo "found element with element_id = '$element_id' while doing a case sensitive search<br>";

	if ($element_id === -1) {
		syslog(LOG_ERR, 'Multiple matches found on case insensitive search. This error should never happen: ' . __FILE__ . '::' . __FUNCTION__);
		die('Multiple matches found for this search. This error should never happen.' .  __FILE__ . '::' . __FUNCTION__);
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-non-port.php');
	freshports_NonPortDescription($db, $ElementRecord);
	exit;
}


function Try_Searching_Element_Case_Insensitive($db, $path_parts, $url_args, $Branch) {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# Do not return from this function if what we find is a port
	# branch - head, 2023Q1, for example
	#
	GLOBAL $User;

	$Debug = 0;
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

	$ElementRecord = new ElementRecord($db);
	# this is case insensitive
	$element_id = $ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_HEAD_PREFIX . '/' . implode('/', $path_parts), false);
	if (!isset($element_id)) {
		if ($Debug) echo 'null returned from ElementRecord->FetchByName<br>';
		# we did not find a match
		# this should be a 404
		return;
	}

	if ($Debug) echo "found element with element_id = '$element_id' while doing a case insensitive search<br>";

	if ($element_id === -1) {
		syslog(LOG_ERR, 'Multiple matches found on case insensitive search. This error should never happen: ' . __FILE__ . '::' . __FUNCTION__);
		die('Multiple matches found for this search. This error should never happen.' .  __FILE__ . '::' . __FUNCTION__);
	}

	# We found something while searching - determine the correct search terms
	if ($Debug) echo var_dump($ElementRecord);

	if ($ElementRecord->IsCategory()) {
		# XXX we should redirect, to the correct URL here
		if ($Debug) echo "We found a category<br>";
		RedirectIncorrectPathCase('/' . $ElementRecord->name, $args = array());
		# this exit should never be hit
		exit('FATAL error: RedirectIncorrectPathCase() returned');
	}

	if ($ElementRecord->IsPort()) {

		# XXX we should redirect, to the correct URL here
		$new_path_parts = explode('/', trim($ElementRecord->element_pathname, '/'));
		if ($Debug) echo '<pre>' . var_dump($new_path_parts) . '</pre>';
		if ($Branch === BRANCH_HEAD) {
			# "/ports/head/sysutils/anvil"
			$Category = $new_path_parts[2];
			$Port     = $new_path_parts[3];
		} else {
			# "/ports/branches/2023Q1/sysutils/anvil"
			$Category  = $new_path_parts[3];
			$Port      = $new_path_parts[4];
		}

		RedirectIncorrectPathCase("/$Category/$Port", $url_args);
		# this exit should never be hit
		exit('FATAL error: RedirectIncorrectPathCase() returned');

		$new_path_parts = array($ElementRecord->element_pathname, $path_parts[1]);
		Try_Displaying_Port($db, $path_parts, $url_args, $Branch);
		syslog(LOG_ERR, 'FATAL ERROR: If we just found a port, the above Try_Displaying_Port() call should never return: ' . __FILE__ . '::' . __FUNCTION__ . '::' . __LINE__);
		die('FATAL ERROR: If we just found a port, the above Try_Displaying_Port() call should never return: ' . __FILE__ . '::' . __FUNCTION__ . '::' . __LINE__);
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-non-port.php');
	freshports_NonPortDescription($db, $ElementRecord);
	exit;
	die('debugging mode. stopping in ' . __FILE__ . '::' . __FUNCTION__);
}
