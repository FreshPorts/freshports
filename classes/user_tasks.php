<?php
	#
	# $Id: user_tasks.php,v 1.2 2006-12-17 11:37:21 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#


DEFINE('FRESHPORTS_TASKS_CATEGORY_VIRTUAL_DESCRIPTION_SET', 'CategoryVirtualDescriptionSet');
DEFINE('FRESHPORTS_TASKS_ANNOUNCEMENTS_MAINTAIN',           'AnnouncementsUpdate');

// base class for user tasks
class UserTasks {

	var $user_id;
	var $tasks;

	var $dbh;

	function UserTasks($dbh) {
		$this->dbh	= $dbh;
	}

	function FetchByID($user_id) {
#		echo "\$user_id = '$user_id'<br>\n";
		if (IsSet($user_id)) {
			$this->id = $user_id;
		}
		$sql = "select id, name from user_tasks, tasks where user_id = " . pg_escape_string($this->id) . " and user_tasks.task_id = tasks.id";
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			for ($i = 0; $i < $numrows; $i++) {
				$myrow = pg_fetch_array ($result, $i);
				$this->tasks{$myrow['name']} = $myrow['id'];
#				echo "\$myrow[name]='$myrow[name]' = $myrow[id]<br>\n";
#				echo "\$this->tasks{$myrow[name]} = '$this->tasks{$myrow[name]}'<br>\n";
			}
		}

        return $this->id;
	}
}

