<?php check_security(); ?>
<?php
  if($ForumParent>0){
    $level="num=$ForumParent";
    $nav="<a href=\"$myname?page=manage&$level\">Up A Level</a> | <a href=\"$myname?page=manage\">Up To Top</a>";
  }
  else{
    $ForumParent=0;
  }

  if(empty($num)){
    $ForumName="Top Level";
    $nav='&nbsp;';
  }
?>
<center class="nav"><?php echo $nav; ?></center>
<table align="center" border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
    <td colspan="2" align="center" class="table-header"><?php echo $ForumName; ?></td>
</tr>
<?php
  $sSQL="Select * from ".$pho_main." where parent=$num order by name";
  $q->query($DB, $sSQL);
  $rec=(object)$q->getrow();
  if($q->numrows()==0){
?>
<tr>
    <td colspan="2" align="center">No Forums Defined.</td>
</tr>
<?php
  }
  While(isset($rec->id)){
    if($rec->folder){
      $text="Folder";
    }
    else{
      $text="Forum";
    }
?>
<tr>
    <td><a href="<?php echo "$myname?page=managemenu&num=$rec->id"; ?>"><?php echo $rec->name; ?></a><?php if($rec->folder) { ?> - <a href="<?php echo "$myname?page=manage&num=$rec->id"; ?>">Browse</a><?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><?php echo $text; ?></td>
</tr>
<?php
    $rec=(object)$q->getrow();
  }
?></td>
</tr>
</table>
