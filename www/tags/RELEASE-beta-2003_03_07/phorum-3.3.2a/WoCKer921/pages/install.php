<html>
<head>
  <title>Phorum Install</title>
</head>
<body bgcolor="white">
Phorum Install Script<br /><br />
<?php

settype($upgrade, "integer");

if(isset($HTTP_POST_VARS)){
  reset($HTTP_POST_VARS);
  while(list($var, $val)=each($HTTP_POST_VARS)){
    $$var=$val;
  }
}

// process forms
switch($step){
    case 1:
        $PHORUM['dbtype']=$dbType;
        writefile();
        break;
    case 2:
        if(!file_exists("./db/$dbType.php")) {
            $err="Could not find or open the dbfile. Check your settings again.";
        } else {
            if(!$DB->open($dbName, implode(':', explode(':', $dbServer)), $dbUser, $dbPass)){
            $err="Could not connect to database.  Check your settings again.";
        } else {
          $PHORUM['main_table']=$mainTable;
          $PHORUM['dbtype']=$dbType;
          $PHORUM['DatabaseServer']=$dbServer;
          $PHORUM['DatabaseName']=$dbName;
          $PHORUM['DatabaseUser']=$dbUser;
          $PHORUM['DatabasePassword']=$dbPass;
          writefile();
          echo "Database settings OK!<br />\n";
          if($upgrade){
            echo "Upgrading tables...<br />";
            if($dbType=="mysql"){
              include("$admindir/upgrade.php");
            } else {
              include("$admindir/upgrade_pg.php");
            }
            writefile("all");
          } else {
            echo "Creating initial tables...";
            if(($err=create_table($DB, "forums", $PHORUM["main_table"])) || ($err=create_table($DB, "auth", $PHORUM["main_table"])) || ($err=create_table($DB, "moderators", $PHORUM["main_table"]))){
              $err="Could not create tables.  Server said: $err";
            } else {
              echo "tables created.<br />\n";
            }
          }
        }
      }
      break;
    case 3:
        if(empty($AdminUser) || empty($AdminPass)){
            $err="Please fill in all fields";
        } elseif($AdminPass!=$AdminPass2){
            $err="Passwords do not match";
        } else {
            $id=$DB->nextid($PHORUM["auth_table"]);
            $crypt_pass=md5($AdminPass);
            $SQL="Insert into $PHORUM[auth_table] (username, password) values ('$AdminUser', '$crypt_pass')";
            if($q->query($DB, $SQL)){
                if($DB->type=="mysql") $id=$DB->lastid();
                $SQL="Insert into $PHORUM[mod_table] (user_id, forum_id) values ($id, 0)";
                if($q->query($DB, $SQL)){
                    echo "Admin User Created";
                }
                else{
                    $err="Could not create admin user.  Database said: ".$q->error();
                }
            }
            else{
                 $SQL="Insert into $PHORUM[mod_table] (user_id, forum_id) values ($id, 0)";
                 if($q->query($DB, $SQL)){
                     echo "Admin User Created";
                 }
              }
            }
        break;
    case 4:
        $url=parse_url($PhorumURL);
        if(!is_array($url)){
            $err="That is not a valid URL";
        } elseif(!is_email($AdminEmail)) {
            $err="That is not a valid email address";
        } else {
            $forum_url=$PhorumURL;
            $DefaultEmail=$AdminEmail;
            writefile();
        $SQL="update $PHORUM[auth_table] set email='$AdminEmail',name='Admin' where id=$id";
            $q->query($DB, $SQL);
            echo "Congratulations.  <a href=\"$PHP_SELF\">Click here</a> to go to the admin.";
        }
        break;
}


if($err){
    echo "<br><font color=\"Red\">Error: $err<br /><br /></font>";
    $cont=$step+1;
    echo "<form action=\"$myname\" method=\"post\"><input type=\"hidden\" name=\"step\" value=\"$cont\" /><input type=\"submit\" value=\"Continue\" /></form>";
    $step--;
}

// show forms
switch($step){
    case 0:
?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="install">
<input type="Hidden" name="step" value="1">
Enter Type Of Database :<br />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
  <th align="left" valign="middle">Database-Type:</th>
  <td valign="middle">
    <select name="dbType">
     <?php
   while(list($key,$var)=each($dbtypes)) {
     print "<option value='$key'";
     if($key==$PHORUM['dbtype']) {
       print " selected";
     }
     print ">$var\n";
   }
   ?>
   </select>
   </td>
</tr>
</table>
<br>
<center><input type="submit" value="Submit"></center>
<br />
<br />
</form>
<?php
    break;
    case 1:
?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="install">
<input type="Hidden" name="step" value="2">
<input type="hidden" name="dbType" value="<?php echo $dbType; ?>">
Enter Your Database Settings:<br />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
  <th align="left" valign="middle">Database - Server Name:</th>
  <td valign="middle"><input type="Text" name="dbServer" value="<?php echo $dbServer; ?>" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">Database - Name:</th>
  <td valign="middle"><input type="Text" name="dbName" value="<?php echo $dbName; ?>" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">Database - User Name:</th>
  <td valign="middle"><input type="Text" name="dbUser" value="<?php echo $dbUser; ?>" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">Database - Password:</th>
  <td valign="middle"><input type="Text" name="dbPass" value="<?php echo $dbPass; ?>" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">Phorum - Main Table Name:</th>
  <td valign="middle"><input type="Text" name="mainTable" value="<?php echo $PHORUM['main_table']; ?>" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">&nbsp;</th>
  <td valign="middle"><input type="checkbox" name="upgrade" value="1" /> Check here if this is an upgrade.<br />Read docs/upgrade.txt for information about some of your settings.</td>
</tr>
</table>
<br>
<center><input type="submit" value="Submit"></center>
<br />
<br />
<b>NOTE:  If SQL Safe Mode is in use on your server, leave the username and password emtpy.</b>
</form>
<?php
    break;
    case 2:
?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="install">
<input type="Hidden" name="step" value="3">
Create the admin user:<br />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
  <th align="left" valign="middle">User Name:</th>
  <td valign="middle"><input type="text" name="AdminUser" value="<?php echo $AdminUser; ?>" maxlength="50" size="20"></td>
</tr>
<tr>
  <th align="left" valign="middle">Password:</th>
  <td valign="middle"><input type="password" name="AdminPass" maxlength="50" size="20"></td>
</tr>
<tr>
  <th valign="middle" align="right">(again)</th>
  <td valign="middle"><input type="password" name="AdminPass2" maxlength="50" size="20"></td>
</tr>
</table>
<br>
<center><input type="submit" value="Submit"></center>
</form>
<?php
    break;
    case 3:
        if(empty($PhorumURL)) $PhorumURL="http://$HTTP_HOST".dirname(dirname($PHP_SELF));
?>
<form action="<?php echo $myname; ?>" method="POST">
<input type="Hidden" name="page" value="install">
<input type="Hidden" name="step" value="4">
<input type="Hidden" name="id" value="<?php echo $id?>">
Last step.<br />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
  <th align="left" valign="middle">Phorum URL:</th>
  <td valign="middle"><input type="text" name="PhorumURL" value="<?php echo $PhorumURL; ?>" size="40"></td>
</tr>
<tr>
  <th align="left" valign="middle">Admin Email Address:</th>
  <td valign="middle"><input type="text" name="AdminEmail" value="<?php echo $AdminEmail; ?>" size="20"></td>
</tr>
</table>
<br>
<center><input type="submit" value="Submit"></center>
</form>
<?php
    break;
}
?>
</body>
</html>
