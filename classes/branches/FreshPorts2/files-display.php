<?php
	#
	# $Id: files-display.php,v 1.1.2.2 2006-11-28 20:55:32 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

// base class for displaying files
class FilesDisplay {

	var $ResultSet;
	var $HTML;

	var $Debug = 0;

	function FilesDisplay($ResultSet) {
		$this->ResultSet = $ResultSet;
		$this->HTML       = '';
	}

	function CreateHTML() {
		GLOBAL $TableWidth;
		GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
		GLOBAL $DaysMarkedAsNew;

		$this->HTML = '';

		if (!$this->ResultSet) {
            die("read from database failed");
			exit;
		}

		$NumRows = pg_numrows($this->ResultSet);
		if ($this->Debug) echo __FILE__ . ':' . __LINE__ . " Number of rows = $NumRows<br>\n";
		if (!$NumRows) { 
			$this->HTML = "<TR><TD>\n<P>Sorry, nothing found in the database....</P>\n</td></tr>\n";
			return 1;
		}

		$this->HTML .= '
<table border="1" width="100%" CELLSPACING="0" CELLPADDING="5">
<TR>
';
		switch ($NumRows) {
			case 0:
				$title = 'no files found';
				break;

			case 1:
				$title = '1 file found';
				break;

			default:
				$title =  $NumRows . ' files found';
		}

		$this->HTML .= freshports_PageBannerText($title, 3);

		$this->HTML .= "
		<TR>
			<TD><b>Action</b></TD><TD><B>Revision</B></TD><TD><b>File</b></TD>
		</TR>\n";

		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array($this->ResultSet, $i);
			$this->HTML .= "<TR>\n";

			switch ($myrow["change_type"]) {
				case "M":
					$Change_Type = "modify";
					break;

				case "A":
					$Change_Type = "import"; 
					break;

				case "R":
					$Change_Type = "remove"; 
					break;

				default:
					$Change_Type = $myrow["change_type"] ; 
			}

			$this->HTML .= "  <TD>" . $Change_Type . "</TD>";
			$this->HTML .= "  <TD>" . $myrow["revision_name"] . "</TD>";
			$this->HTML .= '  <TD WIDTH="100%" VALIGN="middle">';
			$this->HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '?annotate=' . $myrow["revision_name"] . '">';
			$this->HTML .= freshports_Revision_Icon() . '</a> ';
			$this->HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '#rev'       . $myrow["revision_name"] . '">';

			$this->HTML .= '<CODE CLASS="code">' . $myrow["pathname"] . "</CODE></A></TD>";
			$this->HTML .= "</TR>\n";
		}
		
		$this->HTML .= "</table>";
		
		return $this->HTML;
	}

}
?>