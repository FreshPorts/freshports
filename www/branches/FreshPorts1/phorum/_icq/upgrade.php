<xmp>
<?
  $oldinfpath="./";
  if(file_exists("$oldinfpath/forums.inf")){
    include "$oldinfpath/forums.inf";
  
    chdir("../");
    include "common.php";
    error_reporting(0);  
    $f=current($forums);
    while(is_array($f)){
      if($f['email_mod']) $moderation="r";
      $sSQL="Insert into forums values ('$f[id]', '$f[name]', '$f[active]', '$f[description]', 0, 0, 0, 'new$f[table]', '$moderation', '$f[mod]', '$f[mod_pass]', '', '', '0', '$f[multi_level]', '$f[collapse]', '0', '$f[staff_host]', 'lang/$f[lang]', '$f[html]', '$f[table_width]', '$f[table_header_color]', '$f[table_header_font_color]', '$f[table_body_color_1]', '$f[table_body_color_2]', '$f[table_body_font_color_1]', '$f[table_body_font_color_2]', '$f[nav_color]', '$f[nav_font_color]')";
      echo "Inserting $f[name]...\n";
      flush();
      $q->query($DB, $sSQL);  
      if($err=$q->error()) echo $err."\n";
      create_table($DB, "main", "new$f[table]");
      create_table($DB, "bodies", "new$f[table]");
      $sSQL="insert into new$f[table] select id, datestamp, thread, parent, author, subject, email, host, email_reply, 'Y' from $f[table]";
      echo "Moving message headers for $f[name]...\n";
      flush();
      $q->query($DB, $sSQL);  
      if($err=$q->error()) echo "ERROR: $err\n";
      $sSQL="insert into new$f[table]"."_bodies select * from $f[table]"."_bodies";
      echo "Moving message bodies for $f[name]...\n";
      flush();
      $q->query($DB, $sSQL);
      if($err=$q->error()) echo "ERROR: $err\n";
      $sSQL="select max(id) as id from new$f[table]";
      $q->query($DB, $sSQL);  
      $rec=$q->getrow();
      $DB->nextid("new$f[table]");
      if($rec["id"])  $DB->reset_sequence("new$f[table]", $rec["id"]+1);
      $f=next($forums);
      echo "\n-------------------\n\n";
    }
    echo "Attempting to rename $oldinfpath/forums.inf to $oldinfpath/forums.inf.old\n\n";
    rename("$oldinfpath/forums.inf", "$oldinfpath/forums.inf.old");
    echo "You now need to rebuild the inf files using the Phorum 3.1 admin.\n";
  }
  else{
    echo "Could not find old forums.inf file at location: $oldinfpath/forums.inf";
  }
  
?>
</xmp>