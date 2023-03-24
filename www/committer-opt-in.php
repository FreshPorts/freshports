<?php
	#
	# $Id: committer-opt-in.php,v 1.5 2009-01-08 19:47:08 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	if (IN_MAINTENANCE_MODE) {
                header('Location: /' . MAINTENANCE_PAGE, TRUE, 307);
	}

	const FREEBSD_EMAIL_REGEX = '/.*@FreeBSD.org/i';

	$Title = 'Committer opt-in';
	freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

	if (IsSet($User->email) && !preg_match(FREEBSD_EMAIL_REGEX, $User->email)) {
		# nothing yet
	} else {
		if (IsSet($_POST["subscribe"]) && $_POST["subscribe"] && !empty($visitor)) {
			# if not an email address
			if (strrpos($_POST["email"], '@') === false) {
				$committer = $_POST["email"];
				
		    		$sql = 'insert into committer_notify (user_id, committer, status) values ($User->id, $1, $2)';

				if ($Debug) echo "sql=$sql<br>\n";

				$result = pg_query_params($db, $sql, array($committer. 'A'))  or die("insert query failed " . pg_last_error($db));

			    	if (!$result) {
		        		die("determine committer subscribe failed " . pg_last_error($db));
	    			}
			} else {
				die("please enter just your login, not your email address");
				}
		}

		if (IsSet($_POST["unsubscribe"]) && $_POST["unsubscribe"] && !empty($visitor)) {
			$sql = 'delete from committer_notify where user_id = $1';

			if ($Debug) echo "sql=$sql<br>\n";

			$result = pg_query_params($db, $sql, array($User->id))  or die("delete query failed " . pg_last_error($db));

			if (!$result) {
				die("determine committer unsubscribe failed " . pg_last_error($db));
				}
			}


		if (IsSet($_POST["update"]) && $_POST["update"] && !empty($visitor)) {
			$committer = $_POST["email"];
			$sql = 'update committer_notify 
				set committer = $1
				where user_id   = $2';

			if ($Debug) echo "sql=$sql<br>\n";

			$result = pg_query_params($db, $sql, array($committer, $User->id))  or die("update query failed " . pg_last_error($db));

			if (!$result) {
				die("determine committer subscribe failed " . pg_last_error($db));
			}
		}
	}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td class="content">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<tr>
	<?php echo freshports_PageBannerText("Committer opt-in"); ?>
</tr>

<tr><td class="textcontent">
<P>
<?php
	if (!IsSet($User->email) || !preg_match(FREEBSD_EMAIL_REGEX, $User->email)) {
?>
<p><b><big>This page only works if you are logged in and using a @FreeBSD.org email address.</big></b></p>
<?php
	}
?>

Mistakes happen.  And when they do, they are best corrected quickly.  To that end, FreshPorts
provides an opt-in service for all FreeBSD committers.  If you subscribe to this service,
FreshPorts will notify you of any problems it encounters when processing a port change
which you committed.  In the past, such problems are related to syntax errors in the Makefile.
</P>

<P>
One committer referred to this service as an automated nagging mentor...
</P>
</td></tr>

<?php

if (IsSet($User->email)) {
?>

<tr>
	<?php
	echo freshports_PageBannerText("Your opt-in status");
	?>
</tr>

<tr><td>
<P>
<?php
if (!empty($visitor)) {
	$sql = 'select committer
			  from committer_notify
			 where user_id = $1';

	if ($Debug) {
		echo "sql=$sql<br>\n";
	}

	$result = pg_query_params($db, $sql, array($$User->id))  or die("select query failed " . pg_last_error($db));

	if ($result) {
		echo 'You are: ';
		if ($Debug) echo "we found a result there...\n<br>";
		$numrows = pg_num_rows($result);
		if ($numrows) {
			$myrow = pg_fetch_array ($result, 0);
			if ($myrow) {
				echo 'subscribed';
				$committer = $myrow["committer"];
			}
		} else {
			echo 'not subscribed';
			$committer = preg_replace('|^(.*)@FreeBSD\.org|i', '\\1', $User->email);
		}
	}

	if (!IsSet($committer)) {
		$committer = substr($email, 0, strrpos($email, '@'));
	}
}
?>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
               your freefall login:
               <INPUT SIZE="35" NAME="email" VALUE="<?php echo $committer ?? '' ?>"><br><br>
<?php
			if (IsSet($numrows) && $numrows) {
?>
				<INPUT TYPE="submit" VALUE="update"      NAME="Update my address"> 
				<INPUT TYPE="submit" VALUE="unsubscribe" NAME="unsubscribe">
<?php
			} else {
?>
				<INPUT TYPE="submit"  VALUE="subscribe" NAME="subscribe">
<?php
			}
?>

</FORM>

<p>
<BIG>Please do not include @FreeBSD.org in your login name.</BIG>
</p>

</td></tr>

<?php
} // $User->email
?>

</table>
</td>

  <td class="sidebar">
	<?php
	echo freshports_SideBar();
	?>
  </td>

</table>

<?php
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
