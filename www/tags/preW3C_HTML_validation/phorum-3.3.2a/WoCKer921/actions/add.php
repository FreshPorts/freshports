<?php check_security(); ?>
<?php
  if(get_magic_quotes_gpc()){
    $name = stripslashes($name);
    $description = stripslashes($description);
  }

  $name = trim($name);
  $table = trim($table);

  if(empty($name)){
    $err = "You must provide a name for the forum.";
  }
  elseif(!$folder && !eregi("^[a-z]+[^0-9a-z_]*", $table)){
    $err = "The table name can contain only letters, numbers and _.  It must begin with a letter.";
  }
  elseif (!$folder && !$table_exists) {
    if($err=create_table($DB, "main", $table)){
      $err = "Could not create table $table.  Database server said \"$err\"";
    }
  }

  if ($err=="") {
    $id=$DB->nextid($pho_main);
    if($id==0 && $DB->type!="mysql"){
      // drop the tables just created if they did not already exist.
      if(!$table_exists){
        $q->query($DB, "drop table $table");
        $q->query($DB, "drop table $table"."_bodies");
      }
      $page=$frompage;
      QueMessage("Could not get an id for the new forum.\nCheck your database settings.");
    } else {
      $name = addslashes($name);
      $description = addslashes($description);
      if(!$folder) $staff_host = addslashes($staff_host);
      if(!$folder) {
        if(isset($att_types)){
          $upload_types=strtolower(implode(";", $att_types));
        }
        $upload_size=(int)$upload_size;
        $max_uploads=(int)$max_uploads;
        // please keep this formatted like this:
        $SQL="Insert into ".$pho_main." (id,   name,     active,  description,     config_suffix,     folder,   parent,   display,   table_name,  moderation,     email_list,     email_return,     email_tag,     check_dup,   multi_level,   collapse,    flat,    lang,              html,    table_width,     table_header_color,     table_header_font_color,     table_body_color_1,     table_body_color_2,     table_body_font_color_1,     table_body_font_color_2,     nav_color,     nav_font_color,     allow_uploads,     upload_types,     upload_size,     max_uploads,     security,     showip, emailnotification,    body_color,     body_link_color,     body_alink_color,     body_vlink_color)
                                 values ($id,  '$name',  1,       '$description',  '$config_suffix',  $folder,  $parent,  $display,  '$table',    '$moderation',  '$email_list',  '$email_return',  '$email_tag',  $check_dup,  $multi_level,  $collapsed,  $rflat,  '$language_file',  '$allow_html',  '$table_width',  '$table_header_color',  '$table_header_font_color',  '$table_body_color_1',  '$table_body_color_2',  '$table_body_font_color_1',  '$table_body_font_color_2',  '$nav_color',  '$nav_font_color',  '$allow_uploads',  '$upload_types',  '$upload_size',  '$max_uploads',  '$security',  '$showip', $emailnotification, '$body_color',  '$body_link_color',  '$body_alink_color',  '$body_vlink_color')";
      } else {
        $SQL="Insert into ".$pho_main." (id,name,active,description,config_suffix,lang,folder,parent,table_width,table_header_color,table_header_font_color,table_body_color_1,table_body_font_color_1,nav_color,nav_font_color,body_color,body_link_color,body_alink_color,body_vlink_color) values ('$id', '$name', 0, '$description', '$config_suffix', '$language_file', '$folder', '$parent', '$table_width', '$table_header_color', '$table_header_font_color', '$table_body_color_1', '$table_body_font_color_1', '$nav_color', '$nav_font_color','$body_color','$body_link_color','$body_alink_color','$body_vlink_color')";
      }

      $q->query($DB, $SQL);
      $err=$q->error();
      if($err==""){
        if($DB->type=="mysql"){
          $id=$DB->lastid();
        }
        writefile($id,true);
        if(get_magic_quotes_gpc()) $name=stripslashes($name);

        if (!$folder && $AllowAttachments && $allow_uploads == 'Y') {
          if(!file_exists("$AttachmentDir/$table") && !@mkdir("$AttachmentDir/$table", 0777)){
            QueMessage("The directory for attachments could not be created.");
          } else {
            chmod("$AttachmentDir/$table", 0777);
          }
          if($err=create_table($DB, "attachments", $table."_attachments")){
            QueMessage("Could not create attachments table.  Database server said \"$err\"");
          }
        }

        QueMessage(stripslashes($name)." created [id: $id]");
        $num=$id;
        $f=$num;
        include "$PHORUM[settings_dir]/$num.php";
      } else {
        $name = stripslashes($name);
        $description = stripslashes($description);
        if(!$folder) $staff_host = stripslashes($staff_host);
        if(!$table_exists){
          $q->query($DB, "drop table $table");
          $q->query($DB, "drop table $table"."_bodies");
        }
        QueMessage("Could not add forum to the main table (".$pho_main.").  Database error: $err.");
        $page=$frompage;
      }
    }
  } else {
    QueMessage($err);
    $page=$frompage;
  }
?>
