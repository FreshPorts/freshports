<?
	# $Id: graphclick.php,v 1.1.2.2 2002-04-20 04:55:50 dan Exp $
	#

$cache_dir = "/tmp/";

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
	if (!isset($HTTP_REFERER) || $HTTP_REFERER=="") {
		header("Location: http://".$_SERVER['HTTP_HOST']);
	} else {
		header("Location: $HTTP_REFERER");
	}
	exit;
}

$URL = $map_p[$i-1];

header("Location: http://"
	.$_SERVER['HTTP_HOST']
	."$URL");
?>
