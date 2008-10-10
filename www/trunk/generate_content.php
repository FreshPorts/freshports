<?
# $Id: generate_content.php,v 1.1 2008-10-10 19:44:15 dan Exp $
#
# Copyright (c) 2008 Wesley Shields <wxs@FreeBSD.org>
#
# This code will generate JSON objects that can be used by the flot
# graphing library to draw the graphs.

header("Content-type: application/x-javascript");

// XXX: Which of these are necessary?
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

/* 
 * XXX: More graphs to come up with:
 *  - Total commits tracked?
 *  - Top committer on a given date?
 */

/*
 * Given a ds (dataset) parameter we execute the corresponding query
 * and then format the JSON object. 
 */
switch ($_GET['ds']) {
	case ('top10Committers()'):
		$result = pg_query("select committer, count(committer) from commit_log group by committer order by count(committer) desc limit 10") or die("Query error. (1)");
		echo "[ ";
		$i = 0;
		while ($row = pg_fetch_row($result)) {
			echo "{ ";
			echo "\"label\": \"$row[0]\", ";
			echo "\"data\": [[$i, $row[1]]] ";
			echo "}, ";
			$i++;
		}
		echo " ]";
		break;
	case ('commitsOverTime()'):
		$result = pg_query("select date_trunc('day', commit_date) as date, count(commit_date) from commit_log group by date") or die("Query error. (2)");
		echo "[ ";
		while ($row = pg_fetch_row($result)) {
			$epoch = milliepoch($row[0]);
			echo "[ $epoch, $row[1] ], ";
		}
		echo " ]";
		break;
	case ('commitsOverTimeByCommitter()'):
		$result = pg_query("select date_trunc('month', commit_date) as date, committer, count(committer) as num from commit_log group by date, committer order by committer") or die ("Query error. (3)");
		$old = "";
		$seen = TRUE;
		echo "{ \n";
		while ($row = pg_fetch_row($result)) {
			$epoch = milliepoch($row[0]);
			if ($old == $row[1]) {
				echo ", [ $epoch, $row[2] ]";
			}
			else {
				$old = $row[1];
				if (!$seen) {
					echo "]\n";
					echo "    },\n";
				}
				else {
					$seen = FALSE;
				}
				echo "    \"$row[1]\": { \n";
				echo "        \"label\": \"$row[1]\", \n";
				echo "        \"data\": [[ $epoch, $row[2] ]";
			}
		}
		echo "]\n";
		echo "    }\n";
		echo "}";
		break;
	case ('portsByCategory()'):
		$result = pg_query("select count(p.port_id), c.name from categories c left join ports_categories p on (c.id = p.category_id) group by c.name order by count desc limit 10") or die ("Query error. (4)");
		echo "[ ";
		$i = 0;
		while ($row = pg_fetch_row($result)) {
			echo "{ ";
			echo "\"label\": \"$row[1]\", ";
			echo "\"data\": [[$i, $row[0]]] ";
			echo "}, ";
			$i++;
		}
		echo " ]";
		break;
	case ('brokenPorts()'):
		/* Seriously? */
		$result = pg_query("select to_char(F.date, 'YYYY-MM-DD'), F.value as forbidden, B.value as broken, E.value as expired, N.value as new from daily_stats DSF, daily_stats DSB, daily_stats DSE, daily_stats DSN, daily_stats_data as F, daily_stats_data as B, daily_stats_data as E, daily_stats_data as N where (F.daily_stats_id = DSF.id and DSF.title = 'Forbidden ports') and (B.daily_stats_id = DSB.id and DSB.title = 'Broken ports') and (E.daily_stats_id = DSE.id and DSE.title = 'Expired Ports') and (N.daily_stats_id = DSN.id and DSN.title ='New ports') and F.date = B.date and B.date = E.date and E.date = N.date order by F.date desc limit 90") or die ("Query error. (5)");
		echo "{\n";
		$forbidden = "  \"forbidden\": {\n    \"label\": \"forbidden\",\n    \"data\": [";
		$broken = "  \"broken\": {\n    \"label\": \"broken\",\n    \"data\": [";
		$expired = "  \"expired\": {\n    \"label\": \"expired\",\n    \"data\": [";
		$new = "  \"new\": {\n     \"label\": \"new\",\n    \"data\": [";
		while ($row = pg_fetch_row($result)) {
			$epoch = milliepoch($row[0]);
			/* Not the cleanest - extra commas. :( */
			$forbidden .= "[$epoch, $row[1]],";
			$broken .= "[$epoch, $row[2]],";
			$expired .= "[$epoch, $row[3]],";
			$new .= "[$epoch, $row[4]],";
		}
		echo "$forbidden]\n  },\n";
		echo "$broken]\n  },\n";
		echo "$expired]\n  },\n";
		echo "$new]\n  }\n";
		echo "}";
		break;
	case ('portCount()'):
		$result = pg_query("select date_trunc('day', date), value from daily_stats, daily_stats_data where daily_stats_data.daily_stats_id = daily_stats.id and title = 'Port count' order by daily_stats_data.date") or die ("Query error. (6)");
		echo "[";
		while ($row = pg_fetch_row($result)) {
			$epoch = milliepoch($row[0]);
			echo "[$epoch, $row[1]],";
		}
		echo "]";
		break;
	default:
		echo "ERROR! BAD DS!";
}

/*
 * Convert to epoch in milliseconds.
 * This is probably not the best way to do it.
 */
function milliepoch($time) {
	$conv = mktime(0, 0, 0, substr($time, 5, 2), substr($time, 8, 2), substr($time, 0, 4));
	$ret = date('U', $conv);
	/* Debugging: Proves we are right - you get the dates back! */
	//$ret = date('M/d/Y', $conv);
	return ($ret *= 1000);
}
?>
