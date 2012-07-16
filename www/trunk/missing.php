<?php
	#
	# $Id: missing.php,v 1.5 2012-07-16 14:49:13 dan Exp $
	#
	# Copyright (c) 2001-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');


function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return a false value;
	# otherwise, return a non-false value.

	GLOBAL $User;

	$Debug  = 0;
	$result = '';

	$IsPort     = false;
	$IsCategory = false;
	$IsElement  = false;

	if ($Debug) echo "Debug is turned on.  Only 404 will be returned now because we cannot alter the headers at this time.<br>\n";

	$CategoryID = 0;

	define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/');

	$URLParts = parse_url($_SERVER['SCRIPT_URI']);

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
		$IsElement = true;
		if ($Debug) {
			echo 'Yes, we found an element for that path!<br>';
			echo '<pre>';
			var_dump($ElementRecord);
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

			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');

			$Debug = 0;
			if ($Debug) echo 'This is a Port<br>';
		}
	} else {
		if ($Debug) echo 'not an element<br>';
		# let's see if this is a virtual category!

		$result = $REQUEST_URI;
		$PathParts = explode('/', PATH_NAME);

		if ($Debug) {
			echo '<pre>';
			var_dump($PathParts);
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
			$IsPort = true;
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

	if ($IsPort) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');

		# if zero is returned, all is well, otherwise, we can't display that category/port.
		if (!freshports_PortDisplay($db, $category, $port)) {
			exit;
		}
	}

	if ($IsCategory) {
		if ($Debug) echo 'This is a category<br>';
		require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
		freshports_CategoryByID($db, $CategoryID, 1, $User->page_size);
		exit;
	}

	if ($IsElement) {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-non-port.php');
		freshports_NonPortDescription($db, $ElementRecord);
		exit;
	}
				
	return $result;
}

$result = freshports_Parse404URI($_SERVER['REDIRECT_URL'], $db);

if ($result) {

	#
	# this is a true 404

	$Title = 'Document not found';
	freshports_Start($Title,
					$FreshPortsTitle . ' - new ports, applications',
					'FreeBSD, index, applications, ports');

?>

<?php echo freshports_MainTable(); ?>
<TR>
<TD WIDTH="100%" VALIGN="top">
<?php echo freshports_MainContentTable(); ?>
<TR>
    <TD BGCOLOR="<?php echo BACKGROUND_COLOUR; ?>" HEIGHT="29"><FONT COLOR="#FFFFFF"><BIG><BIG>
<?
   echo "$FreshPortsTitle -- $Title";
?>
</BIG></BIG></FONT></TD>
</TR>

<TR>
<TD WIDTH="100%" VALIGN="top">
<P>
Sorry, but I don't know anything about that.
</P>

<P>
<? echo htmlentities($result) ?>
</P>

<P>
Perhaps a <A HREF="/categories.php">list of categories</A> or <A HREF="/search.php">the search page</A> might be helpful.
</P>

</TD>
</TR>
</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</TR>

</TABLE>

<?
	echo freshports_ShowFooter();
?>

</body>
</html>

<?
} else {
#	echo " ummm, not sure what that was: '$result'";
}

?>