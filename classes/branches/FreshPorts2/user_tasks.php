<?
	# $Id: user_tasks.php,v 1.1.2.1 2003-01-10 16:03:25 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
	#


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
		$sql = "select id, name from user_tasks, tasks where user_id = $this->id and user_tasks.task_id = tasks.id";
#		echo "<pre>sql = '$sql'</pre><BR>";

		$result = pg_exec($this->dbh, $sql);
		if ($result) {
			$numrows = pg_numrows($result);
			if ($numrows == 1) {
#				echo "fetched by ID succeeded<BR>";
				$myrow = pg_fetch_array ($result, 0);
				$this->tasks{$myrow[name]} = $myrow[id];
#				echo "\$myrow[name]='$myrow[name]' = $myrow[id]<br>\n";
#				echo "\$this->tasks{$myrow[name]} = '$this->tasks{$myrow[name]}'<br>\n";
			}
		}

        return $this->id;
	}
}
