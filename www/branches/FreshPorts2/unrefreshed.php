<?
	# $Id: unrefreshed.php,v 1.1.2.1 2003-02-25 15:25:15 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

   require_once($_SERVER['DOCUMENT_ROOT'] . '/include/common.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/include/freshports.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/include/databaselogin.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports-unrefreshed.php');

#$Debug=1;

   freshports_Start('Unrefreshed ports',
               'freshports - new ports, applications',
               'FreeBSD, index, applications, ports');

#phpinfo();


function freshports_IfNull($Value1, $Value2='&nbsp') {
	if (IsSet($Value1)) {
		$result = $Value1;
	} else {
		$result = $Value2;
	}
	
	return $result;

}
               
function freshports_DisplayUnrefreshedPorts($dbh) {
	$Ports = new PortsUnrefreshed($dbh);

	$NumRows = $Ports->FetchAll();

	if ($NumRows > 0) {

?>
<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
<tr>
<td><b>Port ID</b></td>
<td><b>Port Name</b></td>
<td><b>Category</b></td>
<td><b>Category ID</b></td>
<td><b>Commit Log ID</b></td>
<td><b>Monitor</b></td>
<td><b>Ignore</b></td>
<td><b>Reset</b></td>
<td><b>Reason</b></td>
<td><b>Date Ignored</b></td>
<?php
		
		for ($i = 0; $i < $NumRows; $i++) {
			$Ports->FetchNth($i);
			echo "<tr>\n";

			$ID = $Ports->commit_log_id . '_' . $Ports->port_id;

?>
<td>
<INPUT NAME="port_id[]"        TYPE="hidden" value="<?php echo $Ports->port_id;       ?>">
<INPUT NAME="commit_log_id[]"  TYPE="hidden" value="<?php echo $Ports->commit_log_id; ?>"><?php
			echo $Ports->port_id . '</td>' . "\n";
			echo '<td valign="top"><a href="/' . $Ports->category_name . '/' . $Ports->port_name . '/">' . $Ports->port_name  . '</a></td>' . "\n";
			echo '<td valign="top">'           . $Ports->category_id   . '</td>';
			echo '<td valign="top"><a href="/' . $Ports->category_name . '/">' . $Ports->category_name  . '</a></td>' . "\n";
			echo '<td  valign="top"nowrap>'    . $Ports->commit_log_id . ' '. freshports_Commit_Link($Ports->message_id) . ' ' . freshports_Email_Link($Ports->message_id) . '</td>' . "\n";
?>
<td valign="top"><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Monitor"<?php if (!IsSet($Ports->date_ignored)) echo " CHECKED"; ?>></td>
<td valign="top"><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Ignore" <?php if ( IsSet($Ports->date_ignored)) echo " CHECKED"; ?>></td>
<td valign="top"><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Reset"></td>
<td valign="top"><INPUT NAME="reason[<?php echo $ID;?>]" TYPE="text" SIZE="18" VALUE="<?php echo $Ports->reason; ?>"></td>
<td valign="top" nowrap><INPUT NAME="date[<?php   echo $ID;?>]" TYPE="hidden" VALUE="<?php echo freshports_IfNull($Ports->date_ignored); ?>"><?php echo freshports_IfNull($Ports->date_ignored); ?></td>
</tr>
<?php
		}
?>
</TABLE>
<BR>
<DIV ALIGN="CENTER">
<INPUT TYPE="submit" VALUE="Update">
</DIV>
</FORM>
<?php
	} else {
		echo '<h4>All ports are refreshed</h4>';
	}
}

if (IsSet($_POST['port_id'])) {
	#
	# OK, time to update things!
	#
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports.php');

	$CommitLogPort = new Commit_Log_Ports($db);
	while (list($key, $port_id) = each($_POST['port_id'])) {
		$commit_log_id = $_POST['commit_log_id'][$key];
		echo "\$key='$key' :: \$port_id=$port_id";
		echo " :: commit_log_id='$commit_log_id'";

		$ID = $commit_log_id . '_' . $port_id;
		echo " :: \$ID='$ID'";
		echo " :: action='"        . $_POST['action'][$ID] . "'";
		echo " :: reason='"        . $_POST['reason'][$ID] . "'";
		echo " :: date  ='"        . $_POST['date']  [$ID] . "'";
		echo "<br>\n";
	}
	
}

?>
<TABLE WIDTH="<? echo $TableWidth; ?>" BORDER="0" ALIGN="center">
<TR><td VALIGN=TOP>
<TABLE WIDTH="100%" ALIGN="left" border="0">
<TR>
	<? echo freshports_PageBannerText("Unrefreshed ports"); ?>
</TR>
<tr><td>
This is a list of ports in our database which need to be refreshed.
Ports in our datbase which need refreshing do not match what's in the ports tree.  The most common reason
for this situation is a port error.

<?php freshports_DisplayUnrefreshedPorts($db); ?>

</td></tr>
</table>
</td>

	<?
	freshports_SideBar();
	?>

</TABLE> 

<?
freshports_ShowFooter();
?>

</body>
</html>
