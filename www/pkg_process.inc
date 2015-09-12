<?php
	#
	# $Id: pkg_process.inc,v 1.6 2006-12-17 12:06:13 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
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
#	global $HTTP_POST_FILES;

	if (is_uploaded_file($_FILES[$FormFileName]['tmp_name'])) {
		$filename = $_FILES[$FormFileName]['tmp_name'];
#		print "$filename was uploaded successfully<BR>";
		$realname = $_FILES[$FormFileName]['name'];
#		print "realname is $realname<BR>";
#		print "copying file to $Destination<BR>";
		copy($_FILES[$FormFileName]['tmp_name'], $Destination);
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
	<TABLE WIDTH="100%" ALIGN="center" CELLPADDING="1" CELLSPACING="0" BORDER="1">
	<TR><TD VALIGN=TOP>
		<TABLE WIDTH="100%">
			<TR>
			    <? echo freshports_PageBannerText("NOTICE"); ?>
			</TR>

			<TR BGCOLOR="#ffffff">
				<TD>
					<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" BORDER="0">
						<TR VALIGN="top">
							<TD VALIGN="middle"><IMG SRC="/images/warning.gif" ALT="warning"></TD>
							<TD VALIGN="middle" width="100%">
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



function ConvertFileContentsToArray($filename) {
	$PortArray = array();

	$gFid = fopen($filename, 'r');
	if (!$gFid) {
		return false;
	}

	while (!feof($gFid)) {
		$buffer = fgets($gFid, 4096);
		if (!$buffer) {
			continue;
		}

		// Get the package name

		$buffer = trim($buffer);
		if ($buffer == '') {
			continue;
		}
		if (IsSet($PortArray[$buffer])) {
			$PortArray[$buffer] = $PortArray[$buffer] + 1;
		} else {
			$PortArray[$buffer] = 1;
		}
	}

	return $PortArray;

}


function ConvertStringToArray($PkgInfoString) {
	$PortArray = array();

	$Lines = explode("\n", $PkgInfoString);
	while (list($key, $CatPort) = each($Lines)) {
		// Get the package name

		$CatPort = trim($CatPort);
		if ($CatPort == '') {
			continue;
		}
		if (IsSet($PortArray[$CatPort])) {
			$PortArray[$CatPort] = $PortArray[$CatPort] + 1;
		} else {
			$PortArray[$CatPort] = 1;
		}
	}

	return $PortArray;

}


// {{{ function ProcessPackages
/**
* Process the package file (this will be slimmed down, when there is no more debug
*
* @param	$filename - name of file to process
* @return	an array of ports
*
*/

function ProcessPackages($UserID, $PortArray, $dbh) {
	// profiling info {{{
	// }}}

	$ok = true;

	pg_exec($dbh, "BEGIN");

	while (list ($key, $count) = each ($PortArray)) {

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

		$sql = 'select WatchListStagingAddItem(' . pg_escape_string($UserID) . ', \'' . 
					pg_escape_string($Category) . '\', \'' . pg_escape_string($Port) . '\', ' . pg_escape_string($count) . ')';
		$result = pg_exec($dbh, $sql);
		if ($result && pg_numrows($result)) {
			# do nothing
		} else {
			echo pg_errormessage() . " sql = $sql";
			$ok = FALSE;
			break;
		}
	}

	if ($ok) {
		# All is well.

		$sql = "select WatchListStagingProcess(" . pg_escape_string($UserID) . ")";
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

function UploadDisplayStagingResultsMatches($UserID, $WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT	category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id, 
					watch_list_element.element_id AS onwatchlist
			   FROM	watch_list_staging LEFT OUTER JOIN watch_list_element
						ON  watch_list_staging.element_id    = watch_list_element.element_id
						AND $WatchListID                     = watch_list_element.watch_list_id
			  WHERE " . pg_escape_string($UserID) . "    = watch_list_staging.user_id
			    AND item_count = 1
				AND watch_list_staging.element_id IS NOT NULL
			  ORDER BY category, port";

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5">
			<TR><TD COLSPAN="2"><B>Port name</B> <SMALL>(<? echo $numrows ?> port<?php if ($numrows > 1) echo 's'; ?>)</SMALL></TD><TD ALIGN="center"><SMALL>Add</SMALL></TD></TR>

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
		echo "<pre>$sql</pre>";
	}

}

function UploadDisplayStagingResultsMatchesNo($UserID, $dbh) {

	$begintime = time();
	$begintime = time();
	$sql = " SELECT category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			     FROM watch_list_staging
			    WHERE " . pg_escape_string($UserID) . "    = watch_list_staging.user_id
			      AND item_count = 1
				   AND watch_list_staging.element_id IS NULL
			 ORDER BY category, port";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5">
			<TR><TD><B>Port name</B> <SMALL>(<? echo $numrows ?> port<?php if ($numrows > 1) echo 's'; ?>)</SMALL></TD><TD><SMALL>search</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);
				echo '<TR>';
				echo '<TD><FONT COLOR="red">' . $row["category"] . '/' . $row["port"] . '</FONT></TD>';
				echo '<TD ALIGN="center"><A HREF="/search.php?query=' . $row["port"] . '&amp;stype=name&amp;num=10&amp;method=soundex&amp;staging=1">?</A></TD>';
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

function UploadDisplayStagingResultsMatchesDuplicates($UserID, $WatchListID, $dbh) {

	$begintime = time();
	$sql = " SELECT category, port, item_count, from_pkg_info, from_watch_list, watch_list_staging.element_id
			     FROM watch_list_staging LEFT OUTER JOIN watch_list_element
					     	 ON  watch_list_staging.element_id = watch_list_element.element_id
						    AND $WatchListID                   = watch_list_element.watch_list_id
			    WHERE " . pg_escape_string($UserID) . "    = watch_list_staging.user_id
			      AND item_count > 1
				   AND watch_list_staging.element_id IS NOT NULL
			 ORDER BY category, port";

#	echo $sql;

	$result = pg_exec($dbh, $sql);

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5">
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

function UploadDisplayWatchListItemsNotInStagingArea($WatchListID, $dbh) {
#	syslog(LOG_NOTICE, "UploadDisplayWatchListItemsNotInStagingArea for WatchListID: $WatchListID");

	$begintime = time();
	$sql = " SELECT categories.name as category, 
	                element.name as port, 
	                ports.element_id
			     FROM ports, categories, watch_list_element, element
			    WHERE $WatchListID      = watch_list_element.watch_list_id
				   AND ports.element_id = watch_list_element.element_id
				   AND ports.element_id = element.id
				   AND categories.id    = ports.category_id
				   AND NOT EXISTS (
					SELECT element_id
					  FROM watch_list_staging
					 WHERE watch_list_staging.element_id = watch_list_element.element_id
				)
			  ORDER BY port, category";

#	syslog(LOG_NOTICE, $sql);

	$result = pg_exec($dbh, $sql);
#	syslog(LOG_NOTICE, "UploadDisplayWatchListItemsNotInStagingArea for WatchListID: $WatchListID");

	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows) {
			?>

			<TABLE ALIGN="center" BORDER="1" CELLSPACING="0" CELLPADDING="5">
			<TR><TD><B>Port name</B></TD><TD ALIGN="center"><SMALL>Add</SMALL></TD></TR>

			<?
			for ($i = 0; $i < $numrows; $i++) {
				$row = pg_fetch_array($result, $i);

				echo '<TR><TD ALIGN="left">';
				echo LinkToPort($row["category"], $row["port"]);
				echo '</TD>';

				echo '<TD ALIGN="center"><INPUT TYPE="checkbox" NAME="ports[]" VALUE="' . $row["element_id"] . '" CHECKED></TD>';

				echo '</TR>' . "\n";
			}
			echo '</TABLE>';

#			echo 'that took '. (time() - $begintime) . ' seconds to process ' . $numrows . ' rows';
		} else {
			echo "There is nothing on this watch list which does not appear in your staging area.";
		}
	} else {
		echo "nothing found!";
	}

}


function MoveStagingToWatchList($UserID, $WatchListID, $dbh) {
	$ok  = TRUE;

	$UserID      = pg_escape_string($UserID);
	$WatchListID = pg_escape_string($WatchListID);

	$Debug = 0;

	if ($Debug) syslog(LOG_NOTICE, 'begin');
	$result = pg_exec($dbh, 'BEGIN');
	if (!$result) {
		$ok = FALSE;
	}

#	if ($ok) {
#		if ($Debug) syslog(LOG_NOTICE, 'WatchListDeleteAllElements: line ' . __LINE__);
#		$sql = "select WatchListDeleteAllElements($WatchListID)";
#		$result = pg_exec($dbh, $sql);
#		if (!$result) {
#			$ok = FALSE;
#		}
#	}

	$PortCount = 0;
	if ($ok) {
		if ($Debug) syslog(LOG_NOTICE, 'MoveStagingToWatchList: line ' . __LINE__);
		$sql = "select MigrateStagingAreaToWatchList($WatchListID)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}

	if ($ok) {
		$sql = "select WatchListStagingLogWatch($UserID, $PortCount)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}

	# clear out the staging area

	if ($ok) {
		$sql = "select WatchListStagingClear($UserID)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}

	if ($ok) {
		if ($Debug) syslog(LOG_NOTICE, 'commit');
		pg_exec($dbh, "COMMIT");
	} else {
		echo pg_errormessage() . " sql = $sql";
		pg_exec($dbh, "ROLLBACK");
	}

	return $ok;
}

function CopyStagingToWatchList($dbh, $UserID, $WatchListID, $Overwrite = TRUE) {
	$ok  = TRUE;
	$begintime = time();

	$UserID      = pg_escape_string($UserID);
	$WatchListID = pg_escape_string($WatchListID);

	$result = pg_exec($dbh, 'BEGIN');
	if (!$result) {
		$ok = FALSE;
	}

	if ($ok && $Overwrite) {
		$sql = "select WatchListDeleteAllElements($WatchListID)";
		$result = pg_exec($dbh, $sql);
		if (!$result) {
			$ok = FALSE;
		}
	}

	if ($ok) {
		$sql = " INSERT INTO watch_list_element
                 SELECT $WatchListID, element_id
                   FROM watch_list_staging
                  WHERE element_id IS NOT NULL
                    AND user_id = $UserID
";

		if (!$Overwrite) {
			$sql .= "EXCEPT SELECT watch_list_id, element_id
                              FROM watch_list_element
                             WHERE watch_list_id = $WatchListID
";
		}
	}

#	syslog(LOG_NOTICE, $sql);

	$result = pg_exec($dbh, $sql);
#	syslog(LOG_NOTICE, "UploadDisplayWatchListItemsNotInStagingArea for WatchListID: $WatchListID");

	if (!$result) {
		$ok = FALSE;
	}				

	if ($ok) {
#		syslog(LOG_NOTICE, 'commit');
		pg_exec($dbh, "COMMIT");
	} else {
		echo pg_errormessage() . " sql = $sql";
		pg_exec($dbh, "ROLLBACK");
	}

	return $ok;

}


function StagingAreaClear($UserID, $dbh) {
	$ok = TRUE;

	$UserID = pg_escape_string($UserID);

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

