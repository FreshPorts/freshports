<?php

  echo "Altering table $pho_main<br />\n";
  flush();
  $SQL="ALTER TABLE $pho_main change id id int UNSIGNED DEFAULT '0' NOT NULL AUTO_INCREMENT";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change parent parent int UNSIGNED DEFAULT '0' NOT NULL AUTO_INCREMENT";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change display display int UNSIGNED DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change check_dup check_dup smallint unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change multi_level multi_level smallint(5) unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change collapse collapse smallint(5) unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main change flat flat smallint(5) unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD allow_uploads char(1) DEFAULT 'N' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_list char(50) DEFAULT '' NOT NULL after mod_pass";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_return char(50) DEFAULT '' NOT NULL after email_list";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_tag char(50) DEFAULT '' NOT NULL after email_return";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD config_suffix char(50) DEFAULT '' NOT NULL after description";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD upload_types char(100) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD upload_size int unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD max_uploads int unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD security int unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD showip smallint(5) unsigned DEFAULT 1 NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD emailnotification smallint(5) unsigned DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_link_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_alink_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_vlink_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main DROP mod_email";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main DROP mod_pass";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main DROP staff_host";
  $q->query($DB, $SQL);

  $SQL="DROP TABLE ".$pho_main."_seq";
  $q->query($DB, $SQL);

  create_table($DB, "auth", $PHORUM['main_table']);
  create_table($DB, "moderators", $PHORUM['main_table']);

  $SQL="Select id, name, table_name from $pho_main WHERE folder = '0'";
  $query = new query($DB, $SQL);

  $rec=$query->getrow();

  while(is_array($rec)){
    echo "Altering tables for $rec[name]<br />\n";
    flush();
    $SQL="ALTER TABLE $rec[table_name]_bodies CHANGE id id int unsigned DEFAULT '0' NOT NULL auto_increment";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name]_bodies CHANGE thread thread int unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);

     $SQL="DROP TABLE $rec[table_name]_seq";
     $q->query($DB, $SQL);

    $SQL="ALTER TABLE $rec[table_name] CHANGE id id int unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] CHANGE thread thread int unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] CHANGE parent parent int unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] CHANGE subject subject char(255) DEFAULT '' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] CHANGE email email char(200) DEFAULT '' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD msgid char(100) DEFAULT '' NOT NULL, ADD KEY msgid (msgid)";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD modifystamp int(10) unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD KEY modifystamp (modifystamp)";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD userid int(10) unsigned DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD KEY userid (userid)";
    $q->query($DB, $SQL);
    echo "Updating modifystamp for $rec[name]<br />\n";
    flush();
    $SQL="select thread, max(datestamp) as datestamp from $rec[table_name] group by thread";
    $q->query($DB, $SQL);

    $q2 = new query($DB);

    while($rec2=$q->getrow()){
        list($date,$time) = explode(" ", $rec2["datestamp"]);
        list($year,$month,$day) = explode("-", $date);
        list($hour,$minute,$second) = explode(":", $time);
        $tstamp = mktime($hour,$minute,$second,$month,$day,$year);
        $SQL="update $rec[table_name] set modifystamp=$tstamp where thread=$rec2[thread]";
        $q2->query($DB, $SQL);
        echo ".";
        flush();
    }
    $SQL="select id, attachment from $rec[table_name]";
    $q->query($DB, $SQL);
    if($q->numrows()>0){
        echo "Converting Attachments for $rec[name]<br />\n";
        while($rec2=$q->getrow()){

            $id=$DB->nextid("$rec[table_name]_attachments");
            if($id==0 && $DB->type!="mysql"){
              echo "Could not get an id for the attachment.<br />\n";
            }
            else{
              if ($rec2[attachment]) {
                  $SQL="Insert into $rec[table_name]_attachments (id, message_id, filename) values($id, $rec2[id], '$rec2[attachment]')";
                  $q2->query($DB, $SQL);
                  $err=$q2->error();
                  if($err==""){
                    if($DB->type=="mysql"){
                      $id=$DB->lastid();
                    }

                    $new_name = "$AttachmentDir/$rec[table_name]/$rec2[id]"."_$id".strtolower(strrchr($rec2["attachment"], "."));
                    if(! rename("$AttachmentDir/$rec[table_name]/$rec2[attachment]", $new_name)){
                      echo "Can't save upload file.";
                    }
                  }
              }
              else{
                echo "Error adding attachment.  DB said: $err<br />\n";
              }
            }

        }
        $SQL="ALTER TABLE $rec[table_name] DROP attachment";
        $q->query($DB, $SQL);
        create_table($DB, "attachments", "$rec[table_name]_attachments");
    }

    $rec=$query->getrow();
  }

  return 1;
?>
