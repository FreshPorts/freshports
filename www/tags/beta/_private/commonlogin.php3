<?php

$db = mysql_connect("localhost","freshports", "marlboro");
mysql_select_db("freshports",$db);

function UserToCookie($User) {
    $EncodedUserID = base64_encode($User);
    $EncodedUserID = base64_encode($EncodedUserID);
    $EncodedUserID = base64_encode($EncodedUserID);
    $EncodedUserID = base64_encode($EncodedUserID);
    $EncodedUserID = base64_encode($EncodedUserID);
    $EncodedUserID = urlencode($EncodedUserID);

    return $EncodedUserID;
}

?>
