<?php
	#
	# $Id: files-display.php,v 1.7 2012-09-25 18:10:12 dan Exp $
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

	function __construct($ResultSet) {
		$this->ResultSet = $ResultSet;
		$this->HTML       = '';
	}

	function CreateHTML($WhichRepo) {
		GLOBAL $freshports_CommitMsgMaxNumOfLinesToShow;
		GLOBAL $DaysMarkedAsNew;

		$this->HTML = '';

		if (!$this->ResultSet) {
			die("read from database failed");
			exit;
		}

		$NumRows = pg_num_rows($this->ResultSet);
		if ($this->Debug) echo __FILE__ . ':' . __LINE__ . " Number of rows = $NumRows<br>\n";
		if (!$NumRows) { 
			$this->HTML = "<tr><td>\n<P>Sorry, nothing found in the database....</P>\n</td></tr>\n";
			return $this->HTML;
		}

		$this->HTML .= '
<table class="files-list fullwidth bordered">
<tr>
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

		$this->HTML .= freshports_PageBannerTextColSpan($title, 4);
		
		$this->HTML .= "</tr>\n";

		$this->HTML .= "
		<tr>
			<th>Action</th><th>Revision</th><th>Annotate/etc</th><th>File</th>
		</tr>\n";

		for ($i = 0; $i < $NumRows; $i++) {
			$myrow = pg_fetch_array($this->ResultSet, $i);

			if ($this->Debug) {
				echo '<pre>';
				var_dump($myrow);
				echo '</pre>';
			}

			$this->HTML .= "<tr>\n";

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

			$this->HTML .= "  <td>" . $Change_Type . "</td>";
			$this->HTML .= '  <td>' . $myrow["revision_name"];
            $this->HTML .= "</td>";
            
            $this->HTML .= '<td>';
            switch($myrow['repository'])
            {
		default:
                    $this->HTML .= freshports_cvsweb_Annotate_Link($myrow["pathname"] , $myrow["revision_name"]); 
                    break;

                case FREEBSD_REPOSITORY_SUBVERSION:
                    # we want something like
                    # http://svn.freebsd.org/ports/head/x11-wm/awesome/Makefile
                    $this->HTML .= ' <a href="http://' . $myrow['repo_hostname'] . $myrow["pathname"] . '?annotate=' . $myrow["revision_name"] . '">';
		    $this->HTML .= freshports_Revision_Icon() . '</a> ';
                    break;

                case FREEBSD_REPOSITORY_GIT:
                    # we want something like
                    # was: https://github.com/freebsd/freebsd-ports/blame/0957c7db9bf1fc4313cdefdcdc2608a0c965dda7/sysutils/goaccess/Makefile
                    # now: https://cgit.freebsd.org/ports/blame/multimedia/plexmediaserver-plexpass/Makefile
                    
                    # branch: https://cgit.freebsd.org/ports/blame/www/gitea/Makefile?h=2021Q2&id=3ce47d16f7eb5c00b470603c307fa52bb9ca920b
                    $this->HTML .= ' <a href="http://' . $myrow['repo_hostname'] . $myrow["path_to_repo"] . '/blame/';
                    $this->HTML .= freshports_Convert_Subversion_Path_To_Git($myrow["pathname"], $myrow['branch']) . '?';
                    if ($myrow['branch'] != BRANCH_HEAD ) {
                        $this->HTML .= 'h=' . $myrow['branch'] . '&';
                    }
                    $this->HTML .= 'id=' . $myrow["revision_name"];
                    $this->HTML .= '">';
                    
                    # getting: https://cgit.freebsd.org/ports/blame/ /ports/branches/2021Q2/www/gitea/Makefile?id=5ceea227c504d2892d91c1aa8d8d81ff15b22fc3&h=
                    #          http://cgit.freebsd.org/ports/blame// ports/branches/2021Q2/www/gitea/Makefile?id=5ceea227c504d2892d91c1aa8d8d81ff15b22fc3&h=
                    $this->HTML .= freshports_Annotate_Icon() . '</a> ';
                    break;
            }


            if ( $Change_Type == "modify" ) {
                switch($myrow['repository'])
                {
                    default:
                        $this->HTML .= ' ';
                        $previousRevision =  $this->GetPreviousRevision( $myrow["revision_name"] );
                        $this->HTML .= freshports_cvsweb_Diff_Link($myrow["pathname"] , $previousRevision, $myrow["revision_name"]);
                        break;

                    case FREEBSD_REPOSITORY_SUBVERSION:
                        $this->HTML .= ' ';
    	        	$previousRevision = $this->GetPreviousRevision( $myrow["revision_name"] );
                        # we want something like http://svnweb.freebsd.org/ports/head/www/p5-App-Nopaste/Makefile?r1=300951&r2=300950&pathrev=300951
                        $this->HTML .= ' <a href="http://' . $myrow['repo_hostname'] . $myrow["pathname"] . '?r1=' . 
            		$myrow["revision_name"] . '&amp;r2=' . $previousRevision . '&amp;pathrev=' . $myrow["revision_name"] . '">';
        		$this->HTML .= freshports_Diff_Icon() . '</a> ';
                        break;

                    case FREEBSD_REPOSITORY_GIT:
                        $this->HTML .= ' ';
			$this->HTML .= freshports_git_commit_Link_diff($myrow['message_id'], $myrow['repo_hostname'], $myrow['path_to_repo']);
                        break;
                }
            }

            $this->HTML .= '</td>';
            $this->HTML .= '  <td>';
            
            switch($myrow['repository'])
            {
                default:
                    $this->HTML .= freshports_cvsweb_Revision_Link($myrow["pathname"] , $myrow["revision_name"]);
                    $url_text = $myrow["pathname"];
                    break;

                case FREEBSD_REPOSITORY_SUBVERSION:
                    # we want something like
                    # http://svnweb.freebsd.org/ports/head/textproc/bsddiff/Makefile?view=log#rev300953
                    $this->HTML .= ' <a href="http://' . $myrow['repo_hostname'] . $myrow["pathname"] . '?view=log#rev' . $myrow["revision_name"] . '">';
                    $url_text = $myrow["pathname"];
                    break;

                case FREEBSD_REPOSITORY_GIT:
                    # we want something like
                    # https://github.com/freebsd/freebsd-ports/commit/0957c7db9bf1fc4313cdefdcdc2608a0c965dda7
                    # https://github.com/freebsd/freebsd-ports/commits/0957c7db9bf1fc4313cdefdcdc2608a0c965dda7/sysutils/goaccess/Makefile
                    # was: https://github.com/freebsd/freebsd-ports/commits/0957c7db9bf1fc4313cdefdcdc2608a0c965dda7sysutils/goaccess/Makefile
                    # now: https://cgit.freebsd.org/ports/log/multimedia/plexmediaserver-plexpass/Makefile
                    $url_text = freshports_Convert_Subversion_Path_To_Git($myrow["pathname"], $myrow['branch']);
                    $this->HTML .= ' <a href="http://' . $myrow['repo_hostname'] . $myrow["path_to_repo"] . '/log/' . $url_text . '" title="Commit history">';
                    break;
            }

            # we once had $myrow["pathname"] here, until git came in, and we needed to convert the pathname.
            $this->HTML .= '<code class="code">' . $url_text . "</code></a></td>";
            $this->HTML .= "</tr>\n";
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
