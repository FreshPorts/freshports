<?php

/* Forum/Folder Menu */

function mmlink($text, $link)
{
    global $PHP_SELF, $f;
    echo "<a href=\"$link\">$text</a><br>\n";
}

$Title=GetForumPath($num);

?>
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
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
    <td align="center" class="table-header"><?php echo $Title; ?></td>
</tr>
<?php
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
<td>
<?php
    if($ForumFolder!="1"){
        mmlink("Easy Admin", "$PHP_SELF?f=$f&page=easyadmin");
        mmlink("Unapproved Messages", "$PHP_SELF?f=$f&page=recentadmin");
    }

    if(!empty($PHORUM["admin_user"]["forums"][0])){
        if($ForumFolder!="1"){
            mmlink("Quick Edit", "$PHP_SELF?f=$f&page=quickedit");
            mmlink("Quick Delete", "$PHP_SELF?f=$f&page=quickdel");
            mmlink("Quick Approve", "$PHP_SELF?f=$f&page=quickapp");
            mmlink("Delete By Date", "$PHP_SELF?f=$f&page=datedel");
            mmlink("Reset Sequence", "$PHP_SELF?f=$f&action=seq&page=managemenu");
        }
        if($ForumActive){
            mmlink("Hide", "$PHP_SELF?f=$f&action=deactivate&page=managemenu");
        } else {
            mmlink("Make Visible", "$PHP_SELF?f=$f&action=activate&page=managemenu");
        }
        mmlink("Edit Properties", "$PHP_SELF?f=$f&page=props");
        mmlink("Drop This $uword", "javascript:dropforum('$PHP_SELF?action=drop&page=main&f=$f', $ForumFolder);");
    }
?>

</td>
</tr>
</table>
