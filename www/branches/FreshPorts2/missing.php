<?php
	#
	# $Id: missing.php,v 1.1.2.33 2006-11-09 17:05:51 dan Exp $
	#
	# Copyright (c) 2001-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');


function freshports_Parse404URI($REQUEST_URI, $db) {
	#
	# we have a pending 404
	# if we can parse it, then do so and return 1;
	# otherwise, return 0.

	define('FRESHPORTS_PORTS_TREE_PREFIX', '/ports/');

	$Debug  = 0;
	$result = '';

	$URLParts = parse_url($_SERVER['SCRIPT_URI']);
	parse_str($_SERVER['REDIRECT_QUERY_STRING'], $QueryParts);
	if ($Debug) {
		echo 'parse_url output is: <pre>';
		print_r($URLParts);
		echo '</pre>';

		echo 'and the query parts of the URL are:<pre>';
		var_dump($QueryParts); 
		echo '</pre>';

#		phpinfo();
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
	
	if ($Debug) echo "PATH_NAME='" . PATH_NAME . "'<br>";

	if ($ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_PREFIX . $pathname)) {
		if ($Debug) {
			echo 'Yes, we found an element for that path!<br>';
			echo '<pre>';
			var_dump($ElementRecord);
			echo '</pre>';
		}

		if ($ElementRecord->IsCategory()) {
			if ($Debug) echo 'This is a category<br>';
			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
			freshports_CategoryByElementID($db, $ElementRecord->id, 1, $User->page_size);
			exit;
		}
		
		if ($Debug) echo 'No, that cannot be a category!<br>';

		if ($ElementRecord->IsPort()) {
			# we don't use list($category, $port) so we don't have to worry
			# about extra bits
			$PathParts = explode('/', PATH_NAME);
			$category = $PathParts[0];
			$port     = $PathParts[1];
			if ($Debug) echo "Category='$category'<br>";
			if ($Debug) echo "Port='$port'<br>";

			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');

			$Debug = 0;
			if ($Debug) echo 'This is a Port<br>';

			# if zero is returned, all is well, otherwise, we can't display that category/port.
			freshports_PortDisplay($db, $category, $port);
			exit;
		}

		if ($Debug) echo 'No, that cannot be a port either!!<br>';

		if ($Debug) echo 'This is a not a category and not a port<br>';
		# this is a non-port (e.g. /Mk/)
		require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-non-port.php');
		freshports_NonPortDescription($db, $ElementRecord);
		exit;
	} else {
		if ($Debug) echo 'not an element<br>';
		$result = $REQUEST_URI;
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