<?php
  // login.php

    function check_login(){
        global $PHP_SELF, $HTTP_COOKIE_VARS, $HTTP_POST_VARS, $HTTP_GET_VARS, $PHORUM, $q, $DB;

        $success=false;

        if(isset($HTTP_GET_VARS["logout"])){
            setcookie("phorum_admin_session", "");
            $success=true;
            header("Location: $PHP_SELF");
            exit();
        }

        if(isset($HTTP_COOKIE_VARS["phorum_admin_session"])){
            $SQL="Select * from $PHORUM[auth_table] where sess_id='$HTTP_COOKIE_VARS[phorum_admin_session]'";
            $q->query($DB, $SQL);
            $PHORUM["admin_user"]=$q->getrow();
            if($PHORUM["admin_user"]["id"]) {
                $SQL="Select forum_id from $PHORUM[mod_table] where user_id=".$PHORUM["admin_user"]["id"];
                $q->query($DB, $SQL);
                while($rec=$q->getrow()){
                   $PHORUM["admin_user"]["forums"][$rec["forum_id"]]=true;
                }
                if(is_array($PHORUM["admin_user"]["forums"])){
                    $success=true;
                }
            }
        }

        if(!$success && isset($HTTP_POST_VARS["login"]) && isset($HTTP_POST_VARS["passwd"])){

            $id=phorum_check_login($HTTP_POST_VARS['login'], $HTTP_POST_VARS["passwd"]);
            if($id){
                $sess_id=phorum_session_id($HTTP_POST_VARS['login'], $HTTP_POST_VARS["passwd"]);
                setcookie("phorum_admin_session", "$sess_id");
                phorum_login_user($sess_id, $id);
                header("Location: $PHP_SELF");
                exit();
            }

        }

        if(!$success){

            $SQL = "select user_id from $PHORUM[mod_table] where forum_id=0 limit 1";
            $q->query($DB, $SQL);
            if($q->numrows()>0){
                show_login();
                exit();
            }
            else{
                // create temporary user
                $PHORUM["admin_user"]["name"]="Temporary User";
                $PHORUM["admin_user"]["forums"][0]=true;
            }
        }
    }

    function show_login()
    {
        global $PHP_SELF, $admindir;
        include "$admindir/header.php";
?>
<br /><br />
<form action="<?php echo $PHP_SELF; ?>" method="post">
<table border="0" cellspacing="0" cellpadding="2" class="box-table">
<tr>
  <th>Login: </th>
  <td><input class="login" type="text" size="15" name="login"></td>
</tr>
<tr>
  <th>Password: </th>
  <td><input class="login" type="password" size="15" name="passwd"></td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td><input class="login" type="submit" value="Login"></td>
</tr>
</table>
</form>
<?php
        include "$admindir/footer.php";
    }

?>