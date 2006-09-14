<?php
	#
	# $Id: missing.php,v 1.1.2.31 2006-09-14 16:50:25 dan Exp $
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

	$pathname = AddSlashes(htmlentities($REQUEST_URI));

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

#	UnSet($result);

	$ElementRecord = new ElementRecord($db);

	# first goal, remove leading / and leading ports/
	if (substr($pathname, 0, 1) == '/') {
		$pathname = substr($pathname, 1);
	}

	if (preg_match('|^ports/|', $pathname)) {
		$pathname = substr($pathname, 6);
	}

	define('PATH_NAME', $pathname);

	# Strip off the files.php extension if it's there...
	$FilesRequest = preg_replace('|^(.*)/files\.php$|', '\\1', $pathname);
	if ($FilesRequest != $pathname) {
		$pathname     = $FilesRequest;
		$FilesRequest = true;
	} else {
		$FilesRequest = false;
	}

	if ($Debug) {
		echo "pathname='" . htmlentities($pathname) . "'<br>";
		echo "FilesRequest='" . $FilesRequest . "'<br>";
	}

	if (strpos($pathname, '/') !== FALSE) {
		GLOBAL $User;

		list($category, $port, $extra) = explode('/', PATH_NAME);
		if ($Debug) echo "extra is '" . $extra . "'<br>";
		if ($Debug) echo "category: '" . $category . "'<br>";
		if ($Debug) echo "port: '" . $port . "'<br>";
		if ($extra == '' && $port != '' || $FilesRequest) {
			if ($FilesRequest) {
				if ($Debug) echo 'going for files.php<br>';
				if ($Debug) echo 'checking for PortID<br>';
				$element_id = freshports_GetElementID($db, $category, $port);
				if (IsSet($element_id)) {
					if ($Debug) echo "$category/$port found by freshports_GetElementID<br>";
					# extract the message ID from the URI
					parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
					$message_id = $query_parts['message_id'];

					if ($Debug) echo 'we have message_id=' . $message_id . '<br>';
					require_once($_SERVER['DOCUMENT_ROOT'] . '/include/files.php');
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
					freshports_Files($User, $element_id, $message_id, $db);
					exit;
				}
			} else {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');

				# if zero is returned, all is well, otherwise, we can't display that category/port.
				if (!freshports_PortDisplay($db, $category, $port)) {
					exit;
				}
			}
		}
	}

	if ($Debug) echo 'checking for ' . FRESHPORTS_PORTS_TREE_PREFIX . $pathname . '<br>';
	list($category, $extra) = explode('/', $pathname);
	if ($extra == '') {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');
		$Category = new Category($db);
		$CategoryID = $Category->IsCategoryByName($category);
		if (IsSet($CategoryID)) {
			// found that category!
			if ($Debug) echo 'found that category<br>';
			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
			freshports_CategoryByID($db, $CategoryID);
			exit;
		}
	}


	if ($ElementRecord->FetchByName(FRESHPORTS_PORTS_TREE_PREFIX . $pathname)) {
		if ($ElementRecord->IsCategory()) {

			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
			freshports_CategoryByElementID($db, $ElementRecord->id);
			exit;
		} else {
			# this is a non-port (e.g. /Mk/)
			require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-non-port.php');
			freshports_NonPortDescription($db, $ElementRecord);
			exit;
		}
	} else {
		if ($Debug) echo 'not an element<br>';
		$result = $REQUEST_URI;
	}

	if ($Debug) {
		echo "\$ElementRecord->id         = $ElementRecord->id<br>";
		echo "\$ElementRecord->name       = $ElementRecord->name<br>";
		echo "\$ElementRecord->type       = $ElementRecord->type<br>";
		echo "\$ElementRecord->status     = $ElementRecord->status<br>";
		echo "\$ElementRecord->iscategory = $ElementRecord->iscategory<br>";
		echo "\$ElementRecord->isport     = $ElementRecord->isport<br>";
		echo '<br>';
		echo "\$ElementRecord->element_pathname = $ElementRecord->element_pathname<br>";
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
