<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="action" value="datedel">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<input type="Hidden" name="frompage" value="datedel">
<center>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td colspan=2 align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Date Delete: <?PHP echo $ForumName; ?></b></font></td>
</tr>
<tr>
<td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Where Date Is: </font></td>
<td valign="middle" bgcolor="#FFFFFF"><select name="dateopt" class=big>
  <option value="=" <? if($moderation=='=') echo 'selected'; ?>>Equal To</option>
  <option value="<" <? if($moderation=='<') echo 'selected'; ?>>Less Than</option>
  <option value=">" <? if($moderation=='>') echo 'selected'; ?>>Greater Than</option>
</select></td>
</tr>
<tr>
<td valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">Date: </font></td>
<td valign="middle" bgcolor="#FFFFFF"><input type="Text" name="date" size="10" class="TEXT"></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Delete" class="BUTTON"></center>
</form>
