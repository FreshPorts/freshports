<?php
	#
	#
	# $Id: graph.php,v 1.2 2006-12-17 12:06:25 dan Exp $
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	// parameters: graph id
	require_once('bar-graphs.php');

if (!function_exists('imagettfbbox'))
	die("Fatal error: this version of PHP does not support GD.\n");

// --------------------------------------------------------------------
// FreshPorts bar chart

// $values must be an array of numbers
function FreshPortsChart($title, $axislabel, $values, $labels, $urls, $file = "-") {
		$c = new dg_BarGraph();
		$c->width      = 500;
		$c->values     = $values;
		$c->labels     = $labels;
		$c->urls       = $urls;
		$c->title      = $title;
		$c->axis_label = $axislabel;
		$c->gradient1  = array(180,0,0); // from dark red
		$c->gradient2  = array(255,255,0); // to bright yellow

		$c->footer = "(c) http://www.FreshPorts.org/                               " . date("Y-m-d G:i:s");
		return $c->show($file);
}


// parameters:
// id=number of graph

$id = pg_escape_string($_GET["id"]);

// assume that we always have graph of id=0
if (!isset($id)) $id=0;

$fid = "FreshPorts.graph$id";
$cache_dir = "/tmp/";
$period = 14400; // in seconds

$filename = $cache_dir.$fid.".png";
if (!file_exists($filename) || filemtime($filename)+$period<time())	{
	// get graph information

	GLOBAL $db;

	// XXX CHANGE THE QUERY XXX
	$data = @pg_exec($db, "select query, title, label, is_clickable from graphs where id = $id")
		or die("PGERR 1: " . pg_ErrorMessage());
	
	if (pg_numrows($data) == 0)
		die("GRAPH: invalid id");

	$r = pg_fetch_row($data, $i);

	$query        = $r[0];
	$title        = $r[1];
	$axislabel    = $r[2];
	$is_clickable = $r[3];

	pg_freeresult($data);

	// get graph data
	$data = @pg_exec($db, $query)
		or die("PGERR 2: " . pg_ErrorMessage());

	$v = array();
	$l = array();
	$u = array();

	for ($i=0; $i<pg_numrows($data); $i++) {
		$r = pg_fetch_row($data, $i);
		array_push($v, $r[1]);
		array_push($l, $r[0]."  ");
		array_push($u, $r[2]);
	}
	
	pg_freeresult($data);
	
	// draw
	$map = FreshPortsChart($title, $axislabel, $v, $l, $u, $filename);

	if ($is_clickable == 't') {
		// save map
		$fp = fopen($cache_dir.$fid.".map","w");
		fputs($fp,$map);
		fclose($fp);
	}
	
	pg_close();
}


header("Content-type: image/png");
readfile($filename);


//  CREATE TABLE "graph" ("id" integer NOT NULL, "query" text NOT NULL,
//  "title" text NOT NULL);

// insert into graph (id, query, title) values (0,'select category,
// name, count(watch_list_element.element_id) from ports_active,
// watch_list_element where ports_active.element_id =
// watch_list_element.element_id group by category, name order by 3 desc
// limit 20','Most Watched Ports');


?>
