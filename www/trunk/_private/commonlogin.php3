<?

//$Debug=1;

$Debug=0;

// get the host name, remove www if it's there
$host = $HTTP_HOST;
if (strtolower(substr($host, 0, 4)) == "www.") {
   $host = substr($host, 5);
}

#echo "first 4 characters is " . substr($host, 0, 5);

$cache_file     =       "/tmp/$host.cache." . basename($PHP_SELF);
$LastUpdateFile =       "$DOCUMENT_ROOT/lastupdate";

$database = "freshports";

if (strtolower(substr($host, 0, 7)) == "develop") {
//   $database = "freshportsdevelop";
} else {
   if (strtolower(substr($host, 0, 4)) == "test") {
      $database = "freshportstest";
   }
}

if ($Debug) {
   echo "database       = $database<br>\n";
   echo "cach_file      = $cache_file<br>\n";
   echo "LastUpdateFile = $LastUpdateFile<br>\n";
}

$db = mysql_connect("localhost","freshports", "marlboro");
mysql_select_db($database, $db);

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
