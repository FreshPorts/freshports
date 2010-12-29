<?php
	#
	# $Id: search.php,v 1.12 2010-12-29 00:01:47 dan Exp $
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
	SEARCH_FIELD_NAME 				=> 'element.name',
	SEARCH_FIELD_PACKAGE			=> 'ports.package_name',
	SEARCH_FIELD_LATEST_LINK		=> 'ports.latest_link',
	SEARCH_FIELD_SHORTDESCRIPTION	=> 'ports.short_description',
	SEARCH_FIELD_LONGDESCRIPTION	=> 'ports.long_description',
	SEARCH_FIELD_DEPENDS_BUILD		=> 'ports.depends_build',
	SEARCH_FIELD_DEPENDS_LIB		=> 'ports.depends_lib',
	SEARCH_FIELD_DEPENDS_RUN		=> 'ports.depends_run',
	SEARCH_FIELD_DEPENDS_ALL		=> 'ports.depends_all',
	SEARCH_FIELD_MAINTAINER			=> 'ports.maintainer',
	SEARCH_FIELD_COMMITMESSAGE		=> 'commit_log.description',
	SEARCH_FIELD_COMMITTER			=> 'commit_log.committer',
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
      $sql .= " $Like '$query'";
      break;

    case SEARCH_FIELD_DEPENDS_ALL:
      $sql .= "\n     (ports.depends_build $Like '$query' OR ports.depends_lib $Like '$query' OR ports.depends_run $Like '$query')";
      break;

    default:
      if (!IsSet($SearchTypeToFieldMap[$stype])) {
        syslog(LOG_ERR, __FILE__ . '::' . __LINE__ . " unknown stype supplied: '$stype'");
        die('something terrible has happened!');
      }
      $sql .= "\n     " .  $SearchTypeToFieldMap[$stype] . " $Like '$query'";
      break;
	}

	return $sql;
}

	#
	# I became annoyed with people creating their own search pages instead of using
	# mine... If the referrer isn't us, ignore them
	#

	if ($RejectExternalSearches  && $_SERVER["HTTP_REFERER"] != '') {
		$pos = strpos($_SERVER["HTTP_REFERER"], "http://" . $_SERVER["SERVER_NAME"]);
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
	if (IsSet($_REQUEST['query']))           $query				= AddSlashes(trim($_REQUEST['query']));
	if (IsSet($_REQUEST['stype']))           $stype				= AddSlashes(trim($_REQUEST['stype']));
	if (IsSet($_REQUEST['num']))             $num			 = intval(AddSlashes(trim($_REQUEST['num'])));
	if (IsSet($_REQUEST['category']))        $category			= AddSlashes(trim($_REQUEST['category']));
	if (IsSet($_REQUEST['port']))            $port				= AddSlashes(trim($_REQUEST['port']));
	if (IsSet($_REQUEST['method']))          $method			= AddSlashes(trim($_REQUEST['method']));
	if (IsSet($_REQUEST['deleted']))         $deleted			= AddSlashes(trim($_REQUEST['deleted']));
	if (!IsSet($_REQUEST[INCLUDE_SRC_COMMITS])) $include_src_commits	= '';
	if (IsSet($_REQUEST['casesensitivity'])) $casesensitivity	= AddSlashes(trim($_REQUEST['casesensitivity']));
	if (IsSet($_REQUEST['orderby']))         $orderby			= AddSlashes(trim($_REQUEST['orderby']));
	if (IsSet($_REQUEST['orderbyupdown']))   $orderbyupdown		= AddSlashes(trim($_REQUEST['orderbyupdown']));
	
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
		header('Location: http://' . $_SERVER['HTTP_HOST'] . "/commit.php?message_id=$query");
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
          die('something terrible has happened!');
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

if ($Debug) echo "at line " . __LINE__ . " sqlUserSpecifiedCondition='$sqlUserSpecifiedCondition'<br>";
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
					$sqlUserSpecifiedCondition = "\n     (ports.depends_build = '$query' OR ports.depends_lib = '$query' OR ports.depends_run = '$query')";
				} else {
					$sqlUserSpecifiedCondition = "\n     (lower(ports.depends_build) = lower('$query') OR lower(ports.depends_lib) = lower('$query') OR lower(ports.depends_run) = lower('$query'))";
				}
				break;

			default:
                $sqlSetAll = true;
				$FieldName = $SearchTypeToFieldMap[$stype];
				if ($casesensitivity == 'casesensitive') {
					$sqlUserSpecifiedCondition = "     $FieldName = '$query'";
				} else {
					$sqlUserSpecifiedCondition = "     lower($FieldName) = lower('$query')";
				}
				break;
		}
		break;

	case 'soundex':
	    $sqlSetAll = true;
		switch ($stype) {
			case SEARCH_FIELD_DEPENDS_ALL:
				$sqlUserSpecifiedCondition = "\n     (levenshtein(substring(ports.depends_build FOR 255), '$query') < " . VEVENSHTEIN_MATCH . 
				                               "   OR levenshtein(substring(ports.depends_lib   FOR 255), '$query') < " . VEVENSHTEIN_MATCH .
											   "   OR levenshtein(substring(ports.depends_run   FPR 255), '$query') < " . VEVENSHTEIN_MATCH . ')';
				$sqlSoundsLikeOrderBy = "levenshtein(substring(ports.depends_build for 255) + levenshtein(substring(ports.depends_lib for 255), '$query') + levenshtein(substring(ports.depends_run for 255), '$query')";
				break;

			default:
				$FieldName = $SearchTypeToFieldMap[$stype];
				$sqlUserSpecifiedCondition = "\n     levenshtein($FieldName, '$query') < " . VEVENSHTEIN_MATCH;
				$sqlSoundsLikeOrderBy = "levenshtein($FieldName, '$query')";
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
				$sqlUserSpecifiedCondition .= " and element.status = 'A' ";
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
						$sqlOrderBy = "\n ORDER BY categories.name desc, element.name";
						break;
		
					case ORDERBYASCENDING:
						$sqlOrderBy = "\n ORDER BY categories.name, element.name";
						break;
				}
				break;
		
			case ORDERBYPORT:
			default:
				switch ($orderbyupdown) {
					case ORDERBYDESCENDING:
					default:
						$sqlOrderBy = "\n ORDER BY element.name desc, categories.name";
						break;
		
					case ORDERBYASCENDING:
						$sqlOrderBy = "\n ORDER BY element.name, categories.name";
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
         ports.id, 
         element.name as port,
         categories.name as category, 
         categories.id as category_id, 
         ports.version as version, 
         ports.revision as revision, 
         ports.portepoch as epoch, 
         ports.maintainer, 
         ports.short_description, 
         ports.package_exists, 
         ports.extract_suffix, 
         ports.homepage, 
         element.status, 
         ports.element_id, 
         ports.broken, 
         ports.deprecated, 
         ports.ignore, 
         ports_vulnerable.current as vulnerable_current,
         ports_vulnerable.past    as vulnerable_past,
         ports.forbidden,
         ports.master_port,
         ports.latest_link,
         ports.no_package,
         ports.package_name,
         ports.restricted,
         ports.no_cdrom,
         ports.expiration_date,
         ports.no_package,
         ports.license  ";
         
$sqlSelectCount = "
  SELECT count(*)";
  
	if ($User->id) {
		$sqlExtraFields .= ",
         onwatchlist";
   }

	$sqlFrom = "
    from ports LEFT OUTER JOIN ports_vulnerable on ports_vulnerable.port_id = ports.id , categories, element  ";

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
       ON TEMP.wle_element_id = element.id";
	}

	$sqlWhere = '
	WHERE ports.category_id  = categories.id
      and ports.element_id   = element.id ' ;


$AddRemoveExtra  = "&&origin=" . $_SERVER['SCRIPT_NAME'] . "?query=" . $query. "+stype=$stype+num=$num+method=$method";
if ($Debug) echo "\$AddRemoveExtra = '$AddRemoveExtra'\n<BR>";
$AddRemoveExtra = AddSlashes($AddRemoveExtra);
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
	$Pager = & Pager::factory($params);



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

<style type="text/css">
@import url(http://www.google.com/cse/api/branding.css);
</style>
<div class="cse-branding-bottom" style="background-color:#FFFFFF;color:#000000">
  <div class="cse-branding-form">
    <form action="http://www.google.com/cse" id="cse-search-box" target="_blank">
      <div>
        <input type="hidden" name="cx" value="partner-pub-0711826105743221:o2t5woa4k6z" />
        <input type="hidden" name="ie" value="ISO-8859-1" />
        <input type="text" name="q" size="70" />
        <input type="submit" name="sa" value="Search" />
      </div>
    </form>
  </div>
  <div class="cse-branding-logo">
    <img src="http://www.google.com/images/poweredby_transparent/poweredby_FFFFFF.gif" alt="Google" />
  </div>
  <div class="cse-branding-text">
    Custom Search
  </div>
</div>
<!-- SiteSearch Google -->


</td></tr>
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
	<INPUT NAME="method"          TYPE="hidden" value="match">
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
