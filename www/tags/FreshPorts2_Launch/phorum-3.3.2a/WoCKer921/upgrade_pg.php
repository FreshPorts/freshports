<?php

  echo "Altering table $pho_main<br />\n";
  flush();
  $SQL="ALTER TABLE $pho_main ADD allow_uploads char(1) DEFAULT 'N' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_list char(50) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_return char(50) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD email_tag char(50) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD config_suffix char(50) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD upload_types char(100) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD upload_size int4 DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD max_uploads int4 DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD security int4 DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD showip int2 DEFAULT 1 NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD emailnotification int2 DEFAULT '0' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_link_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_alink_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);
  $SQL="ALTER TABLE $pho_main ADD body_vlink_color char(7) DEFAULT '' NOT NULL";
  $q->query($DB, $SQL);

  // Not possible with PG - To remove an existing column the table must be recreated and reloaded
  //$SQL="ALTER TABLE $pho_main DROP mod_email";
  //$q->query($DB, $SQL);
  //$SQL="ALTER TABLE $pho_main DROP mod_pass";
  //$q->query($DB, $SQL);
  //$SQL="ALTER TABLE $pho_main DROP staff_host";
  //$q->query($DB, $SQL);
  echo "<b>$pho_main now contains 3 redundant fields: mod_email, mod_pass & staff_host<br />\n";
  echo "these can be removed useing a tool such as phpPgAdmin</b><br />\n";


  create_table($DB, "auth", $PHORUM["main_table"]);
  create_table($DB, "moderators", $PHORUM["main_table"]);


  $SQL="Select id, name, table_name from $pho_main WHERE folder = '0'";
  $query = new query($DB, $SQL);

  $rec=$query->getrow();

  while(is_array($rec)){
    echo "Altering tables for $rec[name]<br />\n";
    flush();
    $SQL="ALTER TABLE $rec[table_name]_bodies ALTER id SET DEFAULT '0'";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name]_bodies ALTER thread SET DEFAULT '0'";
    $q->query($DB, $SQL);

     // droping the sequence casues a duplicate key error
     //$SQL="DROP SEQUENCE $rec[table_name]_seq";
     //$q->query($DB, $SQL);

    $SQL="ALTER TABLE $rec[table_name] ADD msgid char(100) DEFAULT '' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="CREATE INDEX ".$rec[table_name]."_msgid on ".$rec[table_name]."(msgid)";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD modifystamp int4 DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="CREATE INDEX ".$rec[table_name]."_modifystamp on $rec[table_name](modifystamp)";
    $q->query($DB, $SQL);
    $SQL="ALTER TABLE $rec[table_name] ADD userid int4 DEFAULT '0' NOT NULL";
    $q->query($DB, $SQL);
    $SQL="CREATE INDEX ".$rec[table_name]."_userid on $rec[table_name](userid)";
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
        echo "<br />\n";
        echo "Converting Attachments for $rec[name]<br />\n";
        create_table($DB, "attachments", "$rec[table_name]_attachments");
        while($rec2=$q->getrow()){

            $id=$DB->nextid("$rec[table_name]_attachments");
            if($id==0 && $DB->type!="mysql"){
              echo "Could not get an id for the attachment.<br />\n";
            }
            else{
              $SQL="Insert into $rec[table_name]_attachments (id, message_id, filename) values($id, $rec2[id], '$rec2[attachment]')";
              $q->query($DB, $SQL);
              $err=$q->error();
              if($err==""){
                if($DB->type=="mysql"){
                  $id=$DB->lastid();
                }

                $new_name = "$AttachmentDir/$rec[name]/$rec2[id]"."_$id".strtolower(strrchr($rec2["attachment"], "."));
                if(rename("$AttachmentDir/$rec[name]/$rec2[attachment]", $new_name)){
                  echo "Can't save upload file.";
                }
              }
              else{
                echo "Error adding attachment.  DB said: $err<br />\n";
              }
            }

        }
 // Not possible with PG - To remove an existing column the table must be recreated and reloaded
        //$SQL="ALTER TABLE $rec[table_name] DROP attachment";
        //good_query($SQL) || return 0;

  echo "<b>$rec[table_name] contains 1 redundant field: attachment<br />\n";
  echo "this can be removed useing a tool such as phpPgAdmin</b><br />\n";

    }

    $rec=$query->getrow();
  }

  return 1;
?>
