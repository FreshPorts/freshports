<?php
	#
	# $Id: commit.php,v 1.11 2013-04-10 18:47:47 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/cache-commit.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

    $Debug = 0;

DEFINE('MAX_PAGE_SIZE',     1000);
DEFINE('DEFAULT_PAGE_SIZE', 500);

DEFINE('NEXT_PAGE',		'Next');

	$message_id = '';
	$page       = '';
	$page_size  = '';
	
	if (IsSet($_GET['message_id'])) $message_id = pg_escape_string($_GET['message_id']);
	if (IsSet($_GET['revision']))   $revision   = pg_escape_string($_GET['revision']);

	$url_query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	parse_str($url_query, $url_args);

	$clean['category'] = $url_args['category'];
	$clean['port']     = $url_args['port'];

	$FilesForJustOnePort = IsSet($url_args['category']) && IsSet($url_args['port']);
	$files = isset($url_args['files']) ? $url_args['files'] : 'n';
	if (in_array($files, array('y', 'yes', 'n', 'no'))) {
		# normalize to just y/n
		if ($files == 'yes') $files = 'y';
		if ($files == 'no')  $files = 'n';
	} else {
		$files = 'n';
	}
	# from here, files must be just 'y' or 'n'

	# if message_id is not provided, but revision is, fetch the corresponding message_id
	# if found, we redirect using the message_id
        if ($message_id == '' && $revision != '')
        {
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');

		$Commit = new Commit($db);
		// Get the message IDs for this revision
		$message_id_array = $Commit->FetchByRevision($revision);
		if (count($message_id_array) == 1)
		{
		  header('Location: /commit.php?message_id=' . $message_id_array[0]);
                  exit;
                }
                freshports_ConditionalGet($Commit->last_modified);

		if ($Debug) echo 'oh... got something back there: <pre>'. print_r($message_id_array, true) . '</pre>';
	}

	$HTML = '';
	# url category=devel&port=p5-Process-Status&files=yes&message_id=202009131257.08DCv7NJ031020@repo.freebsd.org
	$Cache = new CacheCommit();
	# not sure paging is used on commits
	$Cache->PageSize = $User->page_size;
	$result = $Cache->RetrieveCommit($message_id, $clean['category'], $clean['port'], $files);
	if ($Debug) {
		if (!$result) {
			if ($Debug) echo 'found something from the cache for ' . $message_id . "<br>\n";
		} else {
			echo "found NOTHING in cache for '" . $message_id . "'<br>\n";
		}
	}

	if (!$result) {
		$HTML = $Cache->CacheDataGet();
	} else {

		if ($message_id != '') {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit.php');

			$Commit = new Commit($db);
			$Commit->FetchByMessageId($message_id);
			freshports_ConditionalGet($Commit->last_modified);
		}
	}

	if (IsSet($_REQUEST['page']))      $PageNo   = $_REQUEST['page'];
	if (IsSet($_REQUEST['page_size'])) $PageSize = $_REQUEST['page_size'];

	if ($Debug) {
		echo "\$page      = '$page'<br>\n";
		echo "\$page_size = '$page_size'<br>\n";
	}

	if (!IsSet($page) || $page == '') {
		$page = 1;
	}

	if (!IsSet($page_size) || $page_size == '') {
		$page_size = $User->page_size;
	}

	if ($Debug) {
		echo "\$page      = '$page'<br>\n";
		echo "\$page_size = '$page_size'<br>\n";
	}

	SetType($PageNo,   "integer");
	SetType($PageSize, "integer"); 

	if (!IsSet($PageNo)   || !str_is_int("$PageNo")   || $PageNo   < 1) {
		$PageNo = 1;
	}

	if (!IsSet($PageSize) || !str_is_int("$PageSize") || $PageSize < 1 || $PageSize > MAX_PAGE_SIZE) {	
		$PageSize = DEFAULT_PAGE_SIZE;
	}

	if ($Debug) {
		echo "\$PageNo   = '$PageNo'<br>\n";
		echo "\$PageSize = '$PageSize'<br>\n";
	}



	$Title = 'Commit found by commit id';
	if ($Commit->branch != BRANCH_HEAD) {
	  $Title .= ' on branch ' . $Commit->branch;
	}
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

function str_is_int($str) {
	$var = intval($str);
	return ($str == $var);
}

if ($Debug) echo "UserID='$User->id'";

?>

	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(BORDER); ?>

<?
if (file_exists("announcement.txt") && filesize("announcement.txt") > 4) {
?>
  <TR>
    <TD colspan="2">
       <? include ("announcement.txt"); ?>
    </TD>
  </TR>
<?
}
	if ($message_id != '' || $revision != '') {
	
?>

<TR>
	<? echo freshports_PageBannerText($Title, 3); ?>
</TR>

<?php

#	$numrows = $MaxNumberOfPorts;
	$database = $db;
	if ($database ) {
	
		if (!empty($revision) && count($message_id_array)) {
			// we have multiple messages for that commit
			echo '<tr><TD VALIGN="top">';
			echo "We have multiple emails for that revision: ";
			$Commit->FetchNth(0);
			$clean_revision = htmlentities($Commit->svn_revision);
			// e.g. https://svnweb.freebsd.org/base?view=revision&revision=177821
			echo '<a href="https://' . htmlentities($Commit->svn_hostname) . htmlentities($Commit->path_to_repo) . '?view=revision&amp;revision=' . $clean_revision . 
				'">' . $clean_revision . '</a>';

			echo "<ol>\n";
			foreach($message_id_array as $i => $message_id) {
				$Commit->FetchNth($i);
				$clean_message_id = htmlentities($Commit->message_id);
				echo '<li><a href="/commit.php?message_id=' . $clean_message_id . '">' . htmlentities($clean_message_id) . '</a></li>' . "\n";
			}
			echo "</ol></TD></tr>";
		} else {
			if ($HTML == '')  {

				$HTML = '';

				# this comment makes no sense now...
				#
				# we limit the select to recent things by using a date
				# otherwise, it joins the whole table and that takes quite a while
				#
				#$numrows=400;

				$sql = "select freshports_commit_count_elements('" . pg_escape_string($message_id) . "') as count";

				if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

				$result = pg_exec($database, $sql);
				if ($result) {
					$numrows = pg_numrows($result);
					if ($numrows == 1) { 
						$myrow = pg_fetch_array ($result, 0);
					} else {
						die('could not determine the number of commit elements');
					}

					$NumFilesTouched = $myrow['count'];
				}

				$ActualPageNum = ($PageNo - 1 ) * $PageSize;

				$sql ="set client_encoding = 'ISO-8859-15';
SELECT FPC.*, STF.message as stf_message
  FROM freshports_commit('" . pg_escape_string($message_id) . "', " . pg_escape_string($PageSize) . ", " . pg_escape_string($ActualPageNum) . ", $User->id) FPC
 LEFT OUTER JOIN sanity_test_failures STF
    ON FPC.commit_log_id = STF.commit_log_id
ORDER BY port, element_pathname";

				if ($Debug) echo "\n<pre>sql=$sql</pre>\n";

				$result = pg_exec($database, $sql);

				if ($result) {
					$numrows = pg_numrows($result);
					if ($numrows) {
						require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

						$DisplayCommit = new DisplayCommit($database, $result);
						$DisplayCommit->Debug = $Debug;
						$DisplayCommit->SetShowAllPorts(true);
						$DisplayCommit->SetShowEntireCommit(true);
						$DisplayCommit->ShowLinkToSanityTestFailure = true;
						$RetVal = $DisplayCommit->CreateHTML();
	
						$HTML .= $DisplayCommit->HTML;
						$HTML .= '<tr><TD VALIGN="top"><p>Number of ports [&amp; non-ports] in this commit: ' . $NumFilesTouched . '</p></td></tr>';
					} else {
						$HTML .=  '<tr><TD VALIGN="top"><P>Sorry, nothing found in the database....</P>' . "\n";
						$HTML .=  "</TD></tr>";
					}
				} else {
					syslog(LOG_NOTICE, __FILE__ . '::' . __LINE__ . ': ' . pg_last_error());
					exit;
				}

			$HTML .=  "</TABLE>\n";

			$ShowAllFilesURL = '<a href="' . htmlspecialchars($_SERVER['SCRIPT_URL'] . '?message_id=' .  $message_id . '&files=yes') . '">show all files</a>';

			$HideAllFilesURL = '<a href="' . htmlspecialchars($_SERVER['SCRIPT_URL'] . '?message_id=' .  $message_id) . '">hide all files</a>';

			if ($FilesForJustOnePort) {
				// TODO need to validate category/port here!
				require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/categories.php');

				$Category = new Category($database);
				$CategoryID = $Category->FetchByName($clean['category']);
				if (!$CategoryID) {
					die( 'I don\'t know that category: . ' . htmlentities($clean['category']));
				}

				require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/element_record.php');

				$elementName = '/ports/head/' . $clean['category'] . '/' . $clean['port'];

				$Element = new ElementRecord($database);
				$ElementID = $Element->FetchByName($elementName);

				if (!$ElementID) {
					die( 'I don\'t know that port.');
				}

				if (!$Element->IsPort()) {
					die( 'That is not a port.');
				}


				$PortURL = '<a href="/' . $clean['category'] . '/' . $clean['port'] . '/">' . $clean['category'] . '/' . $clean['port'] . '</a>';
				$HTML .=  '<p>Showing files for just one port: <big><b>' . $PortURL . '</b></big></p>';
				$HTML .=  "<p>$ShowAllFilesURL</p>";
			} # FilesForJustOnePort

			# if we ask for files=yes or files=y
			if (!strcasecmp($files, 'y')) {
				$HTML .=  "<p>$HideAllFilesURL</p>";

				$WhichRepo = freshports_MessageIdToRepoName($message_id);

				require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/files.php');

				$Files = new CommitFiles($database);
				$Files->Debug = $Debug;
				$Files->MessageIDSet($message_id);
				$Files->UserIDSet($User->id);
				if (IsSet($url_args['category'])) {
					$Files->CategorySet(pg_escape_string($url_args['category']));
				}
				if (IsSet($url_args['port'])) {
					$Files->PortSet(pg_escape_string($url_args['port']));
				}

				$NumRows = $Files->Fetch();
				if ($Debug) echo 'numrows = ' . $NumRows;

				require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/files-display.php');

				$FilesDisplay = new FilesDisplay($Files->LocalResult);

				$HTML .= '<br>' . $FilesDisplay->CreateHTML($WhichRepo);
#				$HTML = $FilesDisplay->CreateHTML($WhichRepo);
			} else {
				$HTML .=  "<p>$ShowAllFilesURL</p>";
			} # files == 'y'

			# save the HTML
			$Cache->CacheDataSet($HTML);
			$Cache->AddCommit($message_id, $clean['category'], $clean['port'], $files);
			} // $HTML != ''
		} # count($message_id_array)

		echo $HTML;

	} else {
		$HTML .=  "no connection";
	} # if ($database )

	} else {
		echo '<tr><td valign="top" width="100%">nothing supplied, nothing found!</td>';
	} # if ($message_id != '' || $revision != '')


?>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">

	<?
	echo freshports_SideBar();
	?>

  </td>
</TR>
</TABLE>

<BR>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
