<?
	# $Id: committer-opt-in.php,v 1.1.2.5 2002-12-10 04:00:11 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/common.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/freshports.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/databaselogin.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/include/getvalues.php");

	freshports_Start("Committer opt-in",
					"freshports - new ports, applications",
					"FreeBSD, index, applications, ports");

	if (!eregi(".*@FreeBSD.org", $email)) {
		echo 'Why are you here?';
		exit;
   }

	if ($_POST["subscribe"] && !empty($visitor)) {
		$committer = AddSlashes($_POST["email"]);
		$sql = "insert into committer_notify (user_id, committer, status)
				values ($User->id, '$committer', 'A')";

		if ($Debug) {
			echo "sql=$sql<br>\n";
		}

		$result = pg_exec($db, $sql) or die("insert query failed " . pg_errormessage());

		if (!$result) {
			die("determine committer subscribe failed " . pg_errormessage());
		}
	}

	if ($_POST["unsubscribe"] && !empty($visitor)) {
		$committer = AddSlashes($_POST["email"]);
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


	if ($_POST["update"] && !empty($visitor)) {
		$committer = AddSlashes($_POST["email"]);
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
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE>
<TR>
	<? freshports_PageBannerText("Committer opt-in"); ?>
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

<TR>
	<?
	freshports_BannerSpace();
	freshports_PageBannerText("Your opt-in status");
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
		}
	}

	if (!IsSet($committer)) {
		$committer = substr($email, 0, strrpos($email, '@'));
	}
}
?>

<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" NAME=f>
               committer id:
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

</P>
</TD></TR>

</TABLE>
</TD>
  <TD VALIGN="top" WIDTH="*" ALIGN="center">
    <?
       $ShowPoweredBy = 1;
       include($_SERVER['DOCUMENT_ROOT'] . "/include/side-bars.php");
    ?>
 </TD>
</TABLE>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include($_SERVER['DOCUMENT_ROOT'] . "/include/footer.php") ?>
</TD></TR>
</TABLE>

</BODY>
</HTML>
