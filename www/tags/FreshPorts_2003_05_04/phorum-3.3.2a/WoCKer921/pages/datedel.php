<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="action" value="datedel">
<input type="Hidden" name="num" value="<?php echo $num; ?>">
<input type="Hidden" name="frompage" value="datedel">
<center>
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
<td colspan=2 align="center" valign="middle" class="table-header">Date Delete: <?php echo $ForumName; ?></td>
</tr>
<tr>
<td valign="middle">Where Date Is: </td>
<td valign="middle"><select name="dateopt" class=big>
  <option value="=" <?php if($moderation=='=') echo 'selected'; ?>>Equal To</option>
  <option value="<" <?php if($moderation=='<') echo 'selected'; ?>>Older Than</option>
  <option value=">" <?php if($moderation=='>') echo 'selected'; ?>>Newer Than</option>
</select></td>
</tr>
<tr>
<td valign="middle">Date: (YYYY-MM-DD)</td>
<td valign="middle"><input type="Text" name="date" size="10" class="TEXT"></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Delete" class="BUTTON"></center>
</form>
