<?php check_security(); ?>
<?php
  if($name!=""){
      if(!get_magic_quotes_gpc()){
        $name = addslashes($name);
        $description = addslashes($description);
      }
      if(!$ForumFolder) {
        if(isset($att_types)){
          $upload_types=strtolower(implode(";", $att_types));
        }
        $upload_size=(int)$upload_size;
        $max_uploads=(int)$max_uploads;
        // please keep this formatted like this
        $sSQL="Update ".$pho_main." set
                    name='$name',
                    description='$description',
                    config_suffix='$config_suffix',
                    parent=$parent,
                    display=$display,
                    moderation='$moderation',
                    email_list='$email_list',
                    email_return='$email_return',
                    email_tag='$email_tag',
                    check_dup=$check_dup,
                    multi_level=$multi_level,
                    collapse=$collapsed,
                    flat=$rflat,
                    lang='$language_file',
                    html='$allow_html',
                    table_width='$table_width',
                    table_header_color='$table_header_color',
                    table_header_font_color='$table_header_font_color',
                    table_body_color_1='$table_body_color_1',
                    table_body_color_2='$table_body_color_2',
                    table_body_font_color_1='$table_body_font_color_1',
                    table_body_font_color_2='$table_body_font_color_2',
                    nav_color='$nav_color',
                    nav_font_color='$nav_font_color',
                    allow_uploads='$allow_uploads',
                    upload_types='$upload_types',
                    upload_size='$upload_size',
                    max_uploads='$max_uploads',
                    security='$security',
                    showip='$showip',
                    emailnotification=$emailnotification,
                    body_color='$body_color',
                    body_link_color='$body_link_color',
                    body_alink_color='$body_alink_color',
                    body_vlink_color='$body_vlink_color'
               where
                 id=$num";
      } else {
        $sSQL="Update ".$pho_main." set name='$name', description='$description', config_suffix='$config_suffix', lang='$language_file', parent=$parent, table_width='$table_width', table_header_color='$table_header_color', table_header_font_color='$table_header_font_color', table_body_color_1='$table_body_color_1', table_body_font_color_1='$table_body_font_color_1', nav_color='$nav_color', nav_font_color='$nav_font_color', body_color='$body_color', body_link_color='$body_link_color', body_alink_color='$body_alink_color', body_vlink_color='$body_vlink_color' where id=$num";
      }

      $q->query($DB, $sSQL);
      $err=$q->error();
      if($err==""){
        if (!$folder && $AllowAttachments && $allow_uploads == 'Y') {
          if(!file_exists("$AttachmentDir/$table") && !@mkdir("$AttachmentDir/$table", 0777)){
            QueMessage("The directory ($AttachmentDir/$table) for attachments could not be created.");
          } else {
            chmod("$AttachmentDir/$table", 0777);
          }
          if($err=create_table($DB, "attachments", $table."_attachments")){
            QueMessage("Could not create attachments table.  Database server said \"$err\"");
          }
        }
        $ForumName=stripslashes($name);
        writefile($num);
        include "$PHORUM[settings_dir]/$num.php";
        QueMessage("$ForumName has been updated.");
      }
      else{
        QueMessage($err);
        $page=$frompage;
      }

  }
  else{
    QueMessage("You must provide a name for the forum.");
    $option=="edit_prop";
  }
?>
