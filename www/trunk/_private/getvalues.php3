<script language="php">

$DaysToShow  = 20;
$MaxArticles = 40;
$DaysNew     = 20;

// This is used to determine whether or not the cach can be used.
$DefaultMaxArticles = $MaxArticles;

if ($visitor) {

  $sql = "select * from users ".
         "where cookie = '$visitor'";

//  echo "sql=$sql<br>";

  $result = mysql_query($sql, $db) or die("getvalues query failed");

  if ($result) {
     $myrow = mysql_fetch_array($result);
     if ($myrow) {
        $UserID      = $myrow["userid"];
        $ID          = $myrow["id"];
        $emailsitenotices_yn  = $myrow["emailsitenotices_yn"];
        $email                = $myrow["email"];
        $watchnotifyfrequency = $myrow["watchnotifyfrequency"];

        if ($emailsitenotices_yn == "Y") {
           $emailsitenotices_yn = "ON";
        } else {
           $emailsitenotices_yn = "";
        }
 
//        echo "UserID = $UserID<br>";
//        echo "visitor = $visitor<br>";

        // record their last login
        $sql = "update users set lastlogin = '" . date("Y/m/d", time()) . "'" .
               " where id = $ID";
//        echo $sql, "<br>";
        $result = mysql_query($sql, $db);
     } else {
        $errors = "Sorry, but that login doesn't exist according to me.";
   }
  }
}
</script>
