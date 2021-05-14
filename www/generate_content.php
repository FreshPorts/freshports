<?
# $Id: generate_content.php,v 1.1 2008-10-10 19:44:15 dan Exp $
#
# Copyright (c) 2008 Wesley Shields <wxs@FreeBSD.org>
#
# This code will generate JSON objects that can be used by the flot
# graphing library to draw the graphs.

header("Content-type: application/json");

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
	case ('top10committers()'):
		$result = pg_query("select committer, count(committer) from commit_log group by committer order by count(committer) desc limit 10") or die("Query error. (1)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(
				"label" => $row[0],
				"data" => [[count($data), intval($row[1])]],
			);
		}
		echo json_encode($data);
		break;
	case ('top10committers_doc()'):
		$result = pg_query("  SELECT CL.committer, count(*) AS count
    FROM commit_log CL, repo R
   WHERE CL.repo_id = R.id
     AND R.name     = 'doc'
GROUP BY CL.committer
ORDER BY count(*) DESC
   LIMIT 10") or die("Query error. (1)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(
				"label" => $row[0],
				"data" => [[count($data), intval($row[1])]],
			);
		}
		echo json_encode($data);
		break;
	case ('top10committers_ports()'):
		$result = pg_query("  SELECT CL.committer, count(*) AS count
    FROM commit_log CL, repo R
   WHERE CL.repo_id = R.id
     AND R.name     = 'ports'
GROUP BY CL.committer
ORDER BY count(*) DESC
   LIMIT 10") or die("Query error. (1)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(
				"label" => $row[0],
				"data" => [[count($data), intval($row[1])]],
			);
		}
		echo json_encode($data);
		break;
	case ('top10committers_src()'):
		$result = pg_query("  SELECT CL.committer, count(*) AS count
    FROM commit_log CL, repo R
   WHERE CL.repo_id = R.id
     AND R.name     = 'src'
GROUP BY CL.committer
ORDER BY count(*) DESC
   LIMIT 10") or die("Query error. (1)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(
				"label" => $row[0],
				"data" => [[count($data), intval($row[1])]],
			);
		}
		echo json_encode($data);
		break;
	case ('commitsOverTime()'):
		$result = pg_query("select extract(epoch from date_trunc('day', commit_date)) * 1000 as date, count(commit_date) from commit_log group by date") or die("Query error. (2)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = [intval($row[0]), intval($row[1])];
		}
		echo json_encode(array($data));
		break;
	case ('commitsOverTimeByCommitter()'):
		$result = pg_query("select extract(epoch from date_trunc('month', commit_date)) * 1000 as date, committer, count(committer) as num from commit_log group by date, committer order by committer") or die ("Query error. (3)");
		$data = array();
		$committer = array();
		$old = "";
		while ($row = pg_fetch_row($result)) {
			if ($old == $row[1]) {
				$committer[] = array(intval($row[0]), intval($row[2]));
			} else {
				if ($committer) {
					$data[$old] = array("label" => $old, "data" => $committer);
					$committer = array();
				}
				$old = $row[1];
				$committer[] = array(intval($row[0]), intval($row[2]));
			}
		}
		if ($committer) {
			$data[$old] = array("label" => $old, "data" => $committer);
			$committer = array();
		}
		echo json_encode($data);
		break;
	case ('portsByCategory()'):
		$result = pg_query("select count(p.port_id), c.name from categories c left join ports_categories p on (c.id = p.category_id) group by c.name order by count desc limit 10") or die ("Query error. (4)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(
				"label" => $row[1],
				"data" => [[count($data), intval($row[0])]],
			);
		}
		echo json_encode($data);
		break;
	case ('brokenPorts()'):
		/* Seriously? */
		$result = pg_query("select extract(epoch from date_trunc('day', F.date)) * 1000 as date, F.value as forbidden, B.value as broken, E.value as expired, N.value as new from daily_stats DSF, daily_stats DSB, daily_stats DSE, daily_stats DSN, daily_stats_data as F, daily_stats_data as B, daily_stats_data as E, daily_stats_data as N where (F.daily_stats_id = DSF.id and DSF.title = 'Forbidden ports') and (B.daily_stats_id = DSB.id and DSB.title = 'Broken ports') and (E.daily_stats_id = DSE.id and DSE.title = 'Expired Ports') and (N.daily_stats_id = DSN.id and DSN.title ='New ports') and F.date = B.date and B.date = E.date and E.date = N.date order by F.date desc limit 90") or die ("Query error. (5)");

		$forbidden = array();
		$broken = array();
		$expired = array();
		$new = array();

		while ($row = pg_fetch_array($result)) {
			$forbidden[] = array(intval($row["date"]), intval($row["forbidden"]));
			$broken[]    = array(intval($row["date"]), intval($row["broken"]));
			$expired[]   = array(intval($row["date"]), intval($row["expired"]));
			$new[]       = array(intval($row["date"]), intval($row["new"]));
		}
		echo json_encode(array(
			array("label" => "forbidden", "data" => $forbidden),
			array("label" => "broken",    "data" => $broken),
			array("label" => "expired",   "data" => $expired),
			array("label" => "new",       "data" => $new),
		));
		break;
	case ('portCount()'):
		$result = pg_query("select extract(epoch from date_trunc('day', date)) * 1000, value from daily_stats, daily_stats_data where daily_stats_data.daily_stats_id = daily_stats.id and title = 'Port count' order by daily_stats_data.date") or die ("Query error. (6)");
		$data = array();
		while ($row = pg_fetch_row($result)) {
			$data[] = array(intval($row[0]), intval($row[1]));
		}
		echo json_encode(array($data));
		break;
	default:
		echo "ERROR! BAD DS!";
}
