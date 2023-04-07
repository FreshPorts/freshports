<?php

function PathnameDiffers($Path1, $Path2) {
    # if the two paths are different, we might want to redirect
    # compare the two paths, ignoring any trailing slashes
    return rtrim($Path1, '/') != rtrim($Path2, '/');
}

function RedirectIncorrectPathCase($destination, $args = array()) {
	$https = ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off'));
	if ($https) {
		$protocol = "https";
	} else {
		$protocol = "http";
	}

	header("HTTP/1.1 301 Moved Permanently");

	$query = '';
	if ($args) {
		$query = '?' . http_build_query($args);
	}

	header('Location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . $destination . $query);
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

	$Debug  = 1;

	# start off assuming a 404 - some non-false value	
	$result = -1;

	$IsCategory = false;
	$IsElement  = false;
	$HasCommitsOnBranch = false;
	$CategoryID = 0;

	if ($Debug) {
		echo "Debug is turned on.  Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";
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
		# if this is a category, this function does not return
		Try_Displaying_Category($db, $path_parts, $url_args, $Branch);
	}

	if (count($path_parts) == 2) {
		# if this is a port, this function does not return
		Try_Displaying_Port($db, $path_parts, $url_args, $Branch);
	}

	# if this is an element, this function does not return
	Try_Displaying_Element($db, $path_parts, $url_args, $Branch);



    # Things to try after this: case insensitive search
    # if found then, reinvoke this function?


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
		freshports_CategoryDisplay($db, $Category, 1, $User->page_size, $Branch = BRANCH_HEAD);
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
    $MyPort->setDebug(true);
	$result = $MyPort->FetchByCategoryPortBranch($category, $port, $Branch, $User->id);
	if ($result) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-port.php');
		# display $result
		# if we fetched you here, we got commits on that branch
        $result = freshports_PortDisplayNew($db, $MyPort, $category, $port, $url_args, $Branch, true);
    }
}


function Try_Displaying_ELement($db, $path_parts, $url_args, $Branch) {
	#
	# $db - PostgreSQL database handle
	# path_parts - array of path. e.g. sysutils/anvil/Makefile
	# url_parts  - output of url_args()
	# Do not return from this function if what we find is a port
	# branch - head, 2023Q1, for example
	#
}
