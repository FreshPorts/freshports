<?php check_security(); ?>
<?php
switch($subaction) {
    case 'delete':
        $SQL="delete from $pho_main"."_auth where id=$uid";
        $q->query($DB, $SQL);
        $SQL="delete from $pho_main"."_moderators where user_id=$uid";
        $q->query($DB, $SQL);
        $err="User $uid has been deleted.";
        break;
    case 'save_user':
        if(strlen($password) == 0) {
        $pass='';
    } elseif($password2 != $password) {
            $err="The passwords don't match.";
        } else {
            $crypt_pass=md5($password);
            $pass="password='$crypt_pass',";
        }
        if(!$err) {
            $name=htmlspecialchars($name);
            $email=htmlspecialchars($email);
            $webpage=htmlspecialchars($webpage);
            $image=htmlspecialchars($image);
            $signature=htmlspecialchars($signature);
            $icq=htmlspecialchars($icq);
            $yahoo=htmlspecialchars($yahoo);
            $aol=htmlspecialchars($aol);
            $msn=htmlspecialchars($msn);
            $jabber=htmlspecialchars($jabber);

            if(!get_magic_quotes_gpc()) {
                $username=addslashes($username);
                $name=addslashes($name);
                $email=addslashes($email);
                $webpage=addslashes($webpage);
                $image=addslashes($image);
                $signature=addslashes($signature);
                $icq=addslashes($icq);
                $yahoo=addslashes($yahoo);
                $aol=addslashes($aol);
                $msn=addslashes($msn);
                $jabber=addslashes($jabber);
            }

            if(!empty($uid)){
                $sSQL="UPDATE $pho_main"."_auth SET username='$username', name='$name', $pass email='$email', webpage='$webpage', image='$image', signature='$signature', icq='$icq', yahoo='$yahoo', aol='$aol', msn='$msn', jabber='$jabber' WHERE id='$uid'";
                $q->query($DB, $sSQL);
                $sSQL="delete from $PHORUM[mod_table] where user_id='$uid'";
                $q->query($DB, $sSQL);
            } else {
                $uid=$DB->nextid($pho_main."_auth");
                if($uid==0 && $DB->type!="mysql"){
                    QueMessage("Could not get an id for the new user.\nCheck your database settings.");
                } else {
                    $sSQL="Insert into $pho_main"."_auth (
                                id,
                                username,
                                name,
                                password,
                                email,
                                webpage,
                                image,
                                signature,
                                icq,
                                yahoo,
                                aol,
                                msn,
                                jabber
                            ) VALUES (
                                '$uid',
                                '$username',
                                '$name',
                                '$crypt_pass',
                                '$email',
                                '$webpage',
                                '$image',
                                '$signature',
                                '$icq',
                                '$yahoo',
                                '$aol',
                                '$msn',
                                '$jabber'
                            )";

                    $q->query($DB, $sSQL);
                    if(!$err=$q->error()){
                        if($DB->type=="mysql"){
                          $uid=$DB->lastid();
                        }
                    }
                }
            }

            if(!$err){
                if(isset($grant_admin)){
                        $sSQL="insert into $PHORUM[mod_table] values ('$uid', 0)";
                        $q->query($DB, $sSQL);
                }
                if(is_array($grant_mod)){
                       while(list($key, $fid)=each($grant_mod)){
                               $sSQL="insert into $PHORUM[mod_table] values ('$uid', $fid)";
                               $q->query($DB, $sSQL);
                       }
                }
                $err="User successfully updated.";
                $subaction="";
            } else {
                $subaction="adduser";
            }
        } else {
            $subaction="edituser";
        }
        break;
}
QueMessage($err);
?>
