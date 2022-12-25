<?php
	#
	# Copyright (c) 2016 Dan Langille
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
    $this->dbh	= $dbh;
  }
  
  function InMaintenanceMode() {
    return defined('IN_MAINTENANCE_MODE') && IN_MAINTENANCE_MODE;
  }

  function LoginsAreAllowed() {
    return !defined('NO_LOGIN');
  }

}
