<?php check_security(); ?>
<?php /* Quick Edit */ ?>
<form action="<?php echo $myname; ?>" method="GET">
<input type="Hidden" name="page" value="edit">
<input type="Hidden" name="num" value="<?php echo $num; ?>">
<center>
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
<td align="center" valign="middle" class="table-header">Quick Edit: <?php echo $ForumName; ?></td>
</tr>
<tr>
<td align="center" valign="middle">Id: <input type="Text" name="id" size="10" class="TEXT"></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Edit" class="BUTTON"></center>
</form>
