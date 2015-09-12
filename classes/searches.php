<?php
	#
	# $Id: searches.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 2004 DVL Software Limited
	#

define('FRESHPORTS_SEARCH_METHOD_Soundex', 'soundex');
define('FRESHPORTS_SEARCH_METHOD_Match',   'match');
define('FRESHPORTS_SEARCH_METHOD_Exact',   'exact');

define('FRESHPORTS_SEARCH_STYPE_Package',           'package');
define('FRESHPORTS_SEARCH_STYPE_LatestLink',        'latest_link');


define('FRESHPORTS_SEARCH_DEFAULT_Num',             10);
define('FRESHPORTS_SEARCH_DEFAULT_Stype',           'name');
define('FRESHPORTS_SEARCH_DEFAULT_Method',          FRESHPORTS_SEARCH_METHOD_Match);
define('FRESHPORTS_SEARCH_DEFAULT_Deleted',         'excludedeleted');
define('FRESHPORTS_SEARCH_DEFAULT_Start',           1);
define('FRESHPORTS_SEARCH_DEFAULT_Casesensitivity', 'caseinsensitive');


// base class for searches
class Searches {
	var $SearchPage;

	var $dbh;

	function Searches($dbh, $SearchPage = '/search.php') {
		$this->dbh        = $dbh;
		$this->SearchPage = $SearchPage;
	}

	function GetFormSimple($text) {
return '
	<FORM ACTION="' . $this->SearchPage . '" NAME="f">
	Enter Keywords:<BR>
	<INPUT NAME="query"  TYPE="text" SIZE="8">' . $text . '<INPUT TYPE="submit" VALUE="go" NAME="search">
	<INPUT NAME="num"             TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Num             . '">
	<INPUT NAME="stype"           TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Stype           . '">
	<INPUT NAME="method"          TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Method          . '">
	<INPUT NAME="deleted"         TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Deleted         . '">
	<INPUT NAME="start"           TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Start           . '">
  	<INPUT NAME="casesensitivity" TYPE="hidden" value="' . FRESHPORTS_SEARCH_DEFAULT_Casesensitivity . '" >
	</FORM>
';
	}

	function GetDefaultSearchString($text) {
        return $this->SearchPage . '?' . 'query='           . $text                              .
              '&num='             . FRESHPORTS_SEARCH_DEFAULT_Num      . 
              '&stype='           . FRESHPORTS_SEARCH_DEFAULT_Stype    .
              '&method='          . FRESHPORTS_SEARCH_DEFAULT_Method   . 
              '&deleted='         . FRESHPORTS_SEARCH_DEFAULT_Deleted  .
              '&start='           . FRESHPORTS_SEARCH_DEFAULT_Start    .
              '&casesensitivity=' . FRESHPORTS_SEARCH_DEFAULT_Casesensitivity;
	}

	function GetDefaultSearchStringPackage($text) {
        return $this->SearchPage . '?' . 'query='           . $text                              .
              '&num='             . FRESHPORTS_SEARCH_DEFAULT_Num      . 
              '&stype='           . FRESHPORTS_SEARCH_STYPE_Package    .
              '&method='          . FRESHPORTS_SEARCH_DEFAULT_Method   . 
              '&deleted='         . FRESHPORTS_SEARCH_DEFAULT_Deleted  .
              '&start='           . FRESHPORTS_SEARCH_DEFAULT_Start    .
              '&casesensitivity=' . FRESHPORTS_SEARCH_DEFAULT_Casesensitivity;
	}

	function GetDefaultMethodString($text, $method) {
        return $this->SearchPage . '?' . 'query='           . $text    .
              '&num='             . FRESHPORTS_SEARCH_DEFAULT_Num      .
              '&stype='           . FRESHPORTS_SEARCH_DEFAULT_Stype    .
              '&method='          . $method                            .
              '&deleted='         . FRESHPORTS_SEARCH_DEFAULT_Deleted  .
              '&start='           . FRESHPORTS_SEARCH_DEFAULT_Start    .
              '&casesensitivity=' . FRESHPORTS_SEARCH_DEFAULT_Casesensitivity;
	}

	function GetDefaultMethodStringPackage($text, $method) {
        return $this->SearchPage . '?' . 'query='           . $text    .
              '&num='             . FRESHPORTS_SEARCH_DEFAULT_Num      .
              '&stype='           . FRESHPORTS_SEARCH_STYPE_Package    .
              '&method='          . $method                            . 
              '&deleted='         . FRESHPORTS_SEARCH_DEFAULT_Deleted  .
              '&start='           . FRESHPORTS_SEARCH_DEFAULT_Start    .
              '&casesensitivity=' . FRESHPORTS_SEARCH_DEFAULT_Casesensitivity;
	}

#
# This whole area needs to be cleaned up and improved
# The various methods should be consolidated
#

	function GetDefaultSoundsLikeString($text) {
        return $this->GetDefaultMethodString($text, FRESHPORTS_SEARCH_METHOD_Soundex);
	}

	function GetDefaultPackageSoundsLikeString($text) {
        return $this->GetDefaultMethodString($text, FRESHPORTS_SEARCH_METHOD_Soundex);
	}

	function GetDefaultPackage($text) {
        return $this->GetDefaultMethodString($text, FRESHPORTS_SEARCH_METHOD_Soundex);
	}

	function GetLink($name, $search_type, $package = 0) {
        return $this->GetDefaultMethodStringPackage($name, $search_type);
	}

}

