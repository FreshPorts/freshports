<?
	# $Id: pkg_upload.php,v 1.5.2.4 2002-02-27 02:25:07 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	freshports_Start("Uploading pkg_info",
					"$FreshPortsName - new ports, applications",
					"FreeBSD, index, applications, ports");
$Debug=0;

function StagingAlreadyInUse($WatchListID, $dbh) {

	$Result = 1;	// yes, already in progress.

	$sql = "select WatchListStagingExists($WatchListID)";

	$result = pg_exec($dbh, $sql);
	if ($result && pg_numrows($result)) {
		$row = pg_fetch_array($result, 0);
		if (!$row[0]) {
			$Result = 0;
		}
	} else {
		pg_errormessage() . ' sql = $sql';
	}

	return $Result;
}

	require_once "pkg_process.inc";



?>

<table width="<? echo $TableWidth ?>" border="0" ALIGN="center">
<tr><td VALIGN=TOP>
<TABLE WIDTH="100%">
<TR>
	<? freshports_PageBannerText("Uploading pkg_info"); ?>
<TR><TD>
	<?
#	GLOBAL $WatchListID;

#	echo "WatchListID = $WatchListID<BR>";

	// make sure the POST vars are ok. 
	// check for funny stuff

	global $gDBG;
	$gDBG  = false;
	$clean = false;

	$DisplayStagingArea = 0;

	#
	# is a file name supplied?
	#
	if (StagingAlreadyInUse($WatchListID, $db)) {
		$DisplayStagingArea = 1;
	} else {
		if (trim($pkg_info) != '') {

			require_once "pkg_utils.inc";

			$clean = (strpos($mode, "c") === false) ? false : true;
			$gDBG  = (strpos($mode, "d") === false) ? false : true;

			$retid = -1;
			if (IsLoginValid($user, $pw, $ret_id) || $visitor) {
				$filename = "/tmp/tmp_pkg_output.$user";
				if (!copy($pkg_info, $filename)) {
					?> <pre> Error writing file on server </pre> <?
					exit();
				}


				#
				# $UserID is set by include/getvalues.php
				#
				if ($visitor) $ret_id = $UserID;

				$result = ProcessPackages($WatchListID, $filename, $ret_id, $clean, $db);
				$DisplayStagingArea = 1;

				epp("$user Your Ports Are: ");
				eppp($result['FOUND']);
				epp("<PRE>We were unable to be 100% certain about the following ports.");
			  	epp("It is most likely that you will want them, but you may wish to review.</PRE>");
				eppp($result['GUESS']);
			} else {?>
				<pre>
					Invalid Username and/or Password
	 			</pre> 
			<?}
		} else {
			if ($visitor) {
			?>

				<P>
				You can update your watch lists from the packges database on your computer.  Use the output
				from the <CODE CLASS="code">pkg_info</CODE> command as the input for this page.  FreshPorts
				will take this information, analyze it, and use that data to update your watch list.
				</P>

				<P>Here are the steps you should perform:</P>

				<OL>

				<LI>
				<P>
				You should first issue this command on your FreeBSD computer:
				</P>

				<BLOCKQUOTE>
					<CODE CLASS="code">pkg_info > mypkg_info.txt</CODE>
				</BLOCKQUOTE>

				</LI>

				<LI>
				<P>
				Then click on the browse button and select the file you created in the previous step.
				<P>
				</LI>

				<LI>
				Then click on upload.
				</LI>

				</OL>


				<FORM action='pkg_upload.php?file=1' method='post' enctype='multipart/form-data'>
					<TABLE>
						<!-- <TR><TD>Enter Your Username</TD></TR>  -->
						<!-- <TR><TD><INPUT type="text" name="user" value"" size=20></TD></TR> -->
						<!-- <TR><TD>&nbsp;</TD></TR> -->
						<TR><TD>The file name containing the output from <CODE CLASS="code">pkg_info</CODE>:</TD></TR>
						<TR><TD><INPUT type="file" name="pkg_info" size=40></TD></TR>
						<TR><TD><INPUT type="submit" name="upload" value="Upload" size=20></TD></TR>
					</TABLE>
				</FORM>

				<P>
				If you prefer, you can download the <A HREF="/freshports.tgz">FreshPorts port</A> which will upload
				the output for you.
				</P>
		<? 	} else { ?>
				<P>
				You must <A HREF="login.php">login</A> before you upload your package information.
				</P>
		<?
		 	} 
		}
	}

	if ($DisplayStagingArea) {
		echo '<TABLE ALIGN="center"><TR><TD VALIGN="top"><B>Ports found</B><BR>' . "\n";
		UploadDisplayStagingResultsMatches  ($WatchListID, $db);
		echo '</TD><TD VALIGN="top"><B>These ports are installed on your system but could not be located within FreshPorts.  Perhaps they have
									either been renamed or removed from the ports tree.</B><BR>' . "\n";
		UploadDisplayStagingResultsMatchesNo($WatchListID, $db);
		echo '</TD><TD VALIGN="top"><B>The following ports have been installed multiple times, most definitely with different versions on your system.</B><BR>' . "\n";
		UploadDisplayStagingResultsMatchesDuplicates($WatchListID, $db);
		echo "</TD></TABLE>";
	}
	?>
</TD>
</TR>
</TABLE>
</td>
  <td valign="top" width="*">
    <?
		include("./include/side-bars.php");
    ?>
 </td>
</tr>


 </td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? include("./include/footer.php") ?>
</TD></TR>
</TABLE>

	</BODY>
</HTML>
