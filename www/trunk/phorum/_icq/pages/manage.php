<?
  if(isset($ForumParent)){
    $level="num=$ForumParent";
    $nav="<a href=\"$myname?page=manage&$level\">Up A Level</a> | <a href=\"$myname?page=manage\">Up To Top</a>";
  }    
  else{
    $ForumParent=0;
    $ForumName="Top Level";
    $nav='&nbsp;';
    $num=0;
  }
?>
<center><?PHP echo $nav; ?></center>
<table cellspacing="0" cellpadding="3" border="1">
<tr>
    <td colspan="2" align="center" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><? echo $ForumName; ?></font></td>
</tr>
<?PHP
  $sSQL="Select * from forums where parent=$num order by name";
  $q->query($DB, $sSQL);
  $rec=(object)$q->getrow();
  if($q->numrows()==0){
?>
<tr>
    <td colspan="2" align="center" bgcolor="#FFFFFF"><font face="Arial,Helvetica">No Forums Defined.</font></td>
</tr>
<?PHP
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
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica"><a href="<? echo "$myname?page=managemenu&num=$rec->id"; ?>"><? echo $rec->name; ?></a><? if($rec->folder) { ?> - <a href="<? echo "$myname?page=manage&num=$rec->id"; ?>">Browse</a><? } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></td>
    <td bgcolor="#FFFFFF"><font face="Arial,Helvetica"><? echo $text; ?></font></td>
</tr>
<?PHP    
    $rec=(object)$q->getrow();
  }
?></td>
</tr>
</table>
