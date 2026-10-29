<?php
	#
	# Copyright (c) 2016-2026 Dan Langille
	#


/* base class for System Status, such as 
   * number of commits queued for processing
   * number of commits in processed directory (resets each day after archiving)
   * is commit processing enabled
   * are logins enabled
   * are we in maintenance mode
*/

class SystemStatus {

  var $dbh;

  function __construct($dbh) {
    $this->dbh = $dbh;
  }
  
  function InMaintenanceMode() {
    return defined('IN_MAINTENANCE_MODE') && IN_MAINTENANCE_MODE;
  }

  function LoginsAreAllowed() {
    return !defined('NO_LOGIN');
  }

  function VuXMLToProcess() {
    # the vulns process_vuxml.pl selects for update: new, or with a changed checksum
    # it commits only at the end, so during a run this stays at the run's total
    $result = @pg_query_params($this->dbh, 'SELECT count(*) FROM GetListOfVulnsForUpdate()', array());
    if (!$result) {
      # e.g. www lacks SELECT on vuxml_import; leave the line off the page
      return null;
    }

    return pg_fetch_result($result, 0, 0);
  }

}
