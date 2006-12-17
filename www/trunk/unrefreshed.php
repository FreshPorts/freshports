<?php
	#
	# $Id: unrefreshed.php,v 1.2 2006-12-17 12:06:17 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
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
	GLOBAL $User;
	$Ports = new PortsUnrefreshed($dbh);

	$NumRows = $Ports->FetchAll();

	if ($NumRows > 0) {
		if ($User->type == SUPER_USER) {
?>
<FORM ACTION="<? echo $_SERVER["PHP_SELF"]; ?>" method="POST">
<?php
		}
?>
<TABLE WIDTH="100%" border="1" CELLSPACING="0" CELLPADDING="8">
<tr>
<td valign="top" align="center"><b>Port ID</b></td>
<td valign="top" align="center"><b>Port Name</b></td>
<td valign="top" align="center"><b>Category</b></td>
<td valign="top" align="center"><b>Category ID</b></td>
<td valign="top" align="center"><b>Commit Log ID</b></td>
<?php
		if ($User->type == SUPER_USER) {
?>
<td valign="top" align="center"><b>Monitor</b></td>
<td valign="top" align="center"><b>Ignore</b></td>
<td valign="top" align="center"><b>Mark as Refreshed</b></td>
<td valign="top" align="center"><b>Reason</b></td>
<td valign="top" align="center"><b>Date Ignored</b></td>
<?php
		}
		
		for ($i = 0; $i < $NumRows; $i++) {
			$Ports->FetchNth($i);
			echo "<tr>\n";

			$ID = $Ports->commit_log_id . '_' . $Ports->port_id;

?>
<td  valign="top">
<INPUT NAME="port_id[]"        TYPE="hidden" value="<?php echo $Ports->port_id;       ?>">
<INPUT NAME="commit_log_id[]"  TYPE="hidden" value="<?php echo $Ports->commit_log_id; ?>"><?php
			echo $Ports->port_id . '</td>' . "\n";
			echo '<td valign="top" nowrap><a href="/' . $Ports->category_name . '/' . $Ports->port_name . '/">' . $Ports->port_name  . '</a></td>' . "\n";
			echo '<td valign="top">'                  . $Ports->category_id   . '</td>';
			echo '<td valign="top"><a href="/' . $Ports->category_name . '/">' . $Ports->category_name  . '</a></td>' . "\n";
			echo '<td  valign="top"nowrap>'    . $Ports->commit_log_id . ' '. freshports_Commit_Link($Ports->message_id) . ' ' . freshports_Email_Link($Ports->message_id) . '</td>' . "\n";
			if ($User->type == SUPER_USER) {
?>
<td valign="top" align="center" ><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Monitor"<?php if (!IsSet($Ports->date_ignored)) echo " CHECKED"; ?>></td>
<td valign="top" align="center" ><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Ignore" <?php if ( IsSet($Ports->date_ignored)) echo " CHECKED"; ?>></td>
<td valign="top" align="center" ><INPUT NAME="action[<?php echo $ID;?>]" TYPE="RADIO" VALUE="Reset"></td>
<td valign="top"><INPUT NAME="reason[<?php echo $ID;?>]" TYPE="text" SIZE="18" VALUE="<?php echo $Ports->reason; ?>"></td>
<td valign="top" nowrap><INPUT NAME="date[<?php   echo $ID;?>]" TYPE="hidden"  VALUE="<?php echo $Ports->date_ignored; ?>"><?php echo freshports_IfNull($Ports->date_ignored); ?></td>
</tr>
<?php
			}
		}
?>
</TABLE>
<BR>
<?php
		if ($User->type == SUPER_USER) {
?>
<DIV ALIGN="CENTER">
<INPUT TYPE="submit" VALUE="Update">
</DIV>
</FORM>
<?php
		}
	} else {
		echo '<h4>All ports are refreshed</h4>';
	}
}

if (IsSet($_POST['port_id']) && $User->type == SUPER_USER ) {
	#
	# OK, time to update things!
	#
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports.php');
   require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/commit_log_ports_ignore.php');
   
  	pg_exec ($db, 'BEGIN');
  	$CommitOrRollback = 'COMMIT';


	$CommitLogPort       = new Commit_Log_Ports       ($db);
	$CommitLogPortIgnore = new Commit_Log_Ports_Ignore($db);
	
	while (list($key, $port_id) = each($_POST['port_id'])) {
		$commit_log_id = $_POST['commit_log_id'][$key];

		$ID = $commit_log_id . '_' . $port_id;
		
		$action        = $_POST['action'][$ID];
		$reason        = $_POST['reason'][$ID];
		$date          = $_POST['date'][$ID];

/*
		echo "\$key='$key' :: \$port_id=$port_id";
		echo " :: commit_log_id='$commit_log_id'";

		echo " :: \$ID='$ID'";
		echo " :: action='"        . $action . "'";
		echo " :: reason='"        . $reason . "'";
		echo " :: date  ='"        . $date   . "'";
		echo "<br>\n";
*/

		$CommitLogPort->CommitLogIDSet      ($commit_log_id);
		$CommitLogPort->PortIDSet           ($port_id);

		$CommitLogPortIgnore->CommitLogIDSet($commit_log_id);
		$CommitLogPortIgnore->PortIDSet     ($port_id);

		switch ($action) {
			case 'Monitor':
				# remove the entry from commit_log_port_ignore if one exists
				if (IsSet($date) && chr($date) != 0) {
					$numrows = $CommitLogPortIgnore->delete();
				}
				break;

			case 'Ignore':
				if (!IsSet($reason) || chr($reason) == 0) {
					$reason = 'none given';
				}
				$CommitLogPortIgnore->ReasonSet($reason);
				$numrows = $CommitLogPortIgnore->insert();

				break;

			case 'Reset':
				$numrows = $CommitLogPort->NeedsRefreshClear();
				break;

			default:
				die("unknown action found: '$action'");
		}
		
		if ($numrows != 1) {
			$CommitOrRollback = 'ROLLBACK';
		}
	}
	pg_exec ($db, $CommitOrRollback);
}

?>
	<?php echo freshports_MainTable(); ?>

	<tr><td valign="top" width="100%">

	<?php echo freshports_MainContentTable(); ?>
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

  <TD VALIGN="top" WIDTH="*" ALIGN="center">
	<?
	echo freshports_SideBar();
	?>
  </td>

</TABLE> 

<?
echo freshports_ShowFooter();
?>

</body>
</html>
