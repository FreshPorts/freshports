<?php
	#
	# $Id: files-display.php,v 1.5 2012-07-16 14:53:39 dan Exp $
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

	function CreateHTML($WhichRepo) {
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

		$this->HTML .= freshports_PageBannerText($title, 4);

		$this->HTML .= "
		<TR>
			<TD><b>Action</b></TD><TD><B>Revision</B></TD><td><b>Links</b></td><TD><b>File</b></TD>
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
			$this->HTML .= '  <TD>' . $myrow["revision_name"];
            $this->HTML .= "</TD>";
            
            $this->HTML .= '<td>';
            if ( $Change_Type == "modify" ) {
                switch($WhichRepo)
                {
                    case FREEBSD_REPO_CVS:
                        $this->HTML .= ' ';
    	        		$previousRevision =  $this->GetPreviousRevision( $myrow["revision_name"] );
    	       		    $this->HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '.diff?r1=' . $previousRevision . ';r2=' . $myrow["revision_name"] . '">';
        		    	$this->HTML .= freshports_Diff_Icon() . '</a> ';
        		    	break;

                    case FREEBSD_REPO_SVN:
                        $this->HTML .= ' ';
    	        		$previousRevision =  $this->GetPreviousRevision( $myrow["revision_name"] );
                        # we want something like http://svnweb.freebsd.org/ports/head/www/p5-App-Nopaste/Makefile?r1=300951&r2=300950&pathrev=300951
            			$this->HTML .= ' <A HREF="' . FRESHPORTS_FREEBSD_SVN_URL . '/' . freshports_pathname_to_repo_name($WhichRepo, $myrow["pathname"]) . '?r1=' . 
            			    $myrow["revision_name"] . '&amp;r2=' . $previousRevision . '&amp;pathrev=' . $myrow["revision_name"] . '">';
        		    	$this->HTML .= freshports_Diff_Icon() . '</a> ';
                        break;
                }
            }
            
            # we want something like
            # http://svn.freebsd.org/ports/head/x11-wm/awesome/Makefile
            switch($WhichRepo)
            {
                case FREEBSD_REPO_CVS:
        			$this->HTML .= ' <A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '?annotate=' . $myrow["revision_name"] . '">';
		        	$this->HTML .= freshports_Revision_Icon() . '</a> ';
		        	break;

                case FREEBSD_REPO_SVN:
        			$this->HTML .= ' <A HREF="' . FRESHPORTS_FREEBSD_SVN_URL . '/' . freshports_pathname_to_repo_name($WhichRepo, $myrow["pathname"]) . '?annotate=' . $myrow["revision_name"] . '">';
		        	$this->HTML .= freshports_Revision_Icon() . '</a> ';
                    break;
            }

            $this->HTML .= '</td>';
            
			$this->HTML .= '  <TD WIDTH="100%" VALIGN="middle">';

            switch($WhichRepo)
            {
                case FREEBSD_REPO_CVS:
        			$this->HTML .= '<A HREF="' . FRESHPORTS_FREEBSD_CVS_URL . $myrow["pathname"] . '#rev' . $myrow["revision_name"] . '">';
       			    break;

                case FREEBSD_REPO_SVN:
                    # we want something like http://svnweb.freebsd.org/ports/head/www/p5-App-Nopaste/Makefile?revision=300951&view=markup
        			$this->HTML .= ' <A HREF="' . FRESHPORTS_FREEBSD_SVN_URL . '/' . freshports_pathname_to_repo_name($WhichRepo, $myrow["pathname"]) . '?revision=' . $myrow["revision_name"] . '&amp;&view=markup">';
                    break;
            }

			$this->HTML .= '<CODE CLASS="code">' . $myrow["pathname"] . "</CODE></A></TD>";
			$this->HTML .= "</TR>\n";
		}
		
		$this->HTML .= "</table>";
		
		return $this->HTML;
	}


	function GetPreviousRevision( $revision ) {
	    // if we find a dot, decrement the bit after the last dot
	    // hence, a cvs revision
	    // if no dot, treat it as an svn revision

    	$dotPos = strrpos( $revision, '.' );

    	if ( $dotPos === false ) {
    	    $prev = intval ( $revision ) - 1;
	    } else {
	        $beforeLastDot = substr( $revision, 0, $dotPos + 1 );

	        $afterDot = substr( $revision, $dotPos + 1 );
	        if ( $afterDot === false ) {
	            syslog( LOG_ERR, 'decimal not found in ' . $revision);
	            $prev = $revision;
            } else {
                // previous revision is before dot || (after dot - 1)
	            $prev = $beforeLastDot . ( intval( $afterDot ) - 1 );
            }
        }


        return $prev;
	}

}
?>