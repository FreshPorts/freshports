<?

$Debug=0;

// get the host name, remove www if it's there
$host = $HTTP_HOST;
if (strtolower(substr($host, 0, 4)) == "www.") {
   $host = substr($host, 4);
}

if (substr($host, -3) == ":80") {
   $host = substr($host, 0, strlen($host) - 3);
}

#echo "first 4 characters is " . substr($host, 0, 5);

#
# cache files take the form of /tmp/freshports.<URL to site>.cache.<file name>
#
$cache_file     =       "/tmp/freshports." . strtolower($host) . ".cache." . basename($PHP_SELF);
$LastUpdateFile =       "$DOCUMENT_ROOT/lastupdate";

$database = "freshports";

if (strtolower(substr($host, 0, 7)) == "develop") {
//   $database = "freshportsdevelop";
} else {
   if (strtolower(substr($host, 0, 4)) == "test") {
      $database = "freshportstest";
   } else {
      if (strtolower(substr($host, 0, 6)) == "change" || strtolower(substr($host, 0, 6)) == "public") {
         $database = "freshportschange";
      }
   }
}

#
# this is a debug aid so you don't change the wrong database.
#

if ($Debug) {
   echo "database       = $database<br>\n";
   echo "cach_file      = $cache_file<br>\n";
   echo "LastUpdateFile = $LastUpdateFile<br>\n";
}

$db = mysql_connect("localhost", "freshports", "marlboro");
if (!mysql_select_db($database, $db)) {
   echo mysql_error();
   exit;
};

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
