<?
	# $Id: ports-not-in-latest-index.php,v 1.1.2.10 2002-12-11 04:44:40 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/getvalues.php');

	freshports_Start('title',
					'freshports - new ports, applications',
					'FreeBSD, index, applications, ports');

?>
<html>

<head>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  

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
<tr><td VALIGN="top">

<?

$DESC_URL = "ftp://ftp.freebsd.org/pub/FreeBSD/branches/-current/ports";

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
	echo "<BIG>$NumRows ports found</BIG><BR>\n";
?>

<TABLE WIDTH="<? echo $TableWidth ?>" BORDER="10" ALIGN="center">
<tr><td VALIGN="top">

<?

	for ($i = 0; $i < $NumRows; $i++) {
		$myrow = pg_fetch_array ($result, $i);
		echo '<TR><TD WIDTH="*">';
		echo '<A HREF="' . $myrow["port"] . '">' . $myrow["port"] . '</A>';
		echo '</TD><TD ALIGN="left">' . $myrow["element_id"] . '</TD>' . "\n";
		echo '</TD><TD ALIGN="left">' . $myrow["id"]         . '</TD>' . "\n";
		echo '</TR>';
	}

	echo '</TABLE>';
} // end for

</script>
</td>
 <TD VALIGN="top" WIDTH="*" ALIGN="center"> <td valign="top" width="*">
   <? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/side-bars.php') ?>
 </td>
</tr>
</table>
</td>
</tr>
</table>

<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><TD>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/include/footer.php') ?>
</TD></TR>
</TABLE>

</body>
</html>
