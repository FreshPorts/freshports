<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="setup">
<input type="Hidden" name="action" value="db">
<table border="1" cellspacing="0" cellpadding="3">
<tr>
  <td colspan="2" align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Database Settings</b></font></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Server Type:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><select name="new_dbType" class=big>
<?PHP
  $curr=current($dbsupport);
  while($curr){
    $key=key($dbsupport);
    echo "<option value=\"$key\"";
    if(isset($DB)){
      if($DB->type==$key) echo " selected";
    }
    echo ">$curr</option>\n";
    $curr=next($dbsupport);
  }
?>
</select></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Server Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_dbServer" value="<?PHP echo $dbServer; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Database Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_dbName" value="<?PHP echo $dbName; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">User Name:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_dbUser" value="<?PHP echo $dbUser; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
  <td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Password:</font></td>
  <td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="new_dbPass" value="<?PHP echo $dbPass; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
</table>
<br>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>