<?php check_security(); ?>
<?php
  if(substr($new_forum_url, -1)=="/") $new_forum_url=substr($new_forum_url, 0, -1);
  $forum_url=$new_forum_url;
  $ext=$new_ext;
  $forum_page=$new_forum_page;
  $list_page=$new_list_page;
  $search_page=$new_search_page;
  $read_page=$new_read_page;
  $post_page=$new_post_page;
  $violation_page=$new_violation_page;
  $down_page=$new_down_page;
  $attach_page=$new_attach_page;
  writefile();
  QueMessage("The Files/Paths settings have been updated.");
?>