<?  // Edit Message
  $sSQL="Select author, email, subject, body from $ForumTableName as t, $ForumTableName"."_bodies as b where t.id=b.id and t.id=$id";
  $q->query($DB, $sSQL);
  list($author, $email, $subject, $body) = $q->getrow();
  if (isset($srcpage)) {
    $page = $srcpage;
  } else {
    $page = "managemenu";
  }
?>
<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="edit">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<input type="Hidden" name="id" value="<?PHP echo $id; ?>">
<input type="Hidden" name="page" value="<?PHP echo $page; ?>">
<?php
if (isset($mythread)) { ?>
<input type="Hidden" name="mythread" value="<?PHP echo $mythread; ?>">
<?php
}
?>

<table cellspacing="0" cellpadding="3" border="1">
<tr>
    <td colspan="2" align="center" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF">Edit Message: <? echo $ForumName; ?></font></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica">Author:</font></td>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica"><input type="Text" name="author" value="<?PHP echo $author; ?>" size="10" style="width: 300px;" class="TEXT"></font></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica">Email:</font></td>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica"><input type="Text" name="email" value="<?PHP echo $email; ?>" size="10" style="width: 300px;" class="TEXT"></font></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica">Subject:</font></td>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica"><input type="Text" name="subject" value="<?PHP echo $subject; ?>" size="10" style="width: 300px;" class="TEXT"></font></td>
</tr>
<tr>
    <td bgcolor="#FFFFFF" colspan=2><textarea name="body" cols="60" rows="20" wrap="VIRTUAL"><?PHP echo $body; ?></textarea></td>
</tr>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
