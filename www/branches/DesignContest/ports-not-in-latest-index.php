<?
	# $Id: ports-not-in-latest-index.php,v 1.2 2006-12-17 12:06:15 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

	freshports_Start('title',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
<?
$Debug=0;

#
# if no category provided or category is not numeric, try
# category zero.  inval returns zero if non-numeric
#
#echo $category         . "<br>";
#echo intval($category) . "<br>";

#
# append the category id to the cache_file
#
$cache_file .= "." . $category;

$title = "Ports not in latest /usr/ports/INDEX";
?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="0" ALIGN="center">

<?

$sql = "	SELECT categories.name || '/' || element.name  || '/' as port, ports.element_id, ports.id
			  FROM ports, element, categories 
			 WHERE found_in_index is FALSE
			   AND ports.element_id  = element.id
			   AND ports.category_id = categories.id
			   AND element.status    = 'A'
and EXISTS
 (select * from commit_log_ports where commit_log_ports.port_id = ports.id)
			 ORDER BY port";



if ($Debug) {
   echo "$sql\n";
   echo "GlobalHideLastChange = $GlobalHideLastChange\n";
}

$result = $result = pg_exec($db, $sql);
if ($result) {
	$NumRows = pg_numrows($result);
	echo "<tr><td><BIG>$NumRows ports found</BIG></td></tr>\n";
?>

<tr><td>
<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="10" ALIGN="center">
<?
	for ($i = 0; $i < $NumRows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		echo '<TR><TD WIDTH="*">';
		echo '<A HREF="' . $myrow["port"] . '">' . $myrow["port"] . '</A></td>';
		echo '<TD ALIGN="left">' . $myrow["element_id"] . '</TD>' . "\n";
		echo '<TD ALIGN="left">' . $myrow["id"]         . '</TD>' . "\n";
		echo '</TR>';
	}

} // end for
?>

</table>
</td>

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</tr>
</table>

<?
echo freshports_ShowFooter();
?>

</body>
</html>
