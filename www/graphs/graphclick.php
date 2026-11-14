<?php
	#
	# $Id: graphclick.php,v 1.2 2006-12-17 12:06:25 dan Exp $
	#

	$cache_dir = "/tmp/";

	# The graph on graphs.php is an <input type="image">, so a click sends the
	# graph's id and graph_x / graph_y.  graph.php wrote the map, one
	# "y:url" line per bar, when it drew the graph.
	$id      = is_string($_GET['id']      ?? null) ? intval($_GET['id'])      : 0;
	$graph_y = is_string($_GET['graph_y'] ?? null) ? intval($_GET['graph_y']) : 0;

	# Anything which cannot be resolved to a bar goes back to that graph.
	# (This used $HTTP_REFERER, a register_globals variable which no longer
	# exists, so it sent an empty Location; and a missing map file was a
	# TypeError in count().)
	$back = 'https://' . $_SERVER['HTTP_HOST'] . '/graphs.php?id=' . $id;

	$map = $id > 0 ? @file($cache_dir . "FreshPorts.graph" . $id . ".map", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
	if (empty($map)) {
		header("Location: $back");
		exit;
	}

	$map_y = array();
	$map_p = array();
	foreach ($map as $m) {
		list($y, $p) = array_pad(explode(':', $m, 2), 2, '');
		$map_y[] = intval($y);
		$map_p[] = $p;
	}

	$i = 0;
	while ($i < count($map_y) && $graph_y > $map_y[$i]) {
		$i++;
	}

	// click out of bars (too high or too low)
	if ($i == 0 || $i == count($map_y)) {
		header("Location: $back");
		exit;
	}

	$URL = $map_p[$i - 1];

	header("Location: https://" . $_SERVER['HTTP_HOST'] . "$URL");
?>