<?php

////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////
  if(empty($action)) $action="view";
  require "./common.php";
  include "$PHORUM[include]/post_functions.php";

  initvar("done");
  if($action=="edit"){
      initvar("name");
      initvar("password");
      initvar("email");
      initvar("webpage");
      initvar("image");
      initvar("signature");
      initvar("icq");
      initvar("yahoo");
      initvar("aol");
      initvar("jabber");
      initvar("msn");
      initvar("done");
      initvar("Error");
      initvar("process");
  }

  //Thats for all those ppl who likes to use different colors in different forums
  if($f!=0){
    $table_width=$ForumTableWidth;
    $table_header_color=$ForumTableHeaderColor;
    $table_header_font_color=$ForumTableHeaderFontColor;
    $table_body_color_1=$ForumTableBodyColor1;
    $table_body_font_color_1=$ForumTableBodyFontColor1;
    $nav_color=$ForumNavColor;
  }
  else{
    $table_width=$default_table_width;
    $table_header_color=$default_table_header_color;
    $table_header_font_color=$default_table_header_font_color;
    $table_body_color_1=$default_table_body_color_1;
    $table_body_font_color_1=$default_table_body_font_color_1;
    $nav_color=$default_nav_color;
  }

  //Post Count Function
  function Count_Posts($user_id){
  global $DB,$pho_main;
  //Get list of the forums
  $sql="Select distinct(table_name) from ".$pho_main." where active='1' AND folder='0'";
  $q=new query($DB, $sql);
  $forums=$q->getrow();
  $totalposts=0;
  //Output them in the scipt
  while(is_array($forums)){
    //Count posts in each forum
    $sql="SELECT count(*) as posts FROM $forums[table_name] WHERE userid='$user_id'";
    $query=new query($DB, $sql);
    $rec=$query->getrow();
    $posts=$rec['posts'];
    //Add Them To totals
    $totalposts=$totalposts+$posts;
    $forums=$q->getrow();
  }
  return $totalposts;
  }

  if($id){
    $user_id=$id;
    $SQL="Select * from $pho_main"."_auth where id='$user_id'";
    $q->query($DB, $SQL);
    $rec=$q->getrow();
    if(!is_array($rec))
      $error=$lNoUser;
    $UserName=$rec["username"];
  }else{
    $error=$lNoId;
  }
  $title = " - $lUserProfile";
  if(!empty($password) && !empty($checkpassword)){
  $ChangePass=false;
  if($password!=$checkpassword)
    $EditError=$lNoPassMacth;
  else
    $ChangePass=true;
  }

  if(!empty($name) && !empty($email)){
    if($password!=$checkpassword){
        $EditError=$lNoPassMacth;
    } elseif(censor_check(array($name, $email, $webpage, $image, $signature, $icq, $yahoo, $aol, $msn, $jabber))) {
        $EditError=$lRegistrationCensor;
    } else {


        $safe_name=htmlspecialchars($name);
        $safe_email=htmlspecialchars($email);
        $safe_webpage=htmlspecialchars($webpage);
        $safe_image=htmlspecialchars($image);
        $safe_signature=htmlspecialchars($signature);
        $safe_icq=htmlspecialchars($icq);
        $safe_yahoo=htmlspecialchars($yahoo);
        $safe_aol=htmlspecialchars($aol);
        $safe_msn=htmlspecialchars($msn);
        $safe_jabber=htmlspecialchars($jabber);

        if(!get_magic_quotes_gpc()){
            $safe_name=addslashes($safe_name);
            $safe_email=addslashes($safe_email);
            $safe_webpage=addslashes($safe_webpage);
            $safe_image=addslashes($safe_image);
            $safe_signature=addslashes($safe_signature);
            $safe_icq=addslashes($safe_icq);
            $safe_yahoo=addslashes($safe_yahoo);
            $safe_aol=addslashes($safe_aol);
            $safe_msn=addslashes($safe_msn);
            $safe_jabber=addslashes($safe_jabber);
        }

        $SQL="select id, name, email from ".$pho_main."_auth where (name='$safe_name' or email='$safe_email')";
        if(!empty($id)) $SQL.=" and id!=$user_id";
        //run query
        $q->query($DB,$SQL);
        if($q->numrows()>0){
          $check=$q->getrow();
          if(strtolower($check['name'])==strtolower($name))
            $EditError=$lDupName;
          if(strtolower($check['email'])==strtolower($email))
            $EditError=$lDupEmail;
          if($password!=$checkpassword)
            $EditError=$lNoPassMacth;
        } elseif(!empty($phorum_auth) && $UserName==$phorum_user["username"]) {
          // Change Password.
          $passsql="";
          if(!empty($ChangePass)){
            $crypt_pass=md5($password);
            $passsql=" password='$crypt_pass',";
          }
          $sSQL="UPDATE $pho_main"."_auth SET name='$safe_name',$passsql email='$safe_email', webpage='$safe_webpage', image='$safe_image', signature='$safe_signature', icq='$safe_icq', yahoo='$safe_yahoo', aol='$safe_aol', msn='$safe_msn', jabber='$safe_jabber'  WHERE id='$id'";
          $q->query($DB, $sSQL);
          $done=true;

          header("Location: $forum_url/profile.$ext?f=$f&id=$id$GetVars");
          exit();
        }
    }
  }elseif(!empty($process)){
    $EditError=$lFillInAll;
  }
  include phorum_get_file_name("header");

  //////////////////////////
  // START NAVIGATION     //
  //////////////////////////

    if($ActiveForums>1)
      // Forum List
      addnav($menu, $lForumList, "$forum_page.$ext?f=$ForumParent$GetVars");
      // Go To Top
      addnav($menu, $lGoToTop, "$list_page.$ext?f=$num$GetVars");

      // New Topic
      addnav($menu, $lStartTopic, "$post_page.$ext?f=$num$GetVars");

      // Search
      addnav($menu, $lSearch, "$search_page.$ext?f=$num$GetVars");
      //Thats for stuff below


    // Log Out/Log In
    if(!empty($phorum_auth)){
      // Log Out
      addnav($menu, $lLogOut, "login.$ext?f=$f&logout=1$GetVars");
      if($id!=$phorum_user["id"])
        //The profile of the logged in user
        addnav($menu, $lMyProfile, "profile.$ext?f=$num&id=$phorum_user[id]$GetVars");
    }
    else{
      // Register
      addnav($menu, $lRegisterLink, "register.$ext?f=$f$GetVars");
      // Log In
      addnav($menu, $lLogIn, "login.$ext?f=$f$GetVars");
    }

    if($action=="edit")
      //Back
      addnav($menu, $lBack, "profile.$ext?f=$num&id=$id$GetVars");

    $nav=getnav($menu);

  //////////////////////////
  // END NAVIGATION       //
  //////////////////////////

//If user Edits Profile, and He did submit it yet.
if(($action=='edit' && !$done) || !empty($EditError)){
//Security, so that only Owner could edit it.
//NO go
if(empty($phorum_auth) || ($UserName!=$phorum_user["username"])){
?>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
    <td <?php echo bgcolor($nav_color); ?>><?php echo $nav; ?></td>
</tr>
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
        <table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0">
        <tr>
            <td height="21" <?php echo bgcolor($table_header_color); ?>><FONT color="<?php echo $table_header_font_color; ?>">&nbsp;<?php echo $lEditProfileErrorTitle; ?></font></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $lEditProfileError; ?></font></td>
        </tr>
        </table>
    </td>
</tr>
</table>
<?php
// Its yours
}else{
  if(!empty($EditError))
    echo "<p><b>$EditError</b>";
?>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
    function textlimit(field, limit) {
        if (field.value.length > limit)
            field.value = field.value.substring(0, limit);
    }
</script>
<form action="<?php echo $PHP_SELF; ?>?f=<?php echo $f; ?>&id=<?php echo $id; ?>" method="post">
<input type="hidden" name="process" value="1">
<input type="hidden" name="target" value="<?php echo $target; ?>">
<input type="hidden" name="id" value="<?php echo $user_id; ?>">
<?php echo $PostVars; ?>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
      <table cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td><?php echo $nav; ?></td>
        </tr>
      </table>
    </td>
</tr>
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
        <table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0">
        <tr>
            <td height="21" colspan="2" <?php echo bgcolor($table_header_color); ?>><FONT color="<?php echo $table_header_font_color; ?>">&nbsp;<?php echo $lEditProfile; ?></font></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lFormName;?>*:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="name" size="30" maxlength="50" value="<?php echo $rec['name']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lFormEmail;?>*:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="email" size="30" maxlength="50" value="<?php echo $rec['email']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lNewPass;?>:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="password" name="password" size="20" maxlength="20" value=""></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lPassAgain; ?>:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="password" name="checkpassword" size="20" maxlength="20" value=""></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lWebpage;?>:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="webpage" size="50" maxlength="100" value="<?php echo $rec['webpage']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lImageURL;?>:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="image" size="50" maxlength="100" value="<?php echo $rec['image']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_icq.gif" alt="ICQ" border="0" width="16" height="16" />&nbsp;ICQ:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="icq" size="50" maxlength="50" value="<?php echo $rec['icq']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_aim.gif" alt="AIM" border="0" width="16" height="16" />&nbsp;AOL:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="aol" size="50" maxlength="50" value="<?php echo $rec['aol']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_yahoo.gif" alt="Yahoo IM" border="0" width="16" height="16" />&nbsp;Yahoo:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="yahoo" size="50" maxlength="50" value="<?php echo $rec['yahoo']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_msn.gif" alt="MSN" border="0" width="16" height="16" />&nbsp;MSN:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="msn" size="50" maxlength="50" value="<?php echo $rec['msn']; ?>"></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_jabber.gif" alt="Jabber" border="0" width="16" height="16" />&nbsp;Jabber:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="text" name="jabber" size="50" maxlength="50" value="<?php echo $rec['jabber']; ?>"></td>
        </tr>
        <tr>
            <td valign=top <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lSignature;?>:&nbsp;&nbsp;</font></td>
            <td <?php echo bgcolor($table_body_color_1); ?>><textarea class="PhorumBodyArea" onKeyDown="textlimit(this.form.signature,255);" onKeyUp="textlimit(this.form.signature,255);" cols="30" rows="6" name="signature"><?php echo "\n".$rec['signature']; ?></textarea></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap>&nbsp;</td>
            <td <?php echo bgcolor($table_body_color_1); ?>><input type="submit" value="<?php echo $lUpdateProfile; ?>">&nbsp;<br><img src="images/trans.gif" width=3 height=3 border=0 alt="trans"></td>
        </tr>
        </table>
    </td>
</tr>
</table>
</FORM>
<?php }//END if(empty($phorum_auth) || ($rec["username"]!=$phorum_user["username"]))
//If there was any errors, Output them in that table.
}elseif(isset($error)){ ?>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
      <table cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td><?php echo $nav; ?></td>
        </tr>
      </table>
    </td>
</tr>
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
        <table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0" width="100%">
        <tr>
            <td height="21" <?php echo bgcolor($table_header_color); ?>><FONT color="<?php echo $table_header_font_color; ?>">&nbsp;<?php echo $lEditProfileErrorTitle; ?></font></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $error; ?></font></td>
        </tr>
        </table>
    </td>
</tr>
</table>
<?php //Show the Profile
}else{ ?>
<table cellspacing="0" cellpadding="0" border="0" width="<?php echo $table_width; ?>">
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
      <table cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td><?php echo $nav; ?></td>
        </tr>
      </table>
    </td>
</tr>
<tr>
    <td <?php echo bgcolor($nav_color); ?>>
        <table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0" width="100%">
        <tr>
            <td height="21" colspan="2" <?php echo bgcolor($table_header_color); ?> width="100%"><FONT color="<?php echo $table_header_font_color; ?>">&nbsp;<?php echo $lUserProfile; ?>
            <?php  //Show [Edit Profile] if you are logged on and profile is yours.
            if(!empty($phorum_auth) && ($rec["username"]==$phorum_user["username"]))
                echo "&nbsp;|&nbsp;</font><a href='$PHP_SELF?f=$f&id=$id&action=edit'><font color=\"$table_header_font_color\">$lEditProfile</font></a>";
            ?>
            </font></td>
        </tr>
        <tr>
            <td <?php echo bgcolor($table_body_color_1); ?>><?php if($rec['image']) echo "<img src=\"$rec[image]\" border=\"0\">"; ?></td>
            <td width="100%" valign="top" <?php echo bgcolor($table_body_color_1); ?>>
                <table cellspacing="0" cellpadding="2" border="0">
                <?php if($done){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap colspan=2><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<b><?php echo $lProfileUpdated; ?></b></td>
                </tr>
                <?php } ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lName;?>:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $rec['name']; ?></font></td>
                </tr>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lPosts;?>:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo Count_Posts($user_id); ?></font></td>
                </tr>
                <?php if($rec['email']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lEmail;?>:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><a href="<?php echo htmlencode("mailto:".$rec['email']); ?>"><?php echo htmlencode($rec['email']); ?></a></font></td>
                </tr>
                <?php }
                if($rec['webpage']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lWebpage;?>:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><a href="<?php echo $rec['webpage']; ?>" target="_blank"><?php echo $rec['webpage']; ?></a></font></td>
                </tr>
                <?php }
                if($rec['icq']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_icq.gif" alt="ICQ" border="0" width="16" height="16" />&nbsp;ICQ:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $rec['icq']; ?>&nbsp;<img align="middle" src="http://wwp.icq.com/scripts/online.dll?icq=<?php echo $rec['icq']; ?>&img=7" alt="ICQ Status" border="0"></font></td>
                </tr>
                <?php }
                if($rec['aol']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_aim.gif" alt="AIM" border="0" width="16" height="16" />&nbsp;AOL:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><a href="aim:goim?screenname=<?php echo $rec['aol']; ?>"><?php echo $rec['aol']; ?></a></font></td>
                </tr>
                <?php }
                if($rec['jabber']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_jabber.gif" alt="Jabber" border="0" width="16" height="16" />&nbsp;Jabber:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $rec['jabber']; ?></font></td>
                </tr>
                <?php }
                if($rec['yahoo']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_yahoo.gif" alt="Yahoo IM" border="0" width="16" height="16" />&nbsp;Yahoo:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $rec['yahoo']; ?><img src="http://opi.yahoo.com/online?u=<?php echo $rec['yahoo']; ?>&m=g&t=1" alt="Yahoo Status" border="0"></font></td>
                </tr>
                <?php }
                if($rec['msn']){ ?>
                <tr>
                    <td <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<img src="images/icon_msn.gif" alt="MSN" border="0" width="16" height="16" />&nbsp;MSN:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo $rec['msn']; ?></font></td>
                </tr>
                <?php }
                // show the sig to the user if it is their profile.
                //Also HTML is all converted, so you will see all the html, we should probably change it so it would show all html as in post, but not right now.
                if($rec["signature"] && ($rec["username"]==$phorum_user["username"])){ ?>
                <tr>
                    <td valign=top <?php echo bgcolor($table_body_color_1); ?> nowrap><font color="<?php echo $table_body_font_color_1; ?>">&nbsp;<?php echo $lSignature;?>:&nbsp;&nbsp;</font></td>
                    <td <?php echo bgcolor($table_body_color_1); ?>><font color="<?php echo $table_body_font_color_1; ?>"><?php echo my_nl2br(htmlspecialchars($rec["signature"])); ?></font></td>
                </tr>
                <?php } ?>
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
<?php   } //END if($action=='edit' && !$done)

  include phorum_get_file_name("footer");
?>
