<?php
	define('VUXMLURL', 'http://www.vuxml.org/freebsd/');

	if (IsSet($_REQUEST['vid'])) {
		$vid = $_REQUEST['vid'];

		$vidArray = explode('|', $vid);

		if (count($vidArray) == 1) {
			header('Location: ' . VUXMLURL . $_REQUEST['vid'] . '.html');
		} 
	}
?>

<html>
<body>
<p>
Hi.  Thanks for checking, but this part of the FreshPorts - VuXML system is still
being designed.
<p>
These are the vulnerabilities:

<ul>
<?php

	while (list($key, $value) = each($vidArray)) {
		$URL = VUXMLURL . $value . '.html';
		echo '<li><a href="' . $URL . '">' . $URL . '</a>' . "\n";
	}

?>

</ul>

</body>
</html>