<?
	# $Id: graphclick.php,v 1.1.2.3 2002-05-19 21:59:03 dan Exp $
	#

	$cache_dir = "/tmp/";


	$id      = $_GET["id"];
	$graph_x = $_GET["graph_x"];
	$graph_y = $_GET["graph_y"];

	if (!isset($id)) $id=0;

	$map = file($cache_dir."FreshPorts.graph".$id.".map");
	if (count($map) == 0) {
		die("GRAPH: invalid id");
	}

	foreach ($map as $m) {
		list($y,$p) = split(":",$m);
		$map_y[] = $y;
		$map_p[] = $p;
	}

	$i = 0;
	while ($i < count($map) && $graph_y>$map_y[$i]) {
		$i++;
	}

	// click out of bars (too high or too low)
	if ($i==0 || $i==count($map)) {
		if (!isset($_SERVER["HTTP_REFERER"]) || $_SERVER["HTTP_REFERER"] == '') {
			header("Location: http://" . $_SERVER['HTTP_HOST']);
		} else {
			header("Location: . $_SERVER["HTTP_REFERER"]);
		}
		exit;
	}

	$URL = $map_p[$i-1];

	header("Location: http://" . $_SERVER['HTTP_HOST'] . "$URL");
?>
