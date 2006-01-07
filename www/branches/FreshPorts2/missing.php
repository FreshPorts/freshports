<?php
	#
	# $Id: missing.php,v 1.1.2.27 2006-01-07 21:29:10 dan Exp $
	#
	# Copyright (c) 2001-2003 DVL Software Limited
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

	$Debug  = 0;
	$result = '';

	$pathname = $REQUEST_URI;

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

#	UnSet($result);

	$ElementRecord = new ElementRecord($db);

	if (substr($pathname, 0, 1) != '/') {
		$pathname = '/' . $pathname;
	}

	if (!preg_match('|^/?ports/|', $pathname)) {
		$pathname = '/ports' . $pathname;
	}

	# Strip off the files.php extension if it's there...
	$FilesRequest = preg_replace('|^(.*)/files\.php$|', '\\1', $pathname);
	if ($FilesRequest != $pathname) {
		$pathname     = $FilesRequest;
		$FilesRequest = 1;

	} else {
		$FilesRequest = 0;
	}


	if ($ElementRecord->FetchByName($pathname)) {
		if ($ElementRecord->IsPort()) {

			if ($FilesRequest) {
				# extract the message ID from the URI
				parse_str($_SERVER['REDIRECT_QUERY_STRING'], $query_parts);
				$message_id = $query_parts['message_id'];

				if ($message_id != '') {
					require_once($_SERVER['DOCUMENT_ROOT'] . '/include/files.php');
					freshports_Files($User, $ElementRecord->id, $message_id, $db);
				} else {
					$result = $REQUEST_URI;
				}
			} else {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-port.php');
				freshports_PortDescription($db, $ElementRecord->id);
			}

		} else {
			if ($ElementRecord->IsCategory()) {

				require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-category.php');
				freshports_CategoryByElementID($db, $ElementRecord->id);

			} else {
				# this is a non-port (e.g. /Mk/)
				require_once($_SERVER['DOCUMENT_ROOT'] . '/missing-non-port.php');
				freshports_NonPortDescription($db, $ElementRecord);
			}
		}
	} else {
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
<? echo $result ?>
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
