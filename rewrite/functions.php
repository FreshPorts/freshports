<?php

function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return a false value;
	# otherwise, return a non-false value.

	if (IsSet($_REQUEST['branch'])) {
		$Branch = htmlspecialchars($_REQUEST['branch']);
		define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/branches/' . $Branch . '/');
	} else {
		$Branch = BRANCH_HEAD;
		define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/head/');
	}

	GLOBAL $User;

	$Debug  = 0;
	$result = '';

	$IsPort     = false;
	$IsCategory = false;
	$IsElement  = false;
	$HasCommitsOnBranch = false;

	if ($Debug) {
		echo "Debug is turned on.  Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";
		echo "\$REQUEST_URI='$REQUEST_URI'<br>";
		phpinfo();
	}

	$CategoryID = 0;

	$URLParts = parse_url($_SERVER['REQUEST_URI']);
	if ($Debug)
	{
	  echo 'the URI is <pre>\'' . $_SERVER['REQUEST_URI'] . "'</pre><br>\n";
	  echo 'the url parts are <pre>\'' . print_r($URLParts) . "'</pre><br>\n";
    }

	$pathname = $URLParts['path'];
	if ($Debug) echo "The pathname is '$pathname'<br>";

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

	$ElementRecord = new ElementRecord($db);

	# first goal, remove leading / and leading ports/
	if (substr($pathname, 0, 1) == '/') {
		$pathname = substr($pathname, 1);
	}

	if (preg_match('|^ports/|', $pathname)) {
		$pathname = substr($pathname, 6);
	}

	define('PATH_NAME', $pathname);

	if ($Debug) echo "PATH_NAME='" . FRESHPORTS_PORTS_TREE_PREFIX . PATH_NAME . "'<br>";

	if ($ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_PREFIX . $pathname)) {
	  $IsElement          = true;
          $HasCommitsOnBranch = true; // this is true even if the branch is head
	} else {
	  if ($Branch != BRANCH_HEAD) {
            if ($Debug) echo 'trying on head next<br>';
	    if ($ElementRecord->FetchByName('/ports/head/' . $pathname)) {
	      $IsElement          = true;
	      $HasCommitsOnBranch = false;
	    }
	  }
	}

	if ($IsElement) {
		$IsElement = true;
		if ($Debug) {
			echo 'Yes, we found an element for that path!<br>';
			echo '<pre>';
			echo print_r($ElementRecord, true);
			echo '</pre>';
		}

		if ($ElementRecord->IsCategory()) {
			$IsCategory = true;
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
		}
		else
		{
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
			if ($Debug) echo 'This is a category<br>';

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
		if ($Debug) echo 'including missing-port ' . $Debug . '<BR>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-port.php');
		if ($Debug) echo 'including missing-port ' . $Debug . '<BR>';

		if ($HasCommitsOnBranch) {
			# if zero is returned, all is well, otherwise, we can't display that category/port.
			if (freshports_PortDisplay($db, $category, $port, $Branch)) {
				echo 'freshports_PortDisplay returned non-zero';
				return -1;
			}
		} else {
			# if zero is returned, all is well, otherwise, we can't display that category/port.
			if (freshports_PortDisplayNotOnBranch($db, $category, $port, $Branch)) {
				echo 'freshports_PortDisplay returned non-zero';
				return -1;
			}
		}
	}

	if ($IsCategory && !$IsPort) {
		if ($Debug) echo 'This is a category<br>';
		$query_string = $_SERVER["QUERY_STRING"];
		parse_str($query_string, $url_parts);
		$page = isset($url_parts['page']) ? intval($url_parts['page']) : 1;
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-category.php');
		freshports_CategoryByID($db, $CategoryID, $page, $User->page_size);
		exit;
	}

	if ($IsElement && !$IsPort) {
		if ($Debug) echo 'This is an element<br>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing-non-port.php');
		freshports_NonPortDescription($db, $ElementRecord);
		exit;
	}

	return $result;
}
