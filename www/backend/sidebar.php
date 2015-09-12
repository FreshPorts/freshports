<?php
	#
	# $Id: sidebar.php,v 1.2 2006-12-17 12:06:22 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	DEFINE('MAX_PORTS', 40);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	$date        = pg_escape_string($_REQUEST['date']);
	$committer   = pg_escape_string($_REQUEST['committer']);
	$time        = pg_escape_string($_REQUEST['time']);

	freshports_HTML_Start();

?>

<HEAD>
	<TITLE><? echo $FreshPortsTitle; ?></TITLE>

	<STYLE TYPE="text/css">
	BODY, TD, TR, P, UL, OL, LI, INPUT, SELECT, DL, DD, DT, FONT
	{
		font-family: Helvetica, Verdana, Arial, Clean, sans-serif;
		font-size: 12px;
	}
	ul { padding-left: 20px;}
	</STYLE>

	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<?php
	$ServerName = str_replace('freshports', 'FreshPorts', $_SERVER['SERVER_NAME']);
?>
	<META HTTP-EQUIV="Refresh" CONTENT="1200; URL=http://<?php echo $ServerName . $_SERVER["PHP_SELF"]; ?>">
	<META http-equiv="Pragma"              content="no-cache">
	<META NAME="MSSmartTagsPreventParsing" content="TRUE">

</HEAD>

<?
	freshports_body();
	$ServerName = str_replace("freshports", "FreshPorts", $_SERVER["SERVER_NAME"]);
	GLOBAL $FreshPortsSlogan;
	GLOBAL $FreshPortsName;
?>

	<CENTER>
	<A HREF="http://<? echo $ServerName; ?>/" TARGET="_content"><IMG SRC="/images/freshports_mini.jpg" ALT="<?php echo "$FreshPortsName -- $FreshPortsSlogan"; ?>" TITLE="<?php echo "FreshPorts -- $FreshPortsSlogan"; ?>" WIDTH="128" HEIGHT="28" BORDER="0"></A>

	<BR>

	<SMALL>
	<script LANGUAGE="JavaScript" type="text/javascript">
		var d = new Date();  // today's date and time.
	    document.write(d.toLocaleString());
	</script>
	</SMALL>
	</CENTER>

<?

$sql = "
SELECT PEC.*
FROM (
SELECT PORTELEMENT.*,
       categories.name AS category
FROM (
SELECT LCPPORTS.*,
       element.name    AS port,
       element.status  AS status

FROM (
SELECT LCPCLLCP.*,
       ports.forbidden,
       ports.broken,
       ports.deprecated,
       ports.ignore,
       ports.element_id                     AS element_id,
       CASE when clp_version  IS NULL then ports.version  else clp_version  END as version,
       CASE when clp_revision IS NULL then ports.revision else clp_revision END AS revision,
       ports.version                        AS ports_version,
       ports.revision                       AS ports_revision,
       date_part('epoch', ports.date_added) AS date_added,
       ports.short_description              AS short_description,
       ports.category_id
FROM (
 SELECT LCPCL.*, 
         port_id,
         commit_log_ports.port_version  AS clp_version,
         commit_log_ports.port_revision AS clp_revision,
         commit_log_ports.needs_refresh AS needs_refresh
    FROM 
   (SELECT commit_log.id     AS commit_log_id, 
           commit_date       AS commit_date_raw,
           message_subject,
           message_id,
           committer,
           description       AS commit_description,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'DD Mon YYYY')  AS commit_date,
           to_char(commit_log.commit_date - SystemTimeAdjust(), 'HH24:MI')      AS commit_time,
           encoding_losses
     FROM commit_log JOIN
               (SELECT latest_commits_ports.commit_log_id
                   FROM latest_commits_ports
               ORDER BY latest_commits_ports.commit_date DESC
                 LIMIT $MaxNumberOfPorts) AS LCP
           ON commit_log.id = LCP.commit_log_id) AS LCPCL JOIN commit_log_ports
                         ON commit_log_ports.commit_log_id = LCPCL.commit_log_id
                         AND commit_log_ports.commit_log_id > latest_commits_ports_anchor()) AS LCPCLLCP JOIN ports
on LCPCLLCP.port_id = ports.id) AS LCPPORTS JOIN element
on LCPPORTS.element_id = element.id) AS PORTELEMENT JOIN categories
on PORTELEMENT.category_id = categories.id) AS PEC
order by commit_date_raw desc, category, port ";

	if ($Debug) {
		echo $sql;
		}

	$result = pg_exec ($db, $sql);
	if (!$result) {
		echo $sql . 'error = ' . pg_errormessage();
		exit;
	}
?>

	<UL>
<?
	$numrows = pg_numrows($result);
	for ($i = 0; $i < $numrows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		echo '	<LI><SMALL>';
		if ($date == 1) {
			echo '[' . $myrow['commit_date'] . '] ';
		}

		if ($time == 1) {
			echo '[' . $myrow['commit_time'];
		}

		if ($committer == 1) {
			if ($time != 1) {
				echo '[';
			} else {
				echo ' ';
			}

			echo $myrow['committer'];
		}

		if ($time == 1 || $committer == 1) {
			echo '] ';
		}

		echo '<A HREF="http://' . $ServerName . '/' . $myrow["category"] . '/' . $myrow["port"] . '/" TARGET="_content">';
		echo $myrow["category"] . '/' . $myrow["port"] . '</A>';
		echo '</SMALL></LI>';
		echo "\n";
	}
?>
	</UL>

<?
	echo freshports_ShowFooter();
?>

</body>
</html>
