<? /* Main Menu */ ?>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Menu</b></td>
</tr>
<tr>
<td align="left" valign="middle" bgcolor="#FFFFFF">
<font face="Arial,Helvetica">
<a href="<? echo $myname; ?>?page=newforum">New Forum</a><br>
<a href="<? echo $myname; ?>?page=newfolder">New Folder</a><br>
<a href="<? echo $myname; ?>?page=manage">Manage Forums/Folders</a><br>
<a href="<? echo $myname; ?>?page=setup">Phorum Setup</a><br>
<a href="<? echo $myname; ?>?page=pass">Change Password</a><br>
<a href="<? echo $myname; ?>?action=build">Rebuild INF File</a><br>
<?PHP
  if($down==1){
    ?><a href="<? echo $myname; ?>?action=start">Start Phorum</a><br>
    <?PHP
  }
  else{
    ?><a href="<? echo $myname; ?>?action=stop">Stop Phorum</a><br>
    <?PHP
  }
?>
</td>
</tr>
</table>
