?php
	#
	# $Id: search.php,v 1.15 2013-04-08 12:15:52 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/system_branch.php');

	require_once('Pager/Pager.php');

	checkLoadBeforeProceeding();

	$Debug = 0;
# this should only be referenced after it has been set.
#	$sqlUserSpecifiedCondition = '';
#	if ($Debug) phpinfo();

	$https = ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off'));
	if ($https) {
		$protocol = "https";
	} else {
		$protocol = "http";
	}

	if (IsSet($_REQUEST['branch'])) {
		$Branch = NormalizeBranch(htmlspecialchars($_REQUEST['branch']));
	} else {
		$Branch = BRANCH_HEAD;
	}

	freshports_ConditionalGet(freshports_LastModified_Dynamic());

	# results can be sorted by:
	define('ORDERBYPORT',       'port');
	define('ORDERBYCATEGORY',   'category');
	define('ORDERBYLASTUPDATE', 'lastupdate');

	# results are sorted up or down
	define('ORDERBYASCENDING',  'asc');
	define('ORDERBYDESCENDING', 'desc');

	define('INCLUDE_DELETED_PORTS', 'includedeleted');
	define('INCLUDE_SRC_COMMITS',   'include_src_commits');
	define('VEVENSHTEIN_MATCH', 3);

	define('OUTPUT_FORMAT_HTML',        'html');
	define('OUTPUT_FORMAT_PLAIN_TEXT',  'plaintext');
	define('OUTPUT_FORMAT_DEPENDS',     'depends');

	$PageNumber = 1;
	$PageSize   = DEFAULT_NUMBER_OF_COMMITS;

	if (IsSet($_REQUEST['page'])) {
		$PageNumber = intval($_REQUEST['page']);
		if ($PageNumber != $_REQUEST['page']) {
			$PageNumber = 1;
		}
	}

	define('SEARCH_FIELD_COMMITMESSAGE',        'commitmessage');
	define('SEARCH_FIELD_COMMITTER',            'committer');
	define('SEARCH_FIELD_DEPENDS_ALL',          'depends_all');
	define('SEARCH_FIELD_DEPENDS_BUILD',        'depends_build');
	define('SEARCH_FIELD_DEPENDS_LIB',          'depends_lib');
	define('SEARCH_FIELD_DEPENDS_RUN',          'depends_run');
	define('SEARCH_FIELD_LATEST_LINK',          'latest_link');
	define('SEARCH_FIELD_LONGDESCRIPTION',      'longdescription');
	define('SEARCH_FIELD_LICENSE_PERMS',        'license_perms');
	define('SEARCH_FIELD_LICENSE_RESTRICTED',   'license_restricted');
	define('SEARCH_FIELD_MAINTAINER',           'maintainer');
	define('SEARCH_FIELD_MAKEFILE',             'makefile');
	define('SEARCH_FIELD_MESSAGEID',            'message_id');
	define('SEARCH_FIELD_MANUAL_PACKAGE_BUILD', 'manual_pacakge_bulid');
	define('SEARCH_FIELD_NAME',                 'name');
	define('SEARCH_FIELD_PACKAGE',              'package');
	define('SEARCH_FIELD_PATHNAME',             'tree');
	define('SEARCH_FIELD_PKG_MESSAGE',          'pkg-message');
	define('SEARCH_FIELD_PKG_PLIST',            'pkg-plist');
	define('SEARCH_FIELD_SHORTDESCRIPTION',     'shortdescription');
	define('SEARCH_FIELD_USES',                 'uses');


	$SearchTypeToFieldMap = array(
	    SEARCH_FIELD_COMMITMESSAGE        => 'CL.description',
	    SEARCH_FIELD_COMMITTER            => 'CL.committer',
	    SEARCH_FIELD_DEPENDS_ALL          => 'P.depends_all',
	    SEARCH_FIELD_DEPENDS_BUILD        => 'P.depends_build',
	    SEARCH_FIELD_DEPENDS_LIB          => 'P.depends_lib',
	    SEARCH_FIELD_DEPENDS_RUN          => 'P.depends_run',
	    SEARCH_FIELD_LATEST_LINK          => 'P.latest_link',
	    SEARCH_FIELD_LONGDESCRIPTION      => 'P.long_description',
	    SEARCH_FIELD_LICENSE_PERMS        => 'P.license_perms',
	    SEARCH_FIELD_LICENSE_RESTRICTED   => 'P.license_restricted',
	    SEARCH_FIELD_MAINTAINER           => 'P.maintainer',
	    SEARCH_FIELD_MAKEFILE             => 'P.makefile',
	    SEARCH_FIELD_MANUAL_PACKAGE_BUILD => 'P.manual_package_build',
	    SEARCH_FIELD_NAME                 => 'E.name',
	    SEARCH_FIELD_PACKAGE              => 'P.package_name',
	    SEARCH_FIELD_PKG_PLIST            => 'P.pkg_plist',
	    SEARCH_FIELD_PKG_MESSAGE          => 'P.pkgmessage',
	    SEARCH_FIELD_PATHNAME             => 'EP.pathname',
	    SEARCH_FIELD_SHORTDESCRIPTION     => 'P.short_description',
	    SEARCH_FIELD_USES                 => 'P.uses',
	);

	$sqlExtraFields = ''; # will hold extra fields we need, such as watch list
	                      # or soundex function needed for ORDER BY

	function Category_Ports_To_In_Clause($a_db, $a_Ports) {
	  # convert: graphics/acidwarp-sdl devel/gitlab-runner games/ace-of-penguins devel/py-pytest-rerunfailures
	  #      to: IN ('/ports/head/graphics/acidwarp-sdl', '/ports/head/devel/gitlab-runner', '/ports/head/games/ace-of-penguins', '/ports/head/devel/py-pytest-rerunfailures')
	  # for use in searching.
	  #

	  $ports = explode(' ', $a_Ports);

	  $where = 'IN (';
	  foreach($ports as &$port) {
	    $port = '/ports/head/' . pg_escape_string($a_db, $port);
	  }
	  $where .= ')';

	  $where = "IN ('" . implode("', '", $ports) . "')";

	  return $where;
	}


	function WildCardQuery($db, $stype, $Like, $query) {
	  GLOBAL $SearchTypeToFieldMap;

	  # return the clause for this particular type of query
	  $sql = '';

	  switch ($stype) {
	    case SEARCH_FIELD_PATHNAME:
	      $sql .= " $Like '" . pg_escape_string($db, $query) . "'";
	      break;

	    case SEARCH_FIELD_DEPENDS_ALL:
	      $sql .= "\n     (P.depends_build $Like '" . pg_escape_string($db, $query) . "' OR P.depends_lib $Like '" . pg_escape_string($db, $query) . "' OR P.depends_run $Like '" . pg_escape_string($db, $query) . "')";
	      break;

	    default:
	      if (!IsSet($SearchTypeToFieldMap[$stype])) {
	        syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . " unknown stype supplied: '$stype'");
	        $stype = SEARCH_FIELD_NAME;
	      }
	      $sql .= "\n     " .  $SearchTypeToFieldMap[$stype] . " $Like '" . pg_escape_string($db, $query) . "'";
	      break;
	  }

	  return $sql;
	}

	#
	# I became annoyed with people creating their own search pages instead of using
	# mine... If the referrer isn't us, ignore them
	#

	if ($RejectExternalSearches  && $_SERVER["HTTP_REFERER"] != '') {
		$pos = strpos($_SERVER["HTTP_REFERER"], $protocol . '://' . $_SERVER["HTTP_HOST"]);
		if ($pos === FALSE || $pos != 0) {
			echo "Ouch, something really nasty is going on.  Error code: UAFC.  Please contact the webmaster with this message.";
			syslog(LOG_NOTICE, "External search form discovered: $_SERVER[HTTP_REFERER] $_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT]");
			exit;
		}
	}

	$WeHaveToSearch = FALSE;
	$HTML   = '';

	// If these items are missing from the URL, we want them to have a value
	$query				= '';
	$stype				= 'name';
	$num				= $User->page_size;
	$category			= '';
	$port				= '';
	$method				= '';
	$deleted			= 'excludedeleted';
	$include_src_commits= INCLUDE_SRC_COMMITS;
	$casesensitivity		= 'caseinsensitive';
	$orderby			= ORDERBYCATEGORY;
	$orderbyupdown			= ORDERBYASCENDING;
	$output_format			= OUTPUT_FORMAT_HTML;
	$minimal_output			= 0;


	# No special treatment for $query. Whenever it is output: htmlentities(). Whenever it is used in a query: pg_escape_string()
	# re: https://github.com/FreshPorts/freshports/issues/358
	#
	if (IsSet($_REQUEST['query']))              $query               = trim($_REQUEST['query']);

	// avoid nasty problems by escaping them - I'm not even sure this is proper.
	if (IsSet($_REQUEST['stype']))              $stype               = pg_escape_string($db, trim($_REQUEST['stype']));
	if (IsSet($_REQUEST['num']))                $num                 = intval(pg_escape_string($db, trim($_REQUEST['num'])));
	if (IsSet($_REQUEST['category']))           $category            = pg_escape_string($db, trim($_REQUEST['category']));
	if (IsSet($_REQUEST['port']))               $port                = pg_escape_string($db, trim($_REQUEST['port']));
	if (IsSet($_REQUEST['method']))             $method              = pg_escape_string($db, trim($_REQUEST['method']));
	if (IsSet($_REQUEST['deleted']))            $deleted		 = pg_escape_string($db, trim($_REQUEST['deleted']));
	if (!IsSet($_REQUEST[INCLUDE_SRC_COMMITS])) $include_src_commits = '';
	if (IsSet($_REQUEST['casesensitivity']))    $casesensitivity	 = pg_escape_string($db, trim($_REQUEST['casesensitivity']));
	if (IsSet($_REQUEST['orderby']))            $orderby		 = pg_escape_string($db, trim($_REQUEST['orderby']));
	if (IsSet($_REQUEST['orderbyupdown']))      $orderbyupdown       = pg_escape_string($db, trim($_REQUEST['orderbyupdown']));
	if (IsSet($_REQUEST['format']))             $output_format       = pg_escape_string($db, trim($_REQUEST['format']));
	if (IsSet($_REQUEST['minimal']))            $minimal_output      = pg_escape_string($db, trim($_REQUEST['minimal']));

	# we have a problem with people doing this:
	#
	# 83.85.93.90 - - [02/Oct/2007:04:18:00 -0400] "GET /search.php?stype=https://amyru.h18.ru/images/cs.txt? HTTP/1.1" 301 332 "-" "Wget/1.1 (compatible; i486; Linux; RedHat7.3)"
	# well, it's not so much a problem as an annoyance.  So we will redirect their ass eslewhere.
	#

	if (substr($stype, 0, 7) === 'http://') {
	  # redirect their ass
	  header('Location: https://news.freshports.org/2007/10/02/odd-way-to-break-in/');
	  exit;
	}

	if ($stype == SEARCH_FIELD_MESSAGEID) {
		header('Location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . "/commit.php?message_id=" . htmlentities($query));
		exit;
	}

	switch ($stype) {
		case SEARCH_FIELD_COMMITTER:
		case SEARCH_FIELD_COMMITMESSAGE:
		case SEARCH_FIELD_DEPENDS_ALL:
		case SEARCH_FIELD_DEPENDS_BUILD:
		case SEARCH_FIELD_DEPENDS_LIB:
		case SEARCH_FIELD_DEPENDS_RUN:
		case SEARCH_FIELD_LATEST_LINK:
		case SEARCH_FIELD_LONGDESCRIPTION:
		case SEARCH_FIELD_LICENSE_PERMS:
		case SEARCH_FIELD_LICENSE_RESTRICTED:
		case SEARCH_FIELD_MAINTAINER:
		case SEARCH_FIELD_MAKEFILE:
		case SEARCH_FIELD_MANUAL_PACKAGE_BUILD:
		case SEARCH_FIELD_NAME:
		case SEARCH_FIELD_PACKAGE:
		case SEARCH_FIELD_PATHNAME:
		case SEARCH_FIELD_PKG_PLIST:
		case SEARCH_FIELD_PKG_MESSAGE:
		case SEARCH_FIELD_SHORTDESCRIPTION:
		case SEARCH_FIELD_USES:
	          # all is well.  we have a valid value.
	          break;

	        default:
	          # bad value.
	          # ERROR
	          syslog(LOG_ERR, 'bad search string: ' . $stype);
	          $type = SEARCH_FIELD_NAME;
	}

	#
	# ensure deleted has an appropriate value
	#
	switch ($deleted) {
		case INCLUDE_DELETED_PORTS:
			# do nothing
			break;

		default:
			$deleted = 'excludedeleted';
			# do not break here...
	}


	#
	# ensure casesensitivity has an appropriate value
	#
	switch ($casesensitivity) {
		case 'casesensitive':
			# do nothing
			break;

		default:
			$casesensitivity = 'caseinsensitive';
			# do not break here...
	}

	// validate $output_format
	switch ($output_format) {
		case OUTPUT_FORMAT_HTML:
		case OUTPUT_FORMAT_PLAIN_TEXT:
		case OUTPUT_FORMAT_DEPENDS:
			# valid; do nothing
			break;

		default:
			# some strange value
			$output_format = OUTPUT_FORMAT_HTML;
			break;
	}

	if ($output_format == OUTPUT_FORMAT_PLAIN_TEXT || $output_format == OUTPUT_FORMAT_DEPENDS) {
	  # tell the browser to display plain text
	  header('Content-Type: text/plain');
	}

	// start of HTML output
	if ($output_format == OUTPUT_FORMAT_HTML) {
		$Title = 'Search';
		freshports_Start($Title,
					$Title,
					'FreeBSD, index, applications, ports');

		echo freshports_MainTable();
?>
<tr><td class="content">
<?php echo freshports_MainContentTable(); ?>
  <tr>
	<?php echo freshports_PageBannerText("Search FreshPorts using Google"); ?>
  </tr>
<tr><td><div class="gcse-search"></div>
<?php
	} // end of HTML only output


	#
	# ensure that our parameters have default values
	#

	if ($num < 1 or $num > 500) {
		$num = 10;
	}

	$PageSize = $num;

	if ($stype  == '') $stype  = 'name';
	if ($method == '') $method = 'match';

	if ($Debug) {
		echo "query='" . htmlentities($query) . "' && stype='$stype' && num='$num' && method='$method'\n<BR>";

		if ($query && $stype && $num) {
			echo "yes, we have parameters\n<BR>";
		}
	}

	#
	# we can take parameters.  if so, make it look like a post
	#

	if (IsSet($_REQUEST['query'])) {
		$WeHaveToSearch = $_REQUEST['query'];
	}
	if (!IsSet($WeHaveToSearch) && ($query && $stype && $num && $method)) {
		$WeHaveToSearch = TRUE;
	}

	if ($WeHaveToSearch) {

		if ($Debug) echo "into search stuff<BR>\n";

		$logfile = CACHE_DIRECTORY . '/searchlog.txt';

		# Adjust method if required
		if ($method == 'soundex') {
			switch ($stype) {
				case SEARCH_FIELD_COMMITTER:
				case SEARCH_FIELD_MAINTAINER:
				case SEARCH_FIELD_NAME:
				case SEARCH_FIELD_PACKAGE:
					break;

				default:
					$method = 'match';
					if ($output_format == OUTPUT_FORMAT_HTML ) {
						$HTML .= "<p><b>NOTE</b>: Instead of using 'sounds like' as instructed, the system used 'containing'.  See the notes above for why this is done.</p>";
					}
					break;
			}
		}

		# are we setting the whole SQL condition or just the operator and the value?
		$sqlSetAll = false;

		if ($Debug) echo "at line " . __LINE__ . " stype='$stype'<br>";


		if ($output_format == OUTPUT_FORMAT_DEPENDS) {
		  if ($Debug) echo "output_format is OUTPUT_FORMAT_DEPENDS\n";
		  $sqlUserSuppliedPortsList = Category_Ports_To_In_Clause($db, $query);
		} else {
		  switch ($method) {
			case 'prefix':
				$WildCardMatch = "$query%";
				if ($casesensitivity == 'casesensitive') {
					$Like = 'LIKE';
				} else {
					$Like = 'ILIKE';
				}
				$sqlUserSpecifiedCondition = WildCardQuery($db, $stype, $Like, $WildCardMatch);
				break;

			case 'match':
				$WildCardMatch = "%$query%";
				if ($casesensitivity == 'casesensitive') {
					$Like = 'LIKE';
				} else {
					$Like = 'ILIKE';
				}
				if ($Debug) echo 'invoking WildCardQuery for match<br>';
				$sqlUserSpecifiedCondition = WildCardQuery($db, $stype, $Like, $WildCardMatch);
				break;

			case 'suffix':
				$WildCardMatch = "%$query";
				if ($casesensitivity == 'casesensitive') {
					$Like = 'LIKE';
				} else {
					$Like = 'ILIKE';
				}
				$sqlUserSpecifiedCondition = WildCardQuery($db, $stype, $Like, $WildCardMatch);
				break;

			default:
				case 'exact':
					switch ($stype) {
						case SEARCH_FIELD_DEPENDS_ALL:
							if ($casesensitivity == 'casesensitive') {
								$sqlUserSpecifiedCondition = "\n     (P.depends_build = '" . pg_escape_string($db, $query) . "' OR P.depends_lib = '" . pg_escape_string($db, $query) . "' OR P.depends_run = '" . pg_escape_string($db, $query) . "')";
							} else {
								$sqlUserSpecifiedCondition = "\n     (lower(P.depends_build) = lower('" . pg_escape_string($db, $query) . "') OR lower(P.depends_lib) = lower('" . pg_escape_string($db, $query) . "') OR lower(P.depends_run) = lower('" . pg_escape_string($db, $query) . "'))";
							}
							break;

						default:
					                $sqlSetAll = true;
							$FieldName = $SearchTypeToFieldMap[$stype];
							if (empty($FieldName)) {
							   die('you are probably doing this wrong');
							}
							if ($casesensitivity == 'casesensitive') {
								$sqlUserSpecifiedCondition = "     $FieldName = '" . pg_escape_string($db, $query) . "'";
							} else {
								$sqlUserSpecifiedCondition = "     lower($FieldName) = lower('" . pg_escape_string($db, $query) . "')";
							}
							break;
					}
					break;

				case 'soundex':
				    $sqlSetAll = true;
					$FieldName = $SearchTypeToFieldMap[$stype];
					$sqlUserSpecifiedCondition = "\n     levenshtein($FieldName, '" . pg_escape_string($db, $query) . "') < " . VEVENSHTEIN_MATCH;
					$sqlSoundsLikeOrderBy = "levenshtein($FieldName, '" . pg_escape_string($db, $query) . "')";
					break;
		  }

		} # not OUTPUT_FORMAT_DEPENDS

		if ($Debug && IsSet($sqlUserSpecifiedCondition)) echo "at line " . __LINE__ . " sqlUserSpecifiedCondition is: $sqlUserSpecifiedCondition<br>";

		#
		# include/exclude deleted ports
		#

		switch ($stype) {
			case SEARCH_FIELD_COMMITMESSAGE:
			case SEARCH_FIELD_PATHNAME:
				break;

			default:
				switch ($deleted) {
					case INCLUDE_DELETED_PORTS:
						# do nothing
						break;

					default:
						$deleted = 'excludedeleted';
						# do not break here...

					case 'excludedeleted':
						if ($output_format != OUTPUT_FORMAT_DEPENDS) {
							$sqlUserSpecifiedCondition .= " and";
						}
						$sqlUserSpecifiedCondition .= " E.status = 'A' ";
				}
				break;
		}


		#
		# How are we ordering the output?
		# NOTE that searching by 'sounds like' requires a special approach
		#

		switch ($method) {
			case 'soundex':
				$sqlOrderBy = "\n ORDER BY " . $sqlSoundsLikeOrderBy;
				$sqlExtraFields .= ', ' . $sqlSoundsLikeOrderBy;
				break;

			default:
				switch ($orderby) {
					case ORDERBYLASTUPDATE:
						switch ($orderbyupdown) {
							case ORDERBYDESCENDING:
							default:
								$sqlOrderBy = "\n ORDER BY last_commit_date desc, E.name";
								break;

							case ORDERBYASCENDING:
								$sqlOrderBy = "\n ORDER BY last_commit_date, E.name";
								break;
						}
						break;

					case ORDERBYCATEGORY:
						switch ($orderbyupdown) {
							case ORDERBYDESCENDING:
							default:
								$sqlOrderBy = "\n ORDER BY C.name desc, E.name";
								break;

							case ORDERBYASCENDING:
								$sqlOrderBy = "\n ORDER BY C.name, E.name";
								break;
						}
						break;

					case ORDERBYPORT:
					default:
						switch ($orderbyupdown) {
							case ORDERBYDESCENDING:
							default:
								$sqlOrderBy = "\n ORDER BY E.name desc, C.name";
								break;

							case ORDERBYASCENDING:
								$sqlOrderBy = "\n ORDER BY E.name, C.name";
								break;
						}
						break;
				}
		}

		# grab the constant
		$sqlSelectFields = SEARCH_SELECT_FIELD;

		# and this

		$sqlWatchListFrom = '';
			if ($User->id) {
					$sqlWatchListFrom .= "
      LEFT OUTER JOIN
 (SELECT element_id as wle_element_id, COUNT(watch_list_id) as onwatchlist
    FROM watch_list JOIN watch_list_element
        ON watch_list.id      = watch_list_element.watch_list_id
       AND watch_list.user_id = $User->id
       AND watch_list.in_service
  GROUP BY wle_element_id) AS TEMP
       ON TEMP.wle_element_id = E.id";
			}




		switch ($stype) {
		  case SEARCH_FIELD_COMMITTER:
		    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

		    if ($include_src_commits) {
		      if ($Debug) echo 'searching src';
		      require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_committer.php');
		      $Commits = new CommitsByCommitter($db);
		    } else {
		      if ($Debug) echo 'not searching src';
		      require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port_commits_by_committer.php');
		      $Commits = new PortCommitsByCommitter($db);
		    }
		    if ($Debug) echo 'searching by committer for ' . htmlentities($query);
		    $Commits->CommitterSet($query);

		    $Commits->Debug = $Debug;

		    $NumberOfCommits = $Commits->GetCountCommits();
		    if ($Debug) echo 'number of commits = ' . $NumberOfCommits . "<br>\n";

		    $NumFound = $NumberOfCommits;
		    $params = array(
					'mode'        => 'Sliding',
					'perPage'     => $PageSize,
					'delta'       => 5,
					'totalItems'  => $NumFound,
					'urlVar'      => 'page',
					'currentPage' => $PageNumber,
					'spacesBeforeSeparator' => 2,
					'spacesAfterSeparator'  => 2,
		    );
		    # use @ to suppress: Non-static method Pager::factory() should not be called statically
		    $Pager = @Pager::factory($params);

		    $offset = $Pager->getOffsetByPageId();
		    $NumOnThisPage = $offset[1] - $offset[0] + 1;

		    if ($PageNumber > 1) {
		      $Commits->SetOffset($offset[0] - 1);
		    }
		    $Commits->SetLimit($PageSize);

		    $NumFetches = $Commits->Fetch();
		    $result     = $Commits->LocalResult;
		    break;

		  case SEARCH_FIELD_COMMITMESSAGE:
		    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_description.php');
		    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

		    $Commits = new CommitsByDescription($db);
		    $Commits->ConditionSet($sqlUserSpecifiedCondition);
		    $Commits->UserIDSet($User->id);

		    $Commits->Debug = $Debug;

		    $NumberOfCommits = $Commits->GetCountCommits();
		    if ($Debug) echo 'number of commits = ' . $NumberOfCommits . "<br>\n";

			$NumFound = $NumberOfCommits;
			$params = array(
					'mode'        => 'Sliding',
					'perPage'     => $PageSize,
					'delta'       => 5,
					'totalItems'  => $NumFound,
					'urlVar'      => 'page',
					'currentPage' => $PageNumber,
					'spacesBeforeSeparator' => 2,
					'spacesAfterSeparator'  => 2,
				);
			# use @ to suppress: Non-static method Pager::factory() should not be called statically
			$Pager = @Pager::factory($params);

			$offset = $Pager->getOffsetByPageId();
			$NumOnThisPage = $offset[1] - $offset[0] + 1;

		    if ($PageNumber > 1) {
		      $Commits->SetOffset($offset[0] - 1);
		    }
		    $Commits->SetLimit($PageSize);

		    $NumFetches = $Commits->Fetch();
#		    $result     = $Commits->LocalResult;
		    break;

		  case SEARCH_FIELD_PATHNAME:
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_tree_location.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

			if (empty($sqlUserSpecifiedCondition)) {
				exit("Sorry, but you messed something up in your manually created query");
			}
			$Commits = new CommitsByTreeLocation($db);
			$Commits->UserIDSet($User->id);
			if ($sqlSetAll) {
			  if ($Debug) echo 'invoking TreePathConditionSetAll() with ' . $sqlUserSpecifiedCondition;
			  $Commits->TreePathConditionSetAll($sqlUserSpecifiedCondition);
			} else {
			  if ($Debug) echo 'invoking TreePathConditionSet() with ' . $sqlUserSpecifiedCondition;
			  $Commits->TreePathConditionSet($sqlUserSpecifiedCondition);
			}

			$Commits->Debug = $Debug;

			if (substr($query, 0, 7) == '/ports/') {
			  $NumberOfCommits = $Commits->GetCountPortCommits();
			} else {
			  $NumberOfCommits = $Commits->GetCountCommits();
			}
			if ($Debug) echo 'number of commits = ' . $NumberOfCommits . "<br>\n";

			$NumFound = $NumberOfCommits;
			$params = array(
					'mode'        => 'Sliding',
					'perPage'     => $PageSize,
					'delta'       => 5,
					'totalItems'  => $NumFound,
					'urlVar'      => 'page',
					'currentPage' => $PageNumber,
					'spacesBeforeSeparator' => 2,
					'spacesAfterSeparator'  => 2,
				);
			# use @ to suppress: Non-static method Pager::factory() should not be called statically
			$Pager = @Pager::factory($params);

			$offset = $Pager->getOffsetByPageId();
			$NumOnThisPage = $offset[1] - $offset[0] + 1;

			if ($PageNumber > 1) {
			    $Commits->SetOffset($offset[0] - 1);
			}
			$Commits->SetLimit($PageSize);

			if (substr($query, 0, 7) == '/ports/') {
			    $NumFetches = $Commits->FetchPortCommits();
			} else {
			    $NumFetches = $Commits->Fetch();
			}
			$result = $Commits->LocalResult;
		        break;

		  case SEARCH_FIELD_PKG_PLIST:
		  case SEARCH_FIELD_PKG_MESSAGE:
		  case SEARCH_FIELD_USES:
			switch ($stype) {
				case SEARCH_FIELD_PKG_PLIST:
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_by_pkg_plist.php');
					$Ports = new PortsByPkgPlist($db);
					$Ports->setDebug($Debug);
					$Ports->PkgPlistSet($query);
					$Ports->IncludeDeletedPorts($deleted == INCLUDE_DELETED_PORTS);
					break;

				case SEARCH_FIELD_PKG_MESSAGE:
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_by_pkg_message.php');
					$Ports = new PortsByPkgMessage($db);
					$Ports->PkgMessageSet($query);
					$Ports->IncludeDeletedPorts($deleted == INCLUDE_DELETED_PORTS);
					break;

				case SEARCH_FIELD_USES:
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports_by_uses.php');
					$Ports = new PortsByUses($db);
					$Ports->UsesSet($query);
					$Ports->IncludeDeletedPorts($deleted == INCLUDE_DELETED_PORTS);
					break;
			}

			$Ports->Debug = $Debug;

			$NumFound = $Ports->GetQueryCount();
			if ($Debug) {
				echo 'number of ports = ' . $NumFound . "<br>\n";
				echo 'page size = ' . $PageSize . "<br>\n";
				echo 'page size = ' . $PageNumber . "<br>\n";
			}

			$params = array(
					'mode'        => 'Sliding',
					'perPage'     => $PageSize,
					'delta'       => 5,
					'totalItems'  => $NumFound,
					'urlVar'      => 'page',
					'currentPage' => $PageNumber,
					'spacesBeforeSeparator' => 2,
					'spacesAfterSeparator'  => 2,
				);
			# use @ to suppress: Non-static method Pager::factory() should not be called statically
			$Pager = @Pager::factory($params);

			$offset = $Pager->getOffsetByPageId();
			$NumOnThisPage = $offset[1] - $offset[0] + 1;

			if ($PageNumber > 1) {
				$Ports->SetOffset($offset[0] - 1);
			}
			$Ports->SetLimit($PageSize);

			$NumFetches = $Ports->FetchPorts($User->id, $sqlOrderBy);

			# $result get used later on to display the search results via classes/port-display.php
			$result = $Ports->LocalResult;
			break;


		  default:
			$sqlSelectCount = "\n  SELECT count(*)";
			if ($User->id) {
				$sqlExtraFields .= ",\nonwatchlist";
		        } else {
				$sqlExtraFields .= ",\nNULL AS onwatchlist";
		        }

			$sqlFrom = "
  FROM ports P LEFT OUTER JOIN ports_vulnerable    PV  ON PV.port_id       = P.id
               LEFT OUTER JOIN commit_log          CL  ON P.last_commit_id = CL.id
               LEFT OUTER JOIN repo                R   ON CL.repo_id       = R.id
               LEFT OUTER JOIN commit_log_branches CLB ON CL.id            = CLB.commit_log_id
                          JOIN system_branch       SB  ON SB.branch_name   = '" . pg_escape_string($db, $Branch) . "'
                                                      AND SB.id            = CLB.branch_id,
       categories C, element E
";

			if ($output_format == OUTPUT_FORMAT_DEPENDS) {
				$sqlFrom .= "
JOIN element_pathname EP on E.id = EP.element_id
  AND EP.pathname $sqlUserSuppliedPortsList
";
			}

			$sqlWhere = '
    WHERE P.category_id  = C.id
      AND P.element_id   = E.id ' ;


			$AddRemoveExtra  = "?query=" . htmlentities($query). "+stype=$stype+num=$num+method=$method";
			if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";
			$AddRemoveExtra = pg_escape_string($db, $AddRemoveExtra);
			if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";


			### how many rows is this?

			$sql = $sqlSelectCount . $sqlFrom .  $sqlWhere . ' AND ' . $sqlUserSpecifiedCondition;

			if ($Debug) {
				echo "<pre>$sql<pre>\n";
			}

			$result  = pg_exec($db, $sql);
			if (!$result) {
			  syslog(LOG_NOTICE, pg_last_error($db) . ': ' . $sql);
			  die('something went terribly wrong.  Sorry.');
			}

			$NumRows  = pg_num_rows($result);
			$myrow    = pg_fetch_array($result);
			$NumFound = $myrow[0];

			if ($Debug) {
				echo "\$NumFound = '$NumFound'<br>";
			}

			$NumFetches = 0;
			if ($NumFound > 0) {

				$params = array(
						'mode'        => 'Sliding',
						'perPage'     => $PageSize,
						'delta'       => 5,
						'totalItems'  => $NumFound,
						'urlVar'      => 'page',
						'currentPage' => $PageNumber,
						'spacesBeforeSeparator' => 2,
						'spacesAfterSeparator'  => 2,
				);

				# use @ to suppress: Non-static method Pager::factory() should not be called statically
				$Pager = @Pager::factory($params);

				$sqlOffsetLimit = '';

				if ($output_format == OUTPUT_FORMAT_HTML) {
					$offset = $Pager->getOffsetByPageId();
					$NumOnThisPage = $offset[1] - $offset[0] + 1;
					if ($PageNumber > 1) {
						$sqlOffsetLimit .= "\nOFFSET " . ($offset[0] - 1);
						unset($offset);
					}

					if ($PageSize) {
						$sqlOffsetLimit .= "\nLIMIT " . $PageSize;
					}

				} // HTML format

				$sql = $sqlSelectFields . $sqlExtraFields . $sqlFrom . $sqlWatchListFrom .
				        $sqlWhere . ' AND ' . $sqlUserSpecifiedCondition . $sqlOrderBy . $sqlOffsetLimit;

				if ($Debug) {
					echo "<pre>$sql<pre>\n";
				}

				$result  = pg_exec($db, $sql);
				if (!$result) {
					syslog(LOG_NOTICE, pg_last_error($db) . ': ' . $sql);
					die('something went terribly wrong.  Sorry.');
				}

				$NumFetches = pg_num_rows($result);
			} # $NumFound > 0

		} // end of non-committer search  ## I think this is the end of the default option

		$fp = fopen($logfile, "a");
		if ($fp) {
			switch ($method) {
				case 'match':
				case 'tree':
				case 'exact':
				case 'soundex':
					fwrite($fp, date("Y-m-d H:i:s") . " $stype : $method : $query : $num : $NumFetches : $deleted : $casesensitivity\n");
					break;

				default:
					fwrite($fp, date("Y-m-d H:i:s") . " $stype : $method : $category/$port : $num : $NumFetches : $deleted\n");
			}
			fclose($fp);
		} else {
			print "Please let postmaster@freshports.org know that the search log could not be opened.  This does not affect the search results.\n";
			define_syslog_variables();
			syslog(LOG_ERR, "FreshPorts could not open the search log file: $logfile");
		}


		$Port = new Port($db);
		$Port->LocalResult = $result;

	}

	if ($output_format == OUTPUT_FORMAT_HTML) {
?>
<!-- SiteSearch Google -->
<script>
  (function() {
    var cx = '015787766717316021231:u1yjof0lhkk';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
        '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>
<!-- SiteSearch Google -->

</table>

<br>

<?php echo freshports_MainContentTable(); ?>
  <tr>
	<?php echo freshports_PageBannerText("The FreshPorts Search"); ?>
  </tr>
<tr><td>


<form ACTION="<?php echo $_SERVER["PHP_SELF"] ?>" name="search" >
	<SELECT NAME="stype" size="1">
		<OPTION VALUE="<?php echo SEARCH_FIELD_COMMITMESSAGE        . '"'; if ($stype == SEARCH_FIELD_COMMITMESSAGE)        echo ' SELECTED'; ?>>Commit Message</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_COMMITTER            . '"'; if ($stype == SEARCH_FIELD_COMMITTER)            echo ' SELECTED'; ?>>Committer</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_BUILD        . '"'; if ($stype == SEARCH_FIELD_DEPENDS_BUILD)        echo ' SELECTED'; ?>>Depends Build</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_LIB          . '"'; if ($stype == SEARCH_FIELD_DEPENDS_LIB)          echo ' SELECTED'; ?>>Depends Lib</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_RUN          . '"'; if ($stype == SEARCH_FIELD_DEPENDS_RUN)          echo ' SELECTED'; ?>>Depends Run</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_ALL          . '"'; if ($stype == SEARCH_FIELD_DEPENDS_ALL)          echo ' SELECTED'; ?>>Depends Build/Lib/Run</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LATEST_LINK          . '"'; if ($stype == SEARCH_FIELD_LATEST_LINK)          echo ' SELECTED'; ?>>Latest Link</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LONGDESCRIPTION      . '"'; if ($stype == SEARCH_FIELD_LONGDESCRIPTION)      echo ' SELECTED'; ?>>Long Description</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LICENSE_PERMS        . '"'; if ($stype == SEARCH_FIELD_LICENSE_PERMS)        echo ' SELECTED'; ?>>LICENSE_PERMS</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LICENSE_RESTRICTED   . '"'; if ($stype == SEARCH_FIELD_LICENSE_RESTRICTED)   echo ' SELECTED'; ?>>_LICENSE_RESTRICTED</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MAKEFILE             . '"'; if ($stype == SEARCH_FIELD_MAKEFILE)             echo ' SELECTED'; ?>>Makefile (ports only)</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MAINTAINER           . '"'; if ($stype == SEARCH_FIELD_MAINTAINER)           echo ' SELECTED'; ?>>Maintainer</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MANUAL_PACKAGE_BUILD . '"'; if ($stype == SEARCH_FIELD_MANUAL_PACKAGE_BUILD) echo ' SELECTED'; ?>>MANUAL_PACKAGE_BUILD</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MESSAGEID            . '"'; if ($stype == SEARCH_FIELD_MESSAGEID)            echo ' SELECTED'; ?>>Message ID</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PACKAGE              . '"'; if ($stype == SEARCH_FIELD_PACKAGE)              echo ' SELECTED'; ?>>Package Name</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PKG_PLIST            . '"'; if ($stype == SEARCH_FIELD_PKG_PLIST)            echo ' SELECTED'; ?>>pkg-plist</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PKG_MESSAGE          . '"'; if ($stype == SEARCH_FIELD_PKG_MESSAGE)          echo ' SELECTED'; ?>>pkg-message</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_NAME                 . '"'; if ($stype == SEARCH_FIELD_NAME)                 echo ' SELECTED'; ?>>Port Name</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_SHORTDESCRIPTION     . '"'; if ($stype == SEARCH_FIELD_SHORTDESCRIPTION)     echo ' SELECTED'; ?>>Short Description</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PATHNAME             . '"'; if ($stype == SEARCH_FIELD_PATHNAME)             echo ' SELECTED'; ?>>Under a pathname</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_USES                 . '"'; if ($stype == SEARCH_FIELD_USES)                 echo ' SELECTED'; ?>>USES</OPTION>
	</SELECT>

	<SELECT name=method>
		<OPTION VALUE="exact"   <?php if ($method == "exact"  ) echo 'SELECTED' ?>>equal to
		<OPTION VALUE="prefix"  <?php if ($method == "prefix" ) echo 'SELECTED' ?>>starting with
		<OPTION VALUE="match"   <?php if ($method == "match"  ) echo 'SELECTED' ?>>containing
		<OPTION VALUE="suffix"  <?php if ($method == "suffix" ) echo 'SELECTED' ?>>ending with
		<OPTION VALUE="soundex" <?php if ($method == "soundex") echo 'SELECTED' ?>>sounds like
	</SELECT>

	<INPUT NAME="query" size="40"  VALUE="<?php echo htmlentities($query)?>">

<?php
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/page_options.php');
		$PageOptions = new ItemsPerPage();
		echo $PageOptions->DDLB_Choices('num', $num, 'results');
?>

	<BR><br>

<table class="search-options bordered">
<tr>
<td>
	<INPUT TYPE=checkbox <?php if ($deleted == INCLUDE_DELETED_PORTS) echo 'CHECKED'; ?> VALUE=<?php echo INCLUDE_DELETED_PORTS; ?> NAME=deleted> Include deleted ports
</td>
<td>
	<INPUT TYPE=checkbox <?php if ($casesensitivity == "casesensitive")   echo 'CHECKED'; ?> VALUE=casesensitive   NAME=casesensitivity> Case sensitive search
</td>
<td>
	Sort by: <SELECT name="orderby">
		<OPTION VALUE="<?php echo ORDERBYPORT;       ?>" <?php if ($orderby == ORDERBYPORT       ) echo 'SELECTED' ?>>Port
		<OPTION VALUE="<?php echo ORDERBYCATEGORY;   ?>" <?php if ($orderby == ORDERBYCATEGORY   ) echo 'SELECTED' ?>>Category
		<OPTION VALUE="<?php echo ORDERBYLASTUPDATE; ?>" <?php if ($orderby == ORDERBYLASTUPDATE ) echo 'SELECTED' ?>>Last Update
	</SELECT>

	<SELECT name="orderbyupdown">
		<OPTION VALUE="<?php echo ORDERBYASCENDING;  ?>" <?php if ($orderbyupdown == ORDERBYASCENDING  ) echo 'SELECTED' ?>>ascending
		<OPTION VALUE="<?php echo ORDERBYDESCENDING; ?>" <?php if ($orderbyupdown == ORDERBYDESCENDING ) echo 'SELECTED' ?>>descending
	</SELECT>
</td>
<td>
	<INPUT TYPE="submit" VALUE="Search" NAME="search">
</td>
</tr><tr>
<td colspan="4">
	<INPUT TYPE=checkbox <?php if ($include_src_commits == INCLUDE_SRC_COMMITS) echo 'CHECKED'; ?> VALUE=<?php echo INCLUDE_SRC_COMMITS; ?> NAME=<?php echo INCLUDE_SRC_COMMITS; ?>> Include /src tree
</td>
</tr>
<tr><td colspan="2">
  <b>Output format</b>:<br>
  <input type="radio" name="format" value="<?php echo OUTPUT_FORMAT_HTML       . '"'; if ($output_format == OUTPUT_FORMAT_HTML)       echo ' checked'; ?>> HTML<br>
  <input type="radio" name="format" value="<?php echo OUTPUT_FORMAT_PLAIN_TEXT . '"'; if ($output_format == OUTPUT_FORMAT_PLAIN_TEXT) echo ' checked'; ?>> Plain Text<br>
  <input type="radio" name="format" value="<?php echo OUTPUT_FORMAT_DEPENDS    . '"'; if ($output_format == OUTPUT_FORMAT_DEPENDS)    echo ' checked'; ?>> Depends<br>
</td>
<td>
<INPUT TYPE=checkbox VALUE=1   NAME=effort> Maximum Effort
</td>
<td>
<INPUT TYPE=checkbox <?php if ($minimal_output == "1")   echo 'CHECKED'; ?> VALUE=1   NAME=minimal> Minimal output
</td>
</tr>
<tr>
  <td colspan="4">
    <b>Branch</b>:<br>
      <SELECT NAME="branch" size="1">
        <OPTION VALUE="<?php
        	echo BRANCH_HEAD . '"';
        	if ($Branch == BRANCH_HEAD) echo ' SELECTED'; echo '>' . BRANCH_HEAD;
        	echo '</OPTION>';

        	$system_branch = new SystemBranch($db);
        	$branches = $system_branch->getBranchNames();
        	foreach($branches as $branch_name) {
        		echo '<OPTION VALUE="' . $branch_name . '"';
			if ($Branch == $branch_name) echo ' SELECTED';
			echo '>' . $branch_name . '</OPTION>';
		}
          ?>
      </SELECT>
  </td>
</tr>

</table>
</form>

<h3>Notes</h3>
<ul>
<li><small>Case sensitivity is ignored for "sounds like" and output is ordered by the soundex.</small></li>
<li><small>When searching on 'Message ID', the type of match is ignored.</small></li>
<li><small>When searching on 'Commit Message' only 'containing' is used.</small></li>
<li><small>When searching  by 'Under a pathname', your path must start with something like /ports/, /doc/, or /src/. All
      commits under that point will be returned. The selected match type is ignored and defaults to 'Starts with'.</small></li>
<li><small>Searching for 'sounds like' is only valid for Committer, Maintainer, Package Name, and Port Name.</small></li>
</ul>

<?php

		if ($User->id != '') {
?>
<p>
Special searches:
</p>
<ul>
<li>	<FORM ACTION="/search.php" NAME="f">
	<INPUT NAME="query"           TYPE="hidden" value="<?php GLOBAL $User; echo $User->email; ?>">
	<INPUT NAME="num"             TYPE="hidden" value="10">
	<INPUT NAME="stype"           TYPE="hidden" value="maintainer">
	<INPUT NAME="method"          TYPE="hidden" value="exact">
	<INPUT NAME="deleted"         TYPE="hidden" value="excludedeleted">
	<INPUT NAME="start"           TYPE="hidden" value="1">
  	<INPUT NAME="casesensitivity" TYPE="hidden" value="caseinsensitive">
        <INPUT TYPE="submit" VALUE="Ports I Maintain" NAME="search">
	</FORM>

</ul>
<?php
		} // end User->id

		if ($WeHaveToSearch) {
			echo "<tr><td>\n";
		}

	}  // end of putting out HTML output

	if ($Debug) echo 'in debug mode';

	if ($Debug && $WeHaveToSearch) echo ' we have to search';
	
	if ($WeHaveToSearch) {
		if (IsSet($NumFetches) && $NumFetches == 0) {
		   if ($Debug) echo 'nothing found';
		   if ($output_format == OUTPUT_FORMAT_HTML) {
		     $HTML .= " <strong style=\"color:var(--beastie-red)\">No results found</strong><br>\n";
		   }
		} else {
		      if ($stype == 'committer' || $stype == 'commitmessage' || $stype == 'tree') {
		          $NumFetches = min($num, $NumberOfCommits);
		          if ($Debug) echo 'here we are';
		          if ($NumFetches != $NumberOfCommits) {
		            $MoreToShow = 1;
		          } else {
		             $MoreToShow = 0;
		          }

		          $NumPortsFound = 'Number of commits: ' . $NumberOfCommits;
		          if ($NumFound > $PageSize) {
		            $NumPortsFound .= " (showing only $NumOnThisPage on this page)";
			      }
			  
			      if ($Debug) echo "NumPortsFound = '$NumPortsFound'<br>";
		      } else {
		        if (IsSet($NumFetches) && IsSet($NumRows) && $NumFetches != $NumRows) {
		           $MoreToShow = 1;
		        } else {
		           $MoreToShow = 0;
		        }

		        $NumPortsFound = 'Number of ports: ' . ($NumFound ?? 0);
		        if ($NumFound > $PageSize && $output_format !== OUTPUT_FORMAT_PLAIN_TEXT) {
		          $NumPortsFound .= " (showing only $NumOnThisPage on this page)";
		        }
		      }

			if ($Debug) echo 'here we are2';
			switch ($stype) {
				case SEARCH_FIELD_COMMITTER:
				case SEARCH_FIELD_COMMITMESSAGE:
				case SEARCH_FIELD_PATHNAME:
					if ($Debug) echo 'time to display!';
					$DisplayCommit = new DisplayCommit($db, $Commits->LocalResult);
					$links = $Pager->GetLinks();

					$HTML .= $NumPortsFound . ' ' . $links['all'];
					$HTML .= $DisplayCommit->CreateHTML();
					$HTML .= '<tr><td>' . $NumPortsFound . ' ' . $links['all'] . '</td></tr>';
					break;

				default:
					require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');

					$links = $Pager->GetLinks();

					if ($output_format == OUTPUT_FORMAT_HTML) {
						$HTML .= $NumPortsFound . ' ' . $links['all'];
					}

					GLOBAL $User;

					$port_display = new port_display($db, $User);

					switch ($minimal_output) {
						case 1:
							$port_display->SetDetailsNil();
							$port_display->SetDetailsMinimal();
							break;
						default:
							$port_display->SetDetailsSearch();
							if ($stype == SEARCH_FIELD_PKG_MESSAGE) {
								if ($Debug) echo 'SEARCH_FIELD_PKG_MESSAGE is in effect';
								$port_display->SetDetailsPkgMessage();
							}
							break;
					}

					if ($Debug) echo 'NumFetches = ' . $NumFetches;
					for ($i = 0; $i < $NumFetches; $i++) {
						$Port->FetchNth($i);
						$port_display->SetPort($Port);
						switch ($output_format) {
							case OUTPUT_FORMAT_HTML:
								$Port_HTML = $port_display->Display();
								$HTML .= $port_display->ReplaceWatchListToken($Port->{'onwatchlist'}, $Port_HTML, $Port->{'element_id'});
								break;

							case OUTPUT_FORMAT_PLAIN_TEXT:
								$HTML .= $port_display->DisplayPlainText() . "\n";
								break;

							case OUTPUT_FORMAT_DEPENDS:
								$HTML .= $port_display->DisplayDependencyLine() . "\n";
								$tmp   = $port_display->DisplayDependencyLineLibraries(true);
								if (!empty($tmp)) {
									$HTML .= $tmp . "\n";
								}
								break;
						} // switch
						if ($output_format == OUTPUT_FORMAT_HTML) {
							$HTML .= '<hr width="100%">';
						}
					} // for

				    	if ($output_format == OUTPUT_FORMAT_HTML) {
						$HTML .= $NumPortsFound . ' ' . $links['all'];
					}
#			}
			
		      }
		
		      if ($Debug) echo 'WHAT IS THIS?';
		      echo $HTML;

		} /* NumFetches  != 0 */

		if ($output_format == OUTPUT_FORMAT_HTML) {
			echo $HTML;

?>
</table>

</td>

  <td class="sidebar">
  <?php
			echo freshports_SideBar();
  ?>
  </td>

</tr>
</table>
<?php
echo freshports_ShowFooter();

?>
<?php
			if (!IsSet($_REQUEST['query'])) { ?>
<script>
<!--
document.search.query.focus();
// -->
</script>
<?php
			} /* query is provided */

		} /* OUTPUT_FORMAT_HTML */

		if ($output_format !== OUTPUT_FORMAT_PLAIN_TEXT) {
?>
</body>
</html>

<?php
		}

	} // $WeHaveToSearch
