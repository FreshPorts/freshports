<?
	# $Id: missing.php,v 1.1.2.1 2001-12-23 02:53:30 dan Exp $
	#
	# Copyright (c) 2001 DVL Software Limited

	require("./include/common.php");
	require("./include/freshports.php");
	require("./include/databaselogin.php");

	require("./include/getvalues.php");

	freshports_Start("the place for ports",
				"$FreshPortsName - new ports, applications",
				"FreeBSD, index, applications, ports");

	require("../classes/elements.php");
	require("../classes/ports.php");
?>

<?

function freshports_CategoryId($category, $database) {
	$sql = "select * from categories where name = '$category'";
	$result = pg_exec($database, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows == 1) {
			$myrow = pg_fetch_array ($result, 0);
			$CategoryID = $myrow["id"];
		}
	} else {
		echo 'pg_exec failed: ' . $sql;
	}

	return $CategoryID;
}

function freshports_PortId($category, $port, $database) {
/*	$sql = "$sql = "select ports.id \
              from ports, categories, element \
             where ports.element_id  = $this->{element_id} \
               and ports.category_id = categories.id \
               and ports.element_id  = element.id"; */
	$result = pg_exec($database, $sql);
	if ($result) {
		$numrows = pg_numrows($result);
		if ($numrows == 1) {
			$myrow = pg_fetch_array ($result, 0);
			$CategoryID = $myrow["id"];
		}
	} else {
		echo 'pg_exec failed: ' . $sql;
	}

	return $CategoryID;
}





	echo "you asked for $REQUEST_URI<BR>";
	$url_Array = explode('/', $REQUEST_URI);
	if (array_count_values($url_Array) >= 1) {
		$CategoryName = $url_Array[1];
		if (array_count_values($url_Array) >= 2) {
			$PortName = $url_Array[2];
		}

		echo "\$CategoryName = '$CategoryName'<BR>";
		echo "\$PortName     = '$PortName'<BR>";


		$CategoryID = freshports_CategoryId($CategoryName, $db);

		echo "\$CategoryName = '$CategoryName' ($CategoryID)<BR>";
		echo "\$PortName     = '$PortName'<BR>";

		if (IsSet($PortName)) {
			$element = new Element($db);
			$element->FetchByName("/ports/$CategoryName/$PortName");

			if (IsSet($element->id)) {
				$port = new Port($db);
				$port->FetchByPartialName("/ports/$CategoryName/$PortName");

			}
		}


		if (IsSet($CategoryID)) {
			echo "<A HREF=\"/category.php3?category=$CategoryID\">this link</A> should take you to the category details<BR>";
			if (IsSet($port->id)) {
				echo "This is where you'd see details for port = '$port->id'<BR>";
				echo "<A HREF=\"/port-description.php3?port=$port->id\">this link</A> should take you to the port details<BR>";
			} else {
				if (IsSet($PortName)) {
					echo "no port found like that in this category";
				}
			}
		} else {
			echo "no category '$CategoryName' found";
		}
	}

#	phpinfo();
?>

<? include("./include/footer.php") ?>
</body>
</html>
