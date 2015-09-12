<?php
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

	require_once('Pager/Pager.php');

	$Debug = 0;
#	if ($Debug) phpinfo();

    $https = ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off'));
    if ($https) {
      $protocol = "https";
    } else {
      $protocol = "http";
    }

	freshports_ConditionalGet(freshports_LastModified_Dynamic());

	define('ORDERBYPORT',       'port');
	define('ORDERBYCATEGORY',   'category');
	define('ORDERBYASCENDING',  'asc');
	define('ORDERBYDESCENDING', 'desc');

	define('INCLUDE_DELETED_PORTS', 'includedeleted');
	define('INCLUDE_SRC_COMMITS',   'include_src_commits');
	define('VEVENSHTEIN_MATCH', 3);

	$PageNumber = 1;
	$PageSize   = 100;

	if (IsSet($_REQUEST['page'])) {
		$PageNumber = intval($_REQUEST['page']);
		if ($PageNumber != $_REQUEST['page']) {
			$PageNumber = 1;
		}
	}

define('SEARCH_FIELD_NAME',             'name');
define('SEARCH_FIELD_PACKAGE',          'package');
define('SEARCH_FIELD_LATEST_LINK',      'latest_link');
define('SEARCH_FIELD_SHORTDESCRIPTION', 'shortdescription');
define('SEARCH_FIELD_LONGDESCRIPTION',  'longdescription');
define('SEARCH_FIELD_DEPENDS_BUILD',    'depends_build');
define('SEARCH_FIELD_DEPENDS_LIB',      'depends_lib');
define('SEARCH_FIELD_DEPENDS_RUN',      'depends_run');
define('SEARCH_FIELD_DEPENDS_ALL',      'depends_all');
define('SEARCH_FIELD_MAINTAINER',       'maintainer');
define('SEARCH_FIELD_COMMITTER',        'committer');

define('SEARCH_FIELD_PATHNAME',         'tree');
define('SEARCH_FIELD_MESSAGEID',        'message_id');
define('SEARCH_FIELD_COMMITMESSAGE',    'commitmessage');
	
$SearchTypeToFieldMap = array(
	SEARCH_FIELD_NAME 		=> 'E.name',
	SEARCH_FIELD_PACKAGE		=> 'P.package_name',
	SEARCH_FIELD_LATEST_LINK	=> 'P.latest_link',
	SEARCH_FIELD_SHORTDESCRIPTION	=> 'P.short_description',
	SEARCH_FIELD_LONGDESCRIPTION	=> 'P.long_description',
	SEARCH_FIELD_DEPENDS_BUILD	=> 'P.depends_build',
	SEARCH_FIELD_DEPENDS_LIB	=> 'P.depends_lib',
	SEARCH_FIELD_DEPENDS_RUN	=> 'P.depends_run',
	SEARCH_FIELD_DEPENDS_ALL	=> 'P.depends_all',
	SEARCH_FIELD_MAINTAINER		=> 'P.maintainer',
	SEARCH_FIELD_COMMITMESSAGE	=> 'CL.description',
	SEARCH_FIELD_COMMITTER		=> 'CL.committer',
	SEARCH_FIELD_PATHNAME           => 'EP.pathname'
);

$sqlExtraFields = ''; # will hold extra fields we need, such as watch list
                      # or soundex function needed for ORDER BY

function WildCardQuery($stype, $Like, $query) {
  GLOBAL $SearchTypeToFieldMap;
  # return the clause for this particular type of query
  $sql = '';

  switch ($stype) {
    case SEARCH_FIELD_PATHNAME:
      $sql .= " $Like '" . pg_escape_string($query) . "'";
      break;

    case SEARCH_FIELD_DEPENDS_ALL:
      $sql .= "\n     (P.depends_build $Like '" . pg_escape_string($query) . "' OR P.depends_lib $Like '" . pg_escape_string($query) . "' OR P.depends_run $Like '" . pg_escape_string($query) . "')";
      break;

    default:
      if (!IsSet($SearchTypeToFieldMap[$stype])) {
        syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . " unknown stype supplied: '$stype'");
        $stype = SEARCH_FIELD_NAME;
      }
      $sql .= "\n     " .  $SearchTypeToFieldMap[$stype] . " $Like '" . pg_escape_string($query) . "'";
      break;
	}

	return $sql;
}

	#
	# I became annoyed with people creating their own search pages instead of using
	# mine... If the referrer isn't us, ignore them
	#

	if ($RejectExternalSearches  && $_SERVER["HTTP_REFERER"] != '') {
		$pos = strpos($_SERVER["HTTP_REFERER"], $protocol . '://' . $_SERVER["SERVER_NAME"]);
		if ($pos === FALSE || $pos != 0) {
			echo "Ouch, something really nasty is going on.  Error code: UAFC.  Please contact the webmaster with this message.";
			syslog(LOG_NOTICE, "External search form discovered: $_SERVER[HTTP_REFERER] $_SERVER[REMOTE_ADDR]:$_SERVER[REMOTE_PORT]");
			exit;
		}
	}

	$search = FALSE;
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
	$casesensitivity	= 'caseinsensitive';
	$orderby            = ORDERBYCATEGORY;
	$orderbyupdown		= ORDERBYASCENDING;

	// avoid nasty problems by adding slashes
	if (IsSet($_REQUEST['query']))           $query				= pg_escape_string(trim($_REQUEST['query']));
	if (IsSet($_REQUEST['stype']))           $stype				= pg_escape_string(trim($_REQUEST['stype']));
	if (IsSet($_REQUEST['num']))             $num   		    = intval(pg_escape_string(trim($_REQUEST['num'])));
	if (IsSet($_REQUEST['category']))        $category			= pg_escape_string(trim($_REQUEST['category']));
	if (IsSet($_REQUEST['port']))            $port				= pg_escape_string(trim($_REQUEST['port']));
	if (IsSet($_REQUEST['method']))          $method			= pg_escape_string(trim($_REQUEST['method']));
	if (IsSet($_REQUEST['deleted']))         $deleted			= pg_escape_string(trim($_REQUEST['deleted']));
	if (!IsSet($_REQUEST[INCLUDE_SRC_COMMITS])) $include_src_commits	= '';
	if (IsSet($_REQUEST['casesensitivity'])) $casesensitivity	= pg_escape_string(trim($_REQUEST['casesensitivity']));
	if (IsSet($_REQUEST['orderby']))         $orderby			= pg_escape_string(trim($_REQUEST['orderby']));
	if (IsSet($_REQUEST['orderbyupdown']))   $orderbyupdown		= pg_escape_string(trim($_REQUEST['orderbyupdown']));
	
	# we have a problem with people doing this:
	#
	# 83.85.93.90 - - [02/Oct/2007:04:18:00 -0400] "GET /search.php?stype=http://amyru.h18.ru/images/cs.txt? HTTP/1.1" 301 332 "-" "Wget/1.1 (compatible; i486; Linux; RedHat7.3)"
	# well, it's not so much a problem as an annoyance.  So we will redirect their ass eslewhere.
	#
	
	if (substr($stype, 0, 7) === 'http://') {
	  # redirect their ass
	  header('Location: http://news.freshports.org/2007/10/02/odd-way-to-break-in/');
	  exit;
	}

	if ($stype == SEARCH_FIELD_MESSAGEID) {
		header('Location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . "/commit.php?message_id=$query");
		exit;
	}

	switch ($stype) {
		case SEARCH_FIELD_NAME:
		case SEARCH_FIELD_PACKAGE:
		case SEARCH_FIELD_LATEST_LINK:
		case SEARCH_FIELD_SHORTDESCRIPTION:
		case SEARCH_FIELD_LONGDESCRIPTION:
		case SEARCH_FIELD_DEPENDS_BUILD:
		case SEARCH_FIELD_DEPENDS_LIB:
		case SEARCH_FIELD_DEPENDS_RUN:
		case SEARCH_FIELD_DEPENDS_ALL:
		case SEARCH_FIELD_MAINTAINER:
		case SEARCH_FIELD_COMMITTER:
		case SEARCH_FIELD_PATHNAME:
		case SEARCH_FIELD_COMMITMESSAGE:
          # all is well.  we have a valid value.
          break;

        default:
          # bad value.
          # ERROR
          syslog(LOG_ERR, 'bad search string: ' . $_SERVER['QUERY_STRING']);
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


#	if ($Debug) phpinfo();

if (!IsSet($_REQUEST['query'])) {
	$OnLoad = 'setfocus()';
}

	freshports_Start('Search',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>

<script language="JavaScript" type="text/javascript">
<!--
function setfocus() { document.search.query.focus(); }
// -->
</script>

<?php echo freshports_MainTable(); ?>
<tr><td valign="top" width="100%">
<?php echo freshports_MainContentTable(); ?>
  <tr>
	<? echo freshports_PageBannerText("Search FreshPorts using Google"); ?>
  </tr>
<tr><td valign="top">
<?

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
	echo "'$query' && '$stype' && '$num' && '$method'\n<BR>";

	if ($query && $stype && $num) {
		echo "yes, we have parameters\n<BR>";
	}
}

#
# we can take parameters.  if so, make it look like a post
#

if (IsSet($_REQUEST['query'])) {
	$search = $_REQUEST['query'];
}
if (!IsSet($search) && ($query && $stype && $num && $method)) {
	$search = TRUE;
}

if ($search) {

	if ($Debug) echo "into search stuff<BR>\n";

$logfile = $_SERVER["DOCUMENT_ROOT"] . "/../dynamic/searchlog.txt";

# Adjust method if required
if ($method == 'soundex') {
	switch ($stype) {
		case SEARCH_FIELD_NAME:
		case SEARCH_FIELD_PACKAGE:
		case SEARCH_FIELD_LATEST_LINK:
		case SEARCH_FIELD_MAINTAINER:
		case SEARCH_FIELD_PATHNAME:
			break;

		default:
			$method = 'match';
			echo "NOTE: Instead of using 'sounds like' as instructed, the system used 'containing'.  See the notes below for why this is done.<br>";
			break;
	}
}

# are we setting the whole SQL condition or just the operator and the value?
$sqlSetAll = false;

#if ($Debug) echo "at line " . __LINE__ . " sqlUserSpecifiedCondition='$sqlUserSpecifiedCondition'<br>";
if ($Debug) echo "at line " . __LINE__ . " stype='$stype'<br>";


switch ($method) {
	case 'prefix':
		$WildCardMatch = "$query%";
		if ($casesensitivity == 'casesensitive') {
			$Like = 'LIKE';
		} else {
			$Like = 'ILIKE';
		}
		$sqlUserSpecifiedCondition = WildCardQuery($stype, $Like, $WildCardMatch);
		break;

	case 'match':
		$WildCardMatch = "%$query%";
		if ($casesensitivity == 'casesensitive') {
			$Like = 'LIKE';
		} else {
			$Like = 'ILIKE';
		}
		$sqlUserSpecifiedCondition = WildCardQuery($stype, $Like, $WildCardMatch);
		break;

	case 'suffix':
		$WildCardMatch = "%$query";
		if ($casesensitivity == 'casesensitive') {
			$Like = 'LIKE';
		} else {
			$Like = 'ILIKE';
		}
		$sqlUserSpecifiedCondition = WildCardQuery($stype, $Like, $WildCardMatch);
		break;

	default:
	case 'exact':
		switch ($stype) {
			case SEARCH_FIELD_DEPENDS_ALL:
				if ($casesensitivity == 'casesensitive') {
					$sqlUserSpecifiedCondition = "\n     (P.depends_build = '" . pg_escape_string($query) . "' OR P.depends_lib = '" . pg_escape_string($query) . "' OR P.depends_run = '" . pg_escape_string($query) . "')";
				} else {
					$sqlUserSpecifiedCondition = "\n     (lower(P.depends_build) = lower('" . pg_escape_string($query) . "') OR lower(P.depends_lib) = lower('" . pg_escape_string($query) . "') OR lower(P.depends_run) = lower('" . pg_escape_string($query) . "'))";
				}
				break;

			default:
                $sqlSetAll = true;
				$FieldName = $SearchTypeToFieldMap[$stype];
				if (empty($FieldName)) {
				   die('you are probably doing this wrong');
				}
				if ($casesensitivity == 'casesensitive') {
					$sqlUserSpecifiedCondition = "     $FieldName = '" . pg_escape_string($query) . "'";
				} else {
					$sqlUserSpecifiedCondition = "     lower($FieldName) = lower('" . pg_escape_string($query) . "')";
				}
				break;
		}
		break;

	case 'soundex':
	    $sqlSetAll = true;
		switch ($stype) {
			case SEARCH_FIELD_DEPENDS_ALL:
				$sqlUserSpecifiedCondition = "\n     (levenshtein(substring(P.depends_build FOR 255), '" . pg_escape_string($query) . "') < " . VEVENSHTEIN_MATCH . 
				                               "   OR levenshtein(substring(P.depends_lib   FOR 255), '" . pg_escape_string($query) . "') < " . VEVENSHTEIN_MATCH .
											   "   OR levenshtein(substring(P.depends_run   FPR 255), '" . pg_escape_string($query) . "') < " . VEVENSHTEIN_MATCH . ')';
				$sqlSoundsLikeOrderBy = "levenshtein(substring(P.depends_build for 255) + levenshtein(substring(P.depends_lib for 255), '" . pg_escape_string($query) . "') + levenshtein(substring(P.depends_run for 255), '" . pg_escape_string($query) . "')";
				break;

			default:
				$FieldName = $SearchTypeToFieldMap[$stype];
				$sqlUserSpecifiedCondition = "\n     levenshtein($FieldName, '" . pg_escape_string($query) . "') < " . VEVENSHTEIN_MATCH;
				$sqlSoundsLikeOrderBy = "levenshtein($FieldName, '" . pg_escape_string($query) . "')";
				break;
		}
		break;
}

if ($Debug) echo "at line " . __LINE__ . " sqlUserSpecifiedCondition='$sqlUserSpecifiedCondition'<br>";

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
				$sqlUserSpecifiedCondition .= " and E.status = 'A' ";
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
	


switch ($stype) {
  case SEARCH_FIELD_COMMITTER:
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

    if ($include_src_commits) {
      echo 'searching src';
      require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_committer.php');
      $Commits = new CommitsByCommitter($db);
    } else {
      echo 'not searching src';
      require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port_commits_by_committer.php');
      $Commits = new PortCommitsByCommitter($db);
    }
    $Commits->CommitterSet($query);
    
    $Commits->Debug = $Debug;
  
    $NumberOfCommits = $Commits->GetCountCommits($query);
    if ($Debug) echo 'number of commits = ' . $NumberOfCommits . "<br>\n";

	$NumFound = $NumberOfCommits;
	$params = array(
			'mode'        => 'Sliding',
			'perPage'     => $PageSize,
			'delta'       => 5,
			'totalItems'  => $NumFound,
			'urlVar'      => 'page',
			'currentPage' => $PageNumber,
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
		);
	$Pager = & Pager::factory($params);

	$offset = $Pager->getOffsetByPageId();
	$NumOnThisPage = $offset[1] - $offset[0] + 1;

    if ($PageNumber > 1) {
      $Commits->SetOffset($offset[0] - 1);
    }
    $Commits->SetLimit($PageSize);

    $NumFetches = $Commits->Fetch();
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
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
		);
	$Pager = & Pager::factory($params);

	$offset = $Pager->getOffsetByPageId();
	$NumOnThisPage = $offset[1] - $offset[0] + 1;

    if ($PageNumber > 1) {
      $Commits->SetOffset($offset[0] - 1);
    }
    $Commits->SetLimit($PageSize);
  
    $NumFetches = $Commits->Fetch();
    break;
    
  case SEARCH_FIELD_PATHNAME:
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commits_by_tree_location.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/display_commit.php');

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
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
		);
	$Pager = & Pager::factory($params);

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
    break;
    
  default:
$sqlSelectFields = "
  select distinct
         CL.commit_date - SystemTimeAdjust() AS last_commit_date, 
         P.id, 
         E.name as port,
         C.name as category, 
         C.id as category_id, 
         P.version as version, 
         P.revision as revision, 
         P.portepoch as epoch, 
         P.maintainer, 
         P.short_description, 
         P.package_exists, 
         P.extract_suffix, 
         P.homepage, 
         E.status, 
         P.element_id, 
         P.broken, 
         P.deprecated, 
         P.ignore, 
         PV.current as vulnerable_current,
         PV.past    as vulnerable_past,
         P.forbidden,
         P.master_port,
         P.latest_link,
         P.no_package,
         P.package_name,
         P.restricted,
         P.no_cdrom,
         P.expiration_date,
         P.no_package,
         P.license,
         R.svn_hostname,
         R.path_to_repo,
         element_pathname(P.element_id) as element_pathname  ";
         
$sqlSelectCount = "
  SELECT count(*)";
  
	if ($User->id) {
		$sqlExtraFields .= ",
         onwatchlist";
   }

	$sqlFrom = "
  FROM ports P LEFT OUTER JOIN ports_vulnerable PV ON PV.port_id       = P.id 
               LEFT OUTER JOIN commit_log       CL ON P.last_commit_id = CL.id 
               LEFT OUTER JOIN repo             R  ON CL.repo_id       = R.id, 
       categories C, element E
";                                       	
#    from ports LEFT OUTER JOIN ports_vulnerable on ports_vulnerable.port_id = ports.id JOIN commit_log CL on ports.last_commit_id = CL.id JOIN repo R on CL.repo_id = R.id , categories, element  ";

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

	$sqlWhere = '
    WHERE P.category_id  = C.id
      AND P.element_id   = E.id ' ;


$AddRemoveExtra  = "&&origin=" . $_SERVER['SCRIPT_NAME'] . "?query=" . $query. "+stype=$stype+num=$num+method=$method";
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";
$AddRemoveExtra = pg_escape_string($AddRemoveExtra);
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";


### how many rows is this?

$sql = $sqlSelectCount . $sqlFrom .  $sqlWhere . ' AND ' . $sqlUserSpecifiedCondition;

if ($Debug) {
	echo "<pre>$sql<pre>\n";
}

$result  = pg_exec($db, $sql);
if (!$result) {
  syslog(LOG_NOTICE, pg_errormessage() . ': ' . $sql);
  die('something went terribly wrong.  Sorry.');
}

$NumRows = pg_numrows($result);
$myrow = pg_fetch_array ($result);
$NumFound = $myrow[0];

	$params = array(
			'mode'        => 'Sliding',
			'perPage'     => $PageSize,
			'delta'       => 5,
			'totalItems'  => $NumFound,
			'urlVar'      => 'page',
			'currentPage' => $PageNumber,
			'spacesBeforeSeparator' => 1,
			'spacesAfterSeparator'  => 1,
		);
	$Pager = Pager::factory($params);



$sqlOffsetLimit = '';
$offset = $Pager->getOffsetByPageId();
$NumOnThisPage = $offset[1] - $offset[0] + 1;
if ($PageNumber > 1) {
	$sqlOffsetLimit .= "\nOFFSET " . ($offset[0] - 1);
	unset($offset);
}

if ($PageSize) {
	$sqlOffsetLimit .= "\nLIMIT " . $PageSize;
}


$sql = $sqlSelectFields . $sqlExtraFields . $sqlFrom . $sqlWatchListFrom . 
        $sqlWhere . ' AND ' . $sqlUserSpecifiedCondition . $sqlOrderBy . $sqlOffsetLimit;

if ($Debug) {
	echo "<pre>$sql<pre>\n";
}

$result  = pg_exec($db, $sql);
if (!$result) {
  syslog(LOG_NOTICE, pg_errormessage() . ': ' . $sql);
  die('something went terribly wrong.  Sorry.');
}

$NumFetches = pg_numrows($result);

} // end of non-committer search

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
<gcse:searchbox-only></gcse:searchbox-only>
<!-- SiteSearch Google -->

</table>

<br>

<?php echo freshports_MainContentTable(); ?>
  <tr>
	<? echo freshports_PageBannerText("The FreshPorts Search"); ?>
  </tr>
<tr><td valign="top">


<form ACTION="<? echo $_SERVER["PHP_SELF"] ?>" name="search" >
	<SELECT NAME="stype" size="1">
		<OPTION VALUE="<?php echo SEARCH_FIELD_NAME             . '"'; if ($stype == SEARCH_FIELD_NAME)             echo 'SELECTED'; ?>>Port Name</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PACKAGE          . '"'; if ($stype == SEARCH_FIELD_PACKAGE)          echo 'SELECTED'; ?>>Package Name</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LATEST_LINK      . '"'; if ($stype == SEARCH_FIELD_LATEST_LINK)      echo 'SELECTED'; ?>>Latest Link</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MAINTAINER       . '"'; if ($stype == SEARCH_FIELD_MAINTAINER)       echo 'SELECTED'; ?>>Maintainer</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_COMMITTER        . '"'; if ($stype == SEARCH_FIELD_COMMITTER)        echo 'SELECTED'; ?>>Committer</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_SHORTDESCRIPTION . '"'; if ($stype == SEARCH_FIELD_SHORTDESCRIPTION) echo 'SELECTED'; ?>>Short Description</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_LONGDESCRIPTION  . '"'; if ($stype == SEARCH_FIELD_LONGDESCRIPTION)  echo 'SELECTED'; ?>>Long Description</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_BUILD    . '"'; if ($stype == SEARCH_FIELD_DEPENDS_BUILD)    echo 'SELECTED'; ?>>Depends Build</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_LIB      . '"'; if ($stype == SEARCH_FIELD_DEPENDS_LIB)      echo 'SELECTED'; ?>>Depends Lib</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_RUN      . '"'; if ($stype == SEARCH_FIELD_DEPENDS_RUN)      echo 'SELECTED'; ?>>Depends Run</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_DEPENDS_ALL      . '"'; if ($stype == SEARCH_FIELD_DEPENDS_ALL)      echo 'SELECTED'; ?>>Depends Build/Lib/Run</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_MESSAGEID        . '"'; if ($stype == SEARCH_FIELD_MESSAGEID)        echo 'SELECTED'; ?>>Message ID</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_COMMITMESSAGE    . '"'; if ($stype == SEARCH_FIELD_COMMITMESSAGE)    echo 'SELECTED'; ?>>Commit Message</OPTION>
		<OPTION VALUE="<?php echo SEARCH_FIELD_PATHNAME         . '"'; if ($stype == SEARCH_FIELD_PATHNAME)         echo 'SELECTED'; ?>>Under a pathname</OPTION>
	</SELECT> 

	<SELECT name=method>
		<OPTION VALUE="exact"   <?if ($method == "exact"  ) echo 'SELECTED' ?>>equal to
		<OPTION VALUE="prefix"  <?if ($method == "prefix" ) echo 'SELECTED' ?>>starting with
		<OPTION VALUE="match"   <?if ($method == "match"  ) echo 'SELECTED' ?>>containing
		<OPTION VALUE="suffix"  <?if ($method == "suffix" ) echo 'SELECTED' ?>>ending with
		<OPTION VALUE="soundex" <?if ($method == "soundex") echo 'SELECTED' ?>>sounds like
	</SELECT>

	<INPUT NAME="query" size="40"  VALUE="<? echo
	htmlentities(stripslashes($query))?>">

<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/page_options.php');
    $PageOptions = new ItemsPerPage();
    echo $PageOptions->DDLB_Choices('num', $num, 'results');
?>

	<BR><br>

<table cellpadding="5" cellspacing="0" border="0">
<tr>
<td valign="middle">
	<INPUT TYPE=checkbox <? if ($deleted == INCLUDE_DELETED_PORTS) echo 'CHECKED'; ?> VALUE=<?php echo INCLUDE_DELETED_PORTS; ?> NAME=deleted> Include deleted ports
</td>
<td valign="middle">
	<INPUT TYPE=checkbox <? if ($casesensitivity == "casesensitive")   echo 'CHECKED'; ?> VALUE=casesensitive   NAME=casesensitivity> Case sensitive search
<td valign="middle">
	Sort by: <SELECT name="orderby">
		<OPTION VALUE="<?php echo ORDERBYPORT;     ?>" <?if ($orderby == ORDERBYPORT        ) echo 'SELECTED' ?>>Port
		<OPTION VALUE="<?php echo ORDERBYCATEGORY; ?>" <?if ($orderby == ORDERBYCATEGORY    ) echo 'SELECTED' ?>>Category
	</SELECT>

	<SELECT name="orderbyupdown">
		<OPTION VALUE="<?php echo ORDERBYASCENDING;  ?>" <?if ($orderbyupdown == ORDERBYASCENDING  ) echo 'SELECTED' ?>>ascending
		<OPTION VALUE="<?php echo ORDERBYDESCENDING; ?>" <?if ($orderbyupdown == ORDERBYDESCENDING ) echo 'SELECTED' ?>>descending
	</SELECT>
</td>
<td>
	<INPUT TYPE="submit" VALUE="Search" NAME="search">
</td>
</tr><tr>
<td>
	<INPUT TYPE=checkbox <? if ($include_src_commits == INCLUDE_SRC_COMMITS) echo 'CHECKED'; ?> VALUE=<?php echo INCLUDE_SRC_COMMITS; ?> NAME=<?php echo INCLUDE_SRC_COMMITS; ?>> Include /src tree
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
}
?>
<?
if ($search) {
echo "<tr><td>\n";

if ($NumFetches == 0) {
   $HTML .= " no results found<br>\n";
} else {
	if ($stype == 'committer' || $stype == 'commitmessage' || $stype == 'tree') {
	  $NumFetches = min($num, $NumberOfCommits);
	  if ($NumFetches != $NumberOfCommits) {
		$MoreToShow = 1;
      } else {
		$MoreToShow = 0;
      }

	  $NumPortsFound = 'Number of commits: ' . $NumberOfCommits;
      if ($NumFound > $PageSize) {
	    $NumPortsFound .= " (showing only $NumOnThisPage on this page)";
	  }
	} else {
	  if ($NumFetches != $NumRows) {
		$MoreToShow = 1;
      } else {
		$MoreToShow = 0;
      }

      $NumPortsFound = 'Number of ports: ' . $NumFound;
      if ($NumFound > $PageSize) {
	    $NumPortsFound .= " (showing only $NumOnThisPage on this page)";
	  }
	}
	
	
switch ($stype) {
	case SEARCH_FIELD_COMMITTER:
	case SEARCH_FIELD_COMMITMESSAGE:
	case SEARCH_FIELD_PATHNAME:
		$DisplayCommit = new DisplayCommit($db, $Commits->LocalResult);
		$links = $Pager->GetLinks();
		
		$HTML .= $NumPortsFound . ' ' . $links['all'];
		$HTML .= $DisplayCommit->CreateHTML();
		$HTML .= '<tr><td>' . $NumPortsFound . ' ' . $links['all'] . '</td></tr>';
		break;

	default:
		require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/port-display.php');
	
		$links = $Pager->GetLinks();
		
		$HTML .= $NumPortsFound . ' ' . $links['all'];
	
		GLOBAL $User;
		$port_display = new port_display($db, $User);
		$port_display->SetDetailsSearch();
	
		for ($i = 0; $i < $NumFetches; $i++) {
			$Port->FetchNth($i);
			$port_display->port = $Port;
			$Port_HTML = $port_display->Display();
	
			$HTML .= $port_display->ReplaceWatchListToken($Port->{'onwatchlist'}, $Port_HTML, $Port->{'element_id'});
	    }
	
		$HTML .= $NumPortsFound . ' ' . $links['all'];
	}
}


echo $HTML;
}
?>
</table>

</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
  <?
  echo freshports_SideBar();
  ?>
  </td>

</tr>
</table>
<?
echo freshports_ShowFooter();
?>

</body>
</html>
