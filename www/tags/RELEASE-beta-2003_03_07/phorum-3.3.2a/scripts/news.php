<?php

    /*
    Phorum News v1.1
    Original Author - Stephen VanDyke

    This is a fast news script, I need to update post.php and the admin stuff so that we can designate a forum as NEWS ONLY (ie- first posts are moderated, but the rest are not...)

    -Stephen
    */

    // CONFIG
    $ShowLimit = 10; // this is how many stories to show on the page
    $f = "1"; // this is the forum id of the news forum.

    // set this to the right path to the Phorum dir.
    $path="/PATH/TO/PHORUM";
    $oldir=getcwd();
    chdir($path);
    include "./common.php";

    $SQL = "SELECT
                subject,
                author,
                email,
                datestamp,
                body
            FROM
                $ForumTableName,
                $ForumTableName"."_bodies
             WHERE
                $ForumTableName.id=$ForumTableName"."_bodies.id
             AND
                parent = '0'
             AND
                approved = 'Y'
             ORDER BY
                datestamp DESC
             LIMIT
                $ShowLimit";

    $rSQL = $q->query($DB, $SQL);

    while (list($HEADLINE, $AUTHOR, $EMAIL, $DATESTAMP, $BODY) = mysql_fetch_row($rSQL))
    {
        if(!empty($EMAIL)){
            $AUTHOR="<a href=\"mailto:".htmlencode($EMAIL)."\">$AUTHOR</a>";
        }

        $DATESTAMP=date_format($DATESTAMP);

        echo "<p><B>$HEADLINE</B><BR><font size=\"-1\">By $AUTHOR on $DATESTAMP</font><BR>$BODY</p>";
    }

    // put the script back in the right dir.
    chdir($oldir);

?>