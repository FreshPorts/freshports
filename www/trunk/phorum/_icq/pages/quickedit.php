<? /* Quick Edit */ ?>
<form action="<? echo $myname; ?>" method="GET">
<input type="Hidden" name="page" value="edit">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<center>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Quick Edit: <?PHP echo $ForumName; ?></b></font></td>
</tr>
<tr>
<td align="center" valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Id: <input type="Text" name="id" size="10" class="TEXT"></font></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Edit" class="BUTTON"></center>
</form>
