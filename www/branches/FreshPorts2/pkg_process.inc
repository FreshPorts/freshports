<?
	# $Id: pkg_process.inc,v 1.5.2.13 2002-12-06 14:39:52 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited
	#

	require($_SERVER["DOCUMENT_ROOT"] . "/../classes/ports.php");	

$Debug=0;

function LinkToPort($category, $port) {
	return '<A HREF="/' . $category . '/' . $port . '/">' . $category . '/' . $port . '</A>';
}


function HandleFileUpload($FormFileName, $Destination) {
	#
	# pass in the name of the field on the form into which the user supplies
	# the file name.
	#
	global $HTTP_POST_FILES;

	if (is_uploaded_file($HTTP_POST_FILES[$FormFileName]['tmp_name'])) {
		$filename = $HTTP_POST_FILES[$FormFileName]['tmp_name'];
#		print "$filename was uploaded successfully<BR>";
		$realname = $HTTP_POST_FILES[$FormFileName]['name'];
#		print "realname is $realname<BR>";
#		print "copying file to $Destination<BR>";
		copy($HTTP_POST_FILES[$FormFileName]['tmp_name'], $Destination);
		$result = 1;
	} else {
		DisplayError("No file was uploaded.  Make sure the file name is correct, that the file is non-empty, and that the 
			file size is less than 40KB (this should be sufficient for any
			installation).  Verify you have correctly followed the instructions.<P>Please click back, check the file, and try again.");
		$result = 0;
	}

	return $result;
}

function DisplayError($error) {
?>
	<TABLE WIDTH="100%" BORDER="1" ALIGN="center" CELLPADDING="1" CELLSPACING="0" BORDER="1">
	<TR><TD VALIGN=TOP>
		<TABLE WIDTH="100%">
			<TR>
			    <? freshports_PageBannerText("NOTICE"); ?>
			</TR>

			<TR BGCOLOR="#ffffff">
				<TD>
					<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" BORDER="0">
						<TR VALIGN="top">
							<TD><IMG SRC="/images/warning.gif"></TD>
							<TD width=100%>
							<? echo $error ?>
							</TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
		</TABLE>
	</TD>
	</TR>
	</TABLE>

<?
}

// {{{ function ProcessPackages
/**
* Process the package file (this will be slimmed down, when there is no more debug
*
* @param	$filename - name of file to process
* @return	an array of ports
*
*/

function ProcessPackages($UserID, $filename, $dbh) {
	// profiling info {{{
	// }}}
	global $gDBG;

	$ok			= true;
	$gMyPorts	= array();
	$tempArray	= array();

	$gFid = fopen($filename, 'r');
	if (!$gFid) { return false; }

	$starttime = time();
	$portcount = 0;
	while (!feof($gFid)) {
		$buffer = fgets($gFid,4096);
		if (!$buffer) {
			continue;
		}

		$portcount++;
		// Get the package name

		$buffer = trim($buffer);
		if (IsSet($PortArray[$buffer])) {
			$PortArray[$buffer] = $PortArray[$buffer] + 1;
		} else {
			$PortArray[$buffer] = 1;
		}

	}

	pg_exec ($dbh, "begin");

	while (list ($key, $count) = each ($PortArray)) {

#	echo "'$key'<BR>\n";
		# trimming, just in case...
		$CategoryPort = explode("/", trim($key));
		if (count($CategoryPort) != 2) {
			#
			# in an ideal world, this error message would not be in this function, but in the caller function
			#
			DisplayError('<P>OUCH, That does not look like the right output to me.  Please ensure you
								have the correct file format and try again (see step 1). </P>
							<P>This is the first line I found: <CODE CLASS="code">' . $key . '</CODE></P>');
			$ok = FALSE;
			break;
		}

		$Category = $CategoryPort[0];
		$Port     = $CategoryPort[1];

		$sql = 'select WatchListStagingAddItem(' . AddSlashes($UserID) . ', \'' . 
					AddSlashes($Category) . '\', \'' . AddSlashes($Port) . '\', ' . AddSlashes($count) . ')';
#		echo "\$sql = '$sql'\n<BR>";
		$result = pg_exec($dbh, $sql);
		if ($result && pg_numrows($result)) {
			# do nothing
		} else {
			echo pg_errormessage() . " sql = $sql";
			$ok = FALSE;
			break;
		}
	}
		pg_exec ($dbh, "commit");

	if ($ok) {
		# All is well.

		$sql = "select WatchListStagingProcess($UserID)";
		$result = pg_exec($dbh, $sql);
		if ($result && pg_numrows($result)) {
			# do nothing.  All is well
		} else {
			echo pg_errormessage() . " sql = $sql";
			$ok = FALSE;
		}
	}

	if ($ok) {
		pg_exec ($dbh, "commit");
	} else {
		pg_exec ($dbh, "rollback");
	}

	return $ok;
}
// }}}

function UploadDisplayStagingResultsMatches($UserID, $dbh) {

	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id, 
					watch_list_element.element_id AS onwatchlist
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_element
						ON  watch_list_staging.element_id    = watch_list_element.element_id
						AND watch_list_staging.watch_list_id = watch_list_element.watch_list_id
			  WHERE $UserID    = watch_list_staging.user_id
			    AND item_count = 1
				AND watch_list_staging.element_id IS NOT NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">
			<TR><TD COLSPAN="2"><B>Port name</B> <SMALL>(<? echo $numrows ?> ports)</SMALL></TD><TD ALIGN="center"><SMALL>Add</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);
				echo '<TR>';

				echo '<TD>';
				echo LinkToPort($row["category"], $row["port"]);
				echo '</TD>';

				echo '<TD ALIGN="center">';
				if ($row["onwatchlist"]) {
					echo 'W';
				} else {
					echo '&nbsp;';
				}
				echo '</TD>';

				echo '<TD ALIGN="center"><INPUT TYPE="checkbox" NAME="ports[]" VALUE="' . $row["element_id"] . '" CHECKED></TD>';

				echo '</TR>' . "\n";
			}
			
			echo '</TABLE>';

#			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "Nothing you uploaded was found within our database.";
		}
	} else {
		echo "nothing found!";
	}

}

function UploadDisplayStagingResultsMatchesNo($UserID, $dbh) {

	$begintime = time();
	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			   FROM	watch_list_staging
			  WHERE $UserID    = watch_list_staging.user_id
			    AND item_count = 1
				AND watch_list_staging.element_id IS NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">
			<TR><TD><B>Port name</B> <SMALL>(<? echo $numrows ?> ports)</SMALL></TD><TD><SMALL>search</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);
				echo '<TR>';
				echo '<TD><FONT COLOR="red">' . $row["category"] . '/' . $row["port"] . '</FONT></TD>';
				echo '<TD ALIGN="center"><A HREF="/search.php?query=' . $row["port"] . '&stype=name&num=10&method=soundex&staging=1">?</A></TD>';
				echo '</TR>';
			}
			echo '</TABLE>';

#			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "Everything in your uploaded list was located within our database.";
		}
	} else {
		echo "nothing found!";
	}

}

function UploadDisplayStagingResultsMatchesDuplicates($UserID, $dbh) {

	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_element
						ON  watch_list_staging.element_id    = watch_list_element.element_id
						AND watch_list_staging.watch_list_id = watch_list_element.watch_list_id
			  WHERE $UserID    = watch_list_staging.user_id
			    AND item_count > 1
				AND watch_list_staging.element_id IS NOT NULL
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">
			<TR><TD><B>Port name</B></TD><TD><B>Count</B></TD><TD ALIGN="center"><SMALL>Add</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);
				echo '<TR>';

				echo '<TD>';
				echo LinkToPort($row["category"], $row["port"]);
				echo '</TD>';

				echo '<TD ALIGN="center">';
				echo $row["item_count"];
				echo '</TD>';

				echo '<TD ALIGN="center"><INPUT TYPE="checkbox" NAME="ports[]" VALUE="' . $row["element_id"] . '" CHECKED></TD>';

				echo '</TR>';
			
			}
			echo '</TABLE>';

#			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "no Duplicates found";
		}
	} else {
		echo "nothing found!";
	}

}

function UploadDisplayWatchListItemsNotInStagingArea($UserID, $dbh) {

	$begintime = time();
	$sql = " SELECT categories.name as category, element.name as port, ports.element_id
			     FROM ports, categories, watch_list_element, element
			    WHERE $UserID          = watch_list_element.user_id
				   AND ports.element_id = watch_list_element.element_id
				   AND ports.element_id = element.id
				   AND categories.id    = ports.category_id
				   AND NOT EXISTS (
					SELECT element_id
					  FROM watch_list_staging
					 WHERE watch_list_staging.element_id = watch_list_element.element_id
				)
			  ORDER BY port, category";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5" BORDERCOLOR="#a2a2a2" BORDERCOLORDARK="#a2a2a2" BORDERCOLORLIGHT="#a2a2a2">
			<TR><TD><B>Port name</TD><TD ALIGN="center"><SMALL>Add</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);

				echo '<TD ALIGN="left">';
				echo LinkToPort($row["category"], $row["port"]);
				echo '</TD>';

				echo '<TD ALIGN="center"><INPUT TYPE="checkbox" NAME="ports[]" VALUE="' . $row["element_id"] . '" CHECKED></TD>';

				echo '</TR>' . "\n";
			}
			echo '</TABLE>';

#			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "Every item on your watch list is accounted for on your pkg_info list.";
		}
	} else {
		echo "nothing found!";
	}

}


function MoveStagingToWatchList($WatchListID, $ports, $dbh) {
	$ok  = TRUE;
	
	$sql = "SELECT WatchListUserID($WatchListID)";
	$result = pg_exec($dbh, $sql);
	if ($result) {
		$row = pg_fetch_array($result, 0);
		$UserID = $row[0];
	} else {
		$ok = FALSE;
	}	
	

	$sql = "BEGIN";
	$result = pg_exec($dbh, $sql);
	if (!$result) {
		$ok = FALSE;
	}

	if ($ok) {
		$sql = "select WatchListDeleteAllElements($WatchListID)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}

	if ($ok && $ports) {
		reset($ports);
		$PortCount = 0;
		while (list($key, $value) = each($ports)) {
			$PortCount++;

#			echo "\$key='$key', \$value='$value'<BR>\n";

			$sql = 'insert into watch_list_element (watch_list_id, element_id)
						values (' . AddSlashes($WatchListID) . ' , ' . AddSlashes($value) . ')';

#			echo "\$sql='$sql'<BR>\n";

			$result = pg_exec($dbh, $sql);
			${"port_".$value} = 1;

			if (!$result) {
				echo 'ouch, that failed';
				$ok = FALSE;
				break;
			}				
		}
	}

	if ($ok) {
		reset($ports);
		$sql = "select WatchListStagingLogWatch($UserID, $PortCount)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}			
			
	if ($ok) {
		pg_exec($dbh, "COMMIT");
	} else {
		echo pg_errormessage() . " sql = $sql";
		pg_exec($dbh, "ROLLBACK");
	}

	return $ok;
}

function StagingAreaClear($UserID, $dbh) {
	$ok = TRUE;

	$result = pg_exec($dbh, "BEGIN");
	if ($result) {
		$sql = "select WatchListStagingClear($UserID)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	} else {
		$ok = FALSE;
	}

	if ($result && $ok) {
		pg_exec($dbh, "COMMIT");
	} else {
		echo pg_errormessage() . " sql = $sql";
		pg_exec($dbh, "ROLLBACK");
	}

	return $ok;		
}


?>
