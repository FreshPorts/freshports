<?php  // Edit Message
    $sSQL="Select author, email, subject, body from $ForumTableName as t, $ForumTableName"."_bodies as b where t.id=b.id and t.id=$id";
    $q->query($DB, $sSQL);
    $mtext = $q->getrow();

    if(empty($mtext["subject"])){
        QueMessage("Message $id not found");
        return;
    }


    if (isset($srcpage)) {
        $page = $srcpage;
    } else {
        $page = "managemenu";
    }

    if(substr($mtext["body"], 0, 6)=="<HTML>"){
        $mtext["body"]=ereg_replace("</*HTML>", "", $mtext["body"]);
        $html=1;
    } else {
        $html=0;
    }
    if(substr($mtext["subject"], 0, 3)=="<b>"){
        $mtext["subject"]=ereg_replace("</*b>", "", $mtext["subject"]);
        $mtext["author"]=ereg_replace("</*b>", "", $mtext["author"]);
        $bold=1;
    } else {
        $bold=0;
    }


?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="edit">
<input type="Hidden" name="num" value="<?php echo $num; ?>">
<input type="Hidden" name="id" value="<?php echo $id; ?>">
<input type="Hidden" name="page" value="<?php echo $page; ?>">
<input type="Hidden" name="html" value="<?php echo $html; ?>">
<input type="Hidden" name="bold" value="<?php echo $bold; ?>">
<?php
if (isset($mythread)) { ?>
<input type="Hidden" name="mythread" value="<?php echo $mythread; ?>">
<?php
}
?>

<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
    <td colspan="2" align="center" class="table-header">Edit Message: <?php echo $ForumName; ?></td>
</tr>
<tr>
    <th>Author:</th>
    <td><input type="Text" name="author" value="<?php echo $mtext["author"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
    <th>Email:</th>
    <td><input type="Text" name="email" value="<?php echo $mtext["email"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<tr>
    <th>Subject:</th>
    <td><input type="Text" name="subject" value="<?php echo $mtext["subject"]; ?>" size="10" style="width: 300px;" class="TEXT"></td>
</tr>
<?php
if($PHORUM['AllowAttachments'] && $PHORUM['ForumAllowUploads'] == 'Y') {
  $SQL="Select id, filename from $ForumTableName"."_attachments where message_id=$id";
  $q->query($DB, $SQL);
  while($rec=$q->getrow()){
?>
<input type="hidden" NAME="attachments[<?php echo $rec["id"]; ?>]" value="<?php echo $rec["filename"]; ?>">
<tr>
  <th>Attachment [<?php echo $rec["id"]; ?>]:</th>
  <td><input type="Text" NAME="new_attachment[<?php echo $rec["id"]; ?>]" value="<?php echo $rec["filename"]; ?>" size="10" style="width: 300px;" class="TEXT">&nbsp;&nbsp;<INPUT TYPE="checkbox" NAME="del_attachment[<?php echo $rec["id"]; ?>]" VALUE="true"> delete attachment</td>
</tr>
<?php
     }
  }
?>
<tr>
    <td colspan=2><textarea name="body" cols="60" rows="20" wrap="VIRTUAL"><?php echo $mtext["body"]; ?></textarea></td>
</tr>
</td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Update" class="BUTTON"></center>
</form>
