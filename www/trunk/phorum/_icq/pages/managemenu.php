<? /* Forum/Folder Menu */ ?>
<script language="JavaScript" type="text/javascript">
  function dropforum(url, folder){
    if(folder){
      ans=window.confirm("You are about to drop this folder.  All sub folders and sub forums of this folder will be dropped also.  Do you want to continue?");
    }
    else{
      ans=window.confirm("You are about to drop this forum.  Do you want to continue?");
    }
    if(ans){
      window.location.replace(url);
    }
  }
</script>
<table cellspacing="0" cellpadding="3" border="1">
<tr>
    <td align="center" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><? echo $ForumName; ?></font></td>
</tr>
<?
  if($ForumFolder==1){
    $uword="Folder";
    $lword="folder";
  }
  else{
    $uword="Forum";
    $lword="forum";
  }
?>
<tr>
<td bgcolor="#FFFFFF">
<font face="Arial,Helvetica">
<?PHP if($ForumFolder!="1"){ ?>
<a href="<? echo $myname; ?>?page=easyadmin&num=<?PHP echo $num; ?>">Easy Admin</a><br>
<a href="<?PHP echo $myname; ?>?page=quickedit&num=<?PHP echo $num; ?>">Quick Edit</a><br>
<a href="<?PHP echo $myname; ?>?page=quickdel&num=<?PHP echo $num; ?>">Quick Delete</a><br>
<a href="<? echo $myname; ?>?page=quickapp&num=<?PHP echo $num; ?>">Quick Approve</a><br>
<a href="<?PHP echo $myname; ?>?page=datedel&num=<?PHP echo $num; ?>">Delete By Date</a><br>
<a href="<?PHP echo $myname; ?>?action=seq&page=managemenu&num=<?PHP echo $num; ?>">Reset Sequence</a><br>
<!-- <a href="<?PHP echo $myname; ?>?page=advdel&num=<?PHP echo $num; ?>">Advanced Delete</a><br> -->
<?PHP } ?>
<a href="<?PHP echo $myname; ?>?page=props&num=<?PHP echo $num; ?>">Edit Properties</a><br>
<?PHP if($ForumActive){ ?>
<a href="<?PHP echo $myname; ?>?action=deactivate&page=managemenu&num=<?PHP echo $num; ?>">Deactivate</a><br>
<?PHP }else{ ?>
<a href="<?PHP echo $myname; ?>?action=activate&page=managemenu&num=<?PHP echo $num; ?>">Activate</a><br>
<?PHP } ?>
<?PHP if($ForumFolder!="1"){ ?>
<a href="javascript:dropforum('<?PHP echo $myname; ?>?action=drop&page=main&num=<?PHP echo $num; ?>', 0);">Drop This <?PHP echo $uword; ?></a><br>
<?PHP }else{ ?>
<a href="javascript:dropforum('<?PHP echo $myname; ?>?action=drop&page=main&num=<?PHP echo $num; ?>', 1);">Drop This <?PHP echo $uword; ?></a><br>
<?PHP } ?>
</font>
</td>
</tr>
</table>
