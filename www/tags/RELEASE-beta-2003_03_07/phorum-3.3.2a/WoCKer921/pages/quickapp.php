<?php check_security(); ?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="managemenu">
<input type="Hidden" name="action" value="moderate">
<input type="Hidden" name="num" value="<?php echo $num; ?>">
<center>
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
    <td align="center" valign="middle" class="table-header">Quick Approve: <?php echo $ForumName; ?></td>
</tr>
<tr>
    <td align="center" valign="middle">Approve <INPUT CHECKED TYPE="RADIO" NAME="approved" VALUE="N"> &nbsp; | &nbsp; Disable <INPUT TYPE="RADIO" NAME="approved" VALUE="Y"></td>
</tr>
    <td align="center" valign="middle">Ids/Threads: <input type="Text" name="id" size="10" class="TEXT"></td>
</tr>
</table>
<p><center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
