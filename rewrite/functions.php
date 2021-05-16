<?php

function PathnameDiffers($Path1, $Path2) {
    # if the two paths are different, we might want to redirect
    # compare the two paths, ignoring any trailing slashes
    return rtrim($Path1, '/') != rtrim($Path2, '/');
}

function freshports_Parse404URI($REQUEST_URI, $db) {
	# we have a pending 404
	# if we can parse it, then do so and return a false value;
	# otherwise, return a non-false value.

	GLOBAL $User;

	$Debug  = 0;
	$result = false;

	$IsPort     = false;
	$IsCategory = false;
	$IsElement  = false;
	$HasCommitsOnBranch = false;
	$CategoryID = 0;

	if ($Debug) {
		echo "Debug is turned on.  Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";
		echo "\$REQUEST_URI='$REQUEST_URI'<br>";
		phpinfo();
	}

	$URLParts = parse_url($_SERVER['REQUEST_URI']);
	if ($Debug)
	{
		echo 'the URI is <pre>\'' . $_SERVER['REQUEST_URI'] . "'</pre><br>\n";
		echo 'the url parts are';
		echo '<pre>';
		echo var_dump($URLParts);
		echo "</pre><br>\n";
	}


	$pathname = $URLParts['path'];
	if ($Debug) echo "The pathname is '$pathname'<br>";

	# split the query part of the url into the various arguments
	parse_str($URLParts['query'], $url_args);


	if (IsSet($url_args['branch'])) {
		$Branch = NormalizeBranch(htmlspecialchars($url_args['branch']));
		define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/branches/' . $Branch . '/');
	} else {
		$Branch = BRANCH_HEAD;
		define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/head/');
	}


	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

	$ElementRecord = new ElementRecord($db);

	# first goal, remove leading / and leading ports/
	if (substr($pathname, 0, 1) == '/') {
		$pathname = substr($pathname, 1);
	}

	if (preg_match('|^ports/|', $pathname)) {
		$pathname = substr($pathname, 6);
	}

	# remove trailing /
	$pathname = rtrim($pathname, '/');

	define('PATH_NAME', $pathname);
	if ($Debug) echo "PATH_NAME='" . FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME . "'<br>";

	# let's see if this is a category.
	if ($ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME, 1)) {
		$IsElement = true;
		if ($Debug) echo 'we found an element for that<br>';
		if ($Debug) echo "we have: '$ElementRecord->element_pathname'<br>";
		if ($Debug) echo " we had: '" . FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME . "'<br>";
		if (PathnameDiffers($ElementRecord->element_pathname . '/', FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME)) {
			# in a case insensitive search, we want to redirect if the case was wrong
			if ($Debug) echo "we are redirecting to '" . $ElementRecord->element_pathname . "/'<br>";
			if ($Debug) echo 'which normalizes to ' . str_replace(FRESHPORTS_PORTS_TREE_PREFIX, '/', $ElementRecord->element_pathname . '/<br>');
			$https = ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off'));
			if ($https) {
				$protocol = "https";
			} else {
				$protocol = "http";
			}

			header("HTTP/1.1 301 Moved Permanently");
			header('Location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace(FRESHPORTS_PORTS_TREE_PREFIX, '/', $ElementRecord->element_pathname . '/'));
			exit;
		}

		if ($Debug) echo 'checking if this is a Category<br>';
		if ($ElementRecord->IsCategory()) {
			$IsCategory = true;
			if ($Debug) echo 'This is a category<br>';
		} else {
			if ($Debug) echo 'It is NOT a category<br>';
			if ($Debug) echo 'we found an element for that, therefore, there must be commits!<br>';
			$HasCommitsOnBranch = true; // this is true even if the branch is head
		}
	} else {
		if ($Debug) echo 'we found no element for that.<br>';
		if ($Branch != BRANCH_HEAD) {
			if ($Debug) echo 'trying on head next<br>';
			# if this is not a category, let's check for details on what might be a port
			if ($Debug) echo 'checking ' . FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME . ' to see what we find<br>';
			if ($ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME, 0)) {
				$IsElement          = true;
				$HasCommitsOnBranch = false;
				$IsCategory = $ElementRecord->IsCategory();
			}
		}
	}

	if ($IsElement) {
		if ($Debug) {
			echo 'Yes, we found an element for that path!<br>';
			echo '<pre>';
			echo print_r($ElementRecord, true);
			echo '</pre>';
		}

		if ($IsCategory) {
			if ($Debug) echo 'This is a category<br>';

			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
			$Category = new Category($db);
			$CategoryID = $Category->FetchByElementID($ElementRecord->id);
		} else {
			if ($Debug) echo 'That path does not point at a category!<br>';
		}

		if ($ElementRecord->IsPort()) {
			$IsPort = true;

			# we don't use list($category, $port) so we don't have to worry
			# about extra bits
			$PathParts = explode('/', PATH_NAME);
			$category = $PathParts[0];
			$port     = $PathParts[1];
			if ($Debug) echo "This is a port!<br>";
			if ($Debug) echo "Category='$category'<br>";
			if ($Debug) echo "Port='$port'<br>";
		} else {
			if ($Debug) echo 'The call to ElementRecord indicates this is not a port<br>';
		}
	} else {
		if ($Debug) echo 'not an element<br>';
		# let's see if this is a virtual category!

		$result = $REQUEST_URI;
		$PathParts = explode('/', PATH_NAME);

		if ($Debug) {
			echo '<pre>';
			print_r($PathParts);
			echo '</pre>';
		}

		# start with nothing.
		unset($category);
		unset($port);

		# if the URL looks like /afterstep/, then we'll get two elements
		# in the array.  The second will be empty.  Hence the != ''
		#
		# grab whatever we have
		#
		if (IsSet($PathParts[0]) && $PathParts[0] != '') {
			$category = $PathParts[0];
			if ($Debug) echo "Category is '$category'<br>";
		}

		if (IsSet($PathParts[1]) && $PathParts[1] != '') {
			$port = $PathParts[1];
			if ($Debug) echo "Port is '$port'<br>";
		}

		if (IsSet($port)) {
			$IsPort = false;
			$result = $REQUEST_URI;

			if ($Debug) echo 'This is a Port but there is no element for it.<br>';
		}

		if (IsSet($category) && !$IsPort) {
			# we have a valid category, but no valid port.
			# we will display the category only if they did *try* to speciy a port.
			# i.e. they suuplied an invalid port name
			if ($Debug) echo 'This is a category &&&<br>';

			if (IsSet($port)) {
				if ($Debug)  'Invalid port supplied for a valid category<br>';
			} else {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
				$Category = new Category($db);
				$CategoryID = $Category->FetchByName($category);
				if ($CategoryID) {
					$IsCategory = true;
				}
			}
		}
	}

	if ($Debug) echo 'let us see what we will include now....<br>';

	if ($IsPort) {
		if ($Debug) echo 'including missing-port<BR>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-port.php');

		if ($Debug) echo 'including missing-port<BR>';

		if ($HasCommitsOnBranch) {
			# if zero is returned, all is well, otherwise, we can't display that category/port.
			if ($Debug) echo 'invoking freshports_PortDisplay<br>';
			if (freshports_PortDisplay($db, $category, $port, $Branch)) {
				echo 'freshports_PortDisplay returned non-zero';
				return -1;
			}
		} else {
			# if zero is returned, all is well, otherwise, we can't display that category/port.
			if ($Debug) echo 'invoking freshports_PortDisplayNotOnBranch<br>';
			if (freshports_PortDisplayNotOnBranch($db, $category, $port, $Branch)) {
				echo 'freshports_PortDisplay returned non-zero';
				return -1;
			}
		}
	}

	if ($IsCategory && !$IsPort) {
		if ($Debug) {
			echo 'This is a category ***<br>';
			syslog(LOG_NOTICE, 'invoking ' . $_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-category.php');
		}
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-category.php');
		freshports_CategoryByID($db, $CategoryID, $page, $User->page_size, $Branch);
		exit;
	}

	if ($IsElement && !$IsPort) {
		if ($Debug) echo 'This is an element<br>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-non-port.php');
		freshports_NonPortDescription($db, $ElementRecord);
		exit;
	}

	if ($Debug) echo 'we hit rock bottom in ' . __FUNCTION__ . ' of ' . __FILE__;

	return $result;
}
