<?

// common things needs for all freshports php3 pages

function freshports_Category_Name($id, $db) {
   $sql = "select name from categories where id = $id";

#echo $sql;

   $result = mysql_query($sql, $db);

   $myrow = mysql_fetch_array($result);

#echo $myrow["name"];

   return $myrow["name"];
}

?>
