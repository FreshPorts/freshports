<?php

function PathnameDiffers($Path1, $Path2):string {
    # if the two paths are different, we might want to redirect
    # compare the two paths, ignoring any trailing slashes
    return rtrim($Path1, '/') != rtrim($Path2, '/');
}

function RedirectIncorrectPathCase($destination, $args = array()):never {
	#
	# this function is used to redirect when a path name is encountered which is not quite right.
	# For example, /sysutils/Anvil will redirect to /sysutils/anvil, correcting the case
	#
	header("HTTP/1.1 301 Moved Permanently");

	$query = '';
	if ($args) {
		$query = '?' . http_build_query($args);
	}

	header('Location: ' . $destination . $query);
	exit;
}

function freshports_Parse404URI($url, $db):never {
	#
	# $url is something like https://dev.freshports.org/sysutils/anvil/ for example.
	#
	# We have a pending 404
	# Meaning, the URL did not find a corresponding file on disk in the WWWDIR
	# We examine the URL to see if if matches a database entry.
	# Depending on how many items are in the path, it could be:
	# * a FreeBSD category
	# * a FreeBSD port
	# * something else in the ports tree
	# * not found
	#
	# We should never return from this function.
	#

	$Debug = 0;

	if ($Debug) echo '<br>into ' . __FILE__ . '::' . __FUNCTION__ . "with $url<br>";

	# start off assuming a 404 - some non-false value	
	$result = -1;

	$IsCategory = false;
	$IsElement  = false;

	# Why do we care if there are commits for this port on the branch?
	# If there are commits on the branch, that port exists on that branch.
	# That's how a port gets onto a branch - a commit.
	# This differs from git/subversion in which a branch is created by copying from head.
	# In the FreshPorts database, a branch is created when something is committed to that branch.
	# Thus, if there are no commits on the branch, the port does not exist on that branch (i.e in the database).
	# Therefore, when displaying such a port on the branch, we need to display the port from head (which
	# will always exist, by definition). Well, if it's not on head, it does not exist, but that's
	# different use case.
	#
	$HasCommitsOnBranch = false;
	$CategoryID = 0;

	if ($Debug) {
		echo '<br>Debug is turned on for:<br>'. __FILE__ . '::' . __FUNCTION__ . "<br>Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";
#		phpinfo();
	}

	$URLParts = parse_url($url);
	if ($Debug)
	{
		echo "the URL is '" . $url . "'<br>\n";
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

	if ($Debug)
	{
		if (count($url_args)) {
			echo 'For the URL <pre>\'' . $url . "'</pre><br>\n";
			echo 'the url args are';
			echo '<pre>';
			var_dump($url_args);
			echo "</pre><br>\n";

		} else {
			echo 'There are no arguments to this URI<br>';
		}
	}

	# If not specified, branch is HEAD.
	if (IsSet($url_args['branch'])) {
		if ($Debug) echo 'branch is defined within $url_args<br>';
		# this might still be BRANCH_HEAD
		$Branch = NormalizeBranch(htmlspecialchars($url_args['branch']));
	} else {
		if ($Debug) echo 'branch is not supplied within $url_args<br>';
		$Branch = BRANCH_HEAD;
	}

	if ($Debug) echo "\$Branch ='$Branch'<br>\n";


#	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');
#
#	$ElementRecord = new ElementRecord($db);

	# first goal, remove leading '/' and leading 'ports/'
	$pathname = ltrim($pathname, '/');
	if ($Debug) echo "After triming leading slashes, the pathname is '$pathname'<br>";

	if (str_starts_with($pathname, 'ports/')) {
		$pathname = substr($pathname, 6);
		if ($Debug) echo "After removing 'ports/', the pathname is '$pathname'<br>";
	}

	# remove trailing /
	$pathname = rtrim($pathname, '/');
	if ($Debug) echo "after trimming trailing slashes: the pathname is '$pathname'<br>";

	# split the path into separate directories along the path
	$path_parts = explode('/', $pathname);
	if ($Debug) {
		echo '<pre>$path_parts:<br>';
		var_dump($path_parts);
		echo '</pre>';
	}
	
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
	# We have no options left: 404
	$FreshPortsTitle = 'FreshPorts';
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing.php');

	# this exit should never be hit
	$msg = 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . '::' . __LINE__ . ' we should never get this far in the code.';
	syslog(LOG_ERR, $msg);
	die($msg);
}

function Try_Displaying_Category($db, $path_parts, $url_args, $Branch):void {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# branch - head, 2023Q1, for example
	#
	# Do not return from this function if what we find is a category
	#

	if (!is_array($path_parts) || count($path_parts) != 1) {
		$msg = 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters';
		syslog(LOG_ERR, $msg);
		die($msg);
	}

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

	$category = $path_parts[0];
	$Category = new Category($db);
	$CategoryID = $Category->FetchByName($category);
	if ($CategoryID) {
#		if ($Debug) echo 'This is a category<br>';

		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-category.php');
		# We may have to pass in page size / page number from URL
		freshports_CategoryDisplayNew($db, $Category, $url_args, $Branch);
		exit;
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
		$msg = 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . ' invoked with invalid parameters';
		syslog(LOG_ERR, $msg);
		die($msg);
	}

	$category = $path_parts[0];
	$port     = $path_parts[1];
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	$MyPort = new Port($db);
	$MyPort->setDebug($Debug);
	$result = $MyPort->FetchByCategoryPortBranch($category, $port, $Branch, $User->id);
	if (!$result) {
		if ($Debug) echo htmlentities("$category/$port") . ' was not found on branch ' . htmlentities($Branch);
		# try on head, if found, it's just that this port has no commits on branch
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
        freshports_PortDisplayNew($db, $MyPort, $category, $port, $url_args, $Branch, $HasCommitsOnBranch);
		exit;
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
	if ($Debug) {
		echo 'into ' . __FUNCTION__;
		var_dump($path_parts);
	}
	$element_id = $ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_HEAD_PREFIX . '/' . implode('/', $path_parts));
	if (!isset($element_id)) {
		if ($Debug) echo 'null returned from ElementRecord->FetchByName<br>';
		# we did not find a match
		return;
	}

	if ($Debug) echo "found element with element_id = '$element_id' while doing a case sensitive search<br>";

	if ($element_id === -1) {
		$msg = 'Multiple matches found on case insensitive search. This error should never happen: ' . __FILE__ . '::' . __FUNCTION__;
		syslog(LOG_ERR, $msg);
		die($msg);
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
		$msg = 'Multiple matches found on case insensitive search. This error should never happen: ' . __FILE__ . '::' . __FUNCTION__;
		syslog(LOG_ERR, $msg);
		die($msg);
	}

	# We found something while searching - determine the correct search terms
	if ($Debug)  var_dump($ElementRecord);

	if ($ElementRecord->IsCategory()) {
		# XXX we should redirect, to the correct URL here
		if ($Debug) echo "We found a category<br>";
		RedirectIncorrectPathCase('/' . $ElementRecord->name . '/', $args = array());
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

		RedirectIncorrectPathCase("/$Category/$Port/", $url_args);

		# this exit should never be hit
		$msg = 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . '::' . __LINE__ . ' we should never get this far in the code.';
		syslog(LOG_ERR, $msg);
		die($msg);
	}

	# this exit should never be hit
	$msg = 'Fatal error: ' . __FILE__ . '::' . __FUNCTION__ . '::' . __LINE__ . ' we should never get this far in the code.';
	syslog(LOG_ERR, $msg);
	die($msg);
}
