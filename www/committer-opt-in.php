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

	freshports_Start('Committer opt-in',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

	if (!eregi(".*@FreeBSD.org", $User->email)) {
		echo 'Why are you here?';
		exit;
   }

	if (IsSet($_POST["subscribe"]) && $_POST["subscribe"] && !empty($visitor)) {
	    # if not an email address
	    if (strrpos($_POST["email"], '@') === false) {
    		$committer = pg_escape_string($_POST["email"]);
    		$sql = "insert into committer_notify (user_id, committer, status)
	    			values ($User->id, '$committer', 'A')";

    		if ($Debug) {
	    		echo "sql=$sql<br>\n";
            }

    		$result = pg_exec($db, $sql) or die("insert query failed " . pg_errormessage());

	    	if (!$result) {
	        	die("determine committer subscribe failed " . pg_errormessage());
    		}
        } else {
          die("please enter just your login, not your email address");
        }
	}

	if (IsSet($_POST["unsubscribe"]) && $_POST["unsubscribe"] && !empty($visitor)) {
		$committer = pg_escape_string($_POST["email"]);
		$sql = "delete from committer_notify where user_id = $User->id";

		if ($Debug) {
			echo "sql=$sql<br>\n";
		}

		$result = pg_exec($db, $sql) or die("insert query failed " . pg_errormessage());

		if (!$result) {
			die("determine committer unsubscribe failed " . pg_errormessage());
		}

		Unset($committer);
	}


	if (IsSet($_POST["update"]) && $_POST["update"] && !empty($visitor)) {
		$committer = pg_escape_string($_POST["email"]);
		$sql = "update committer_notify 
                   set committer = '$committer'
                 where user_id   = $User->id";

		if ($Debug) {
			echo "sql=$sql<br>\n";
		}

		$result = pg_exec($db, $sql) or die("insert query failed " . pg_errormessage());

		if (!$result) {
			die("determine committer subscribe failed " . pg_errormessage());
		}
	}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(NOBORDER); ?>
<TR>
	<? echo freshports_PageBannerText("Committer opt-in"); ?>
</TR>

<TR><TD>
<P>
Mistakes happen.  And when they do, they are best corrected quickly.  To that end, FreshPorts
provides an opt-in service for all FreeBSD committers.  If you subscribe to this service,
FreshPorts will notify you of any problems it encounters when processing a port change
which you committed.  In the past, such problems are related to syntax errors in the Makefile.
</P>

<P>
One committer referred to this service as a automated nagging mentor...
</P>
</TD></TR>

<?
	echo freshports_BannerSpace();
?>

<TR>
	<?
	echo freshports_PageBannerText("Your opt-in status");
	?>
</TR>

<TR><TD>
<P>
<?
if (!empty($visitor)) {
	$sql = "select committer
			  from committer_notify
			 where user_id = $User->id";

	if ($Debug) {
		echo "sql=$sql<br>\n";
	}

	$result = pg_exec($db, $sql) or die("determine committer query failed " . pg_errormessage());

	if ($result) {
		echo 'You are: ';
		if ($Debug) echo "we found a result there...\n<br>";
		$numrows = pg_numrows($result);
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
               <INPUT SIZE="35" NAME="email" VALUE="<?echo $committer ?>"><BR><BR>
<?
			if ($numrows) {
?>
				<INPUT TYPE="submit" VALUE="update"      NAME="Update my address"> 
				<INPUT TYPE="submit" VALUE="unsubscribe" NAME="unsubscribe">
<?
			} else {
?>
				<INPUT TYPE="submit"  VALUE="subscribe" NAME="subscribe">
<?
			}
?>

</FORM>

<p>
<BIG>Please do not include @FreeBSD.org in your login name.</BIG>
</p>

</TD></TR>

</TABLE>
</TD>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TABLE>

<?
echo freshports_ShowFooter();
?>

</BODY>
</HTML>
