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
  
  function CommitQueueCount() {
    $dir   = MESSAGE_QUEUE_RECENT;
    $files = scandir($dir);
    if ($files) {
      # count the number of .txt files
      $count = 0;
      foreach ($files as $file) {
        if (preg_match('/\.txt$/', $file)) {
          $count++;
        }
      }
    } else {
      $count = 'unknown';
      syslog(LOG_ERR, 'Error could not get file count for message queue recent: ' . MESSAGE_QUEUE_RECENT);
    }
    
    return $count;
  }
  
  function InMaintenanceMode() {
    return defined(IN_MAINTENCE_MODE) && IN_MAINTENCE_MODE;
  }

  function LoginsAreAllowed() {
    return defined(NO_LOGIN);
  }

}
