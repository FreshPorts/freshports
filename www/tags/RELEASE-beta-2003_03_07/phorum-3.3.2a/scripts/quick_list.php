<?php
  // This is a no frills script.  Alter at will.
  // pass or set num to the forum number you want displayed and
  // then include this script.

  chdir("../");  // path to where common.php is.  you may want to hard code
                 // this if you are going to be including this script from
                 // several places.

  include "common.php";

  $number=10;   // number of messages to show.

  // There are a few SQL statements to choose from here.
  // Keep all but one commented out.

  // newest $number messages
  $SQL="select id, thread, subject from $ForumTableName order by thread desc limit $number";

  // newest $number threads
//$SQL="select id, thread, subject from $ForumTableName where thread=id order by thread desc limit $number";

  $q->query($DB, $SQL);
  $rec=$q->getrow();
  while(is_array($rec)){
    echo "<a href=\"$forum_url/$read_page.$ext?f=$num&i=$rec[id]&t=$rec[thread]\">$rec[subject]</a><br>\n";
    $rec=$q->getrow();
  }
?>