<?php
//  authors: Rich Davey (rich@atari.org)
//           Brian Moon (brian@phorum.org)
  chdir("../");
    include "common.php";
?>
<html>
<head>
<title>Phorum Stats</title>
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#800080" alink="#FF0000">
<form action="stats.php" method="GET">
<font size="+3">Phorum Stats</font><br>
<font size="-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Phorum Version: <?php echo $phorumver; ?></font>
<hr width="100%" size="1" noshade>
<?php
  $sSQL="Select id, name from ".$pho_main." where folder=0";
  $q->query($DB, $sSQL);
  echo $q->error();
/* Generate drop down list of Phorums */

    print "Select a forum : ";
    print "<select name=\"f\" size=\"1\">\n";

  $forum=$q->firstrow();
    while ($forum) {
        echo "<option value=\"$forum[id]\"";
    if($forum["id"]==$f) echo " selected";
    echo ">" . $forum["name"] . "</option>\n";
    $forum=$q->getrow();
    }

    print "</select>\n";
    print " Show data from : ";
    print "<select name=\"lastdays\" size=\"1\">\n";
    print "<option value=\"7\">Last Week</option>\n";
    print "<option value=\"14\">Last 2 Weeks</option>\n";
    print "<option value=\"30\">Last Month</option>\n";
    print "<option value=\"60\">Last 2 Months</option>\n";
    print "<option value=\"90\">Last Quarter</option>\n";
    print "</select> &nbsp; \n";
    print "<input type=\"submit\" name=\"submit\" value=\"Show\">\n";
  print "<hr width=\"100%\" size=\"1\" noshade>\n";
/* Reset for the next part */

  $forum=$q->firstrow();

/* Show all the info */

  if ($ForumName) {

        echo "<font size=\"+2\">Forum: $ForumName</b></font>\n";
        echo "<table width=\"500\" border=\"0\" cellspacing=\"1\" cellpadding=\"10\" bordercolor=\"#000000\" bgcolor=\"#000000\">\n";

/* Posts available from */

        $sSQL="SELECT max(datestamp) AS max FROM ".$ForumTableName;
        $q->query($DB, $sSQL);
    $row=$q->getrow();
        $max=$row["max"];

        if(substr($max,0,10)==date("Y-m-d")){
            $last_full_day=date("Y-m-d", mktime(0,0,0,substr($max, 5,2),substr($max, 8,2)-1,substr($max, 0,4)));
        } else {
            $last_full_day=substr($max, 0,10);
        }

        $sSQL="SELECT min(datestamp) AS min FROM ".$ForumTableName;
        $q->query($DB, $sSQL);
    $row=$q->getrow();
        $first_day=substr($row["min"], 0,10);

        $sSQL="SELECT count(*) AS total FROM ".$ForumTableName." WHERE datestamp < '$last_full_day 23:59:59'";
        $q->query($DB, $sSQL);
    $row=$q->getrow();
        $total=$row["total"];

        if ($total) {
            $time1=mktime(0,0,0,substr($last_full_day, 5,2),substr($last_full_day, 8,2)-1,substr($last_full_day, 0,4));
            $time2=mktime(0,0,0,substr($first_day, 5,2),substr($first_day, 8,2)-1,substr($first_day, 0,4));
            $date_diff=($time1 - $time2) / 84600;

            if ($date_diff == 0) $date_diff=1;
            $avg = number_format($total/$date_diff, 1, '.', '');

            echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top><b>Analyzed Dates:</b></td><td nowrap bgcolor=#F0F0F0>";

            $fdyear = substr($first_day, 0, 4);
            $fdmonth = substr($first_day, 5, 2);
            $fdday = substr($first_day, 8, 2);

            $ldyear = substr($last_full_day, 0, 4);
            $ldmonth = substr($last_full_day, 5, 2);
            $ldday = substr($last_full_day, 8, 2);

            print date("M j, Y", mktime(0,0,0,$fdmonth,$fdday,$fdyear) );
            print "to ";
            print date("M j, Y", mktime(0,0,0,$ldmonth,$ldday,$ldyear) );
            echo "</td></tr>\n";

/* Total Posts */

            echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top><b>Total messages:</b></td><td nowrap bgcolor=#F0F0F0>$total</td></tr>";
            echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top><b>Average per day:</b></td><td nowrap bgcolor=#F0F0F0>$avg</td></tr>";

/* Posts in the last X days */

            if ($date_diff > $lastdays) {
                $lastdaysdb=date("Y-m-d", mktime(0,0,0,substr($last_full_day, 5,2),substr($last_full_day, 8,2)-$lastdays,substr($last_full_day, 0,4)));
                $sSQL="SELECT count(*) AS total FROM ".$ForumTableName." WHERE datestamp < '$last_full_day 23:59:59' AND datestamp > '$lastdaysdb'";
                $q->query($DB, $sSQL);
        $row=$q->getrow();
                $lastdayscount = $row["total"];
                $lastdaysavg = number_format($lastdayscount/$lastdays, 1, '.', '');

                echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top><b>Posts in the last $lastdays days:</b></td><td nowrap bgcolor=#F0F0F0>";
                echo "$lastdayscount<br>";
                echo "<font size=-1>An average of $lastdaysavg posts per day<br>";
                $lastpercent = round(($lastdayscount/$total) * 100);
                echo "<font size=-1>The last $lastdays days account for $lastpercent" . "% of total posts<br>";
                echo "</td></tr>\n";
            }

/* Total Unique Posters */

            $sSQL="SELECT DISTINCT author FROM ".$ForumTableName;   /* MySQL only? */
            $q->query($DB, $sSQL);

            if ($q->numrows()>0) {
                $num_authors=$q->numrows();
                echo "<tr><td nowrap bgcolor=#F0F0F0>Total Unique Authors:</td><td nowrap bgcolor=#F0F0F0><b>$num_authors</b></td></tr>\n";
            } else {
                echo "<tr><td nowrap bgcolor=#F0F0F0>Total Unique Authors:</td><td nowrap bgcolor=#F0F0F0><b>No-one has posted yet!</b></td></tr>\n";
            }

/* Total Unique Threads */

            $sSQL="SELECT DISTINCT subject FROM ".$ForumTableName;  /* MySQL only? */
            $q->query($DB, $sSQL);

            if ($q->numrows()>0) {
                $num_threads=$q->numrows();
                echo "<tr><td nowrap bgcolor=#F0F0F0>Total Unique Threads:</td><td nowrap bgcolor=#F0F0F0><b>$num_threads</b></td></tr>\n";
            } else {
                echo "<tr><td nowrap bgcolor=#F0F0F0>Total Unique Threads:</td><td nowrap bgcolor=#F0F0F0><b>No-one has posted yet!</b></td></tr>\n";
            }

/* The Top Thread Subject (unchecked, maybe not correct result) */

            $sSQL="SELECT subject,count(*) AS cnt FROM ".$ForumTableName." GROUP BY subject ORDER BY cnt DESC LIMIT 1";
            $q->query($DB, $sSQL);

            if ($q->numrows() > 0) {
                $row=$q->getrow();
                $threadcount = $row["cnt"];
                echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top>Most Popular Thread:</td><td nowrap bgcolor=#F0F0F0><b>";
                echo $row["subject"];
                echo "</b><br><font size=-1>There are $threadcount posts in this thread</td></tr>\n";
            } else {
                echo "<tr><td nowrap bgcolor=#F0F0F0>Total Unique Posters:</td><td nowrap bgcolor=#F0F0F0><b>No-one has posted yet!</b></td></tr>\n";
            }

/* Top 10 posters */

            $sSQL="SELECT author,email,count(*) AS cnt FROM ".$ForumTableName." GROUP BY author,email ORDER BY cnt DESC LIMIT 10";
            $q->query($DB, $sSQL);

            if ($q->numrows()>0) {
                if ($q->numrows()>10) {
                    $num_authors=$max_authors;
                } else {
                    $num_authors=$q->numrows();
                }

                $row=$q->firstrow();
                echo "<tr><td nowrap bgcolor=#F0F0F0 valign=top>Top $num_authors posters:</td><td nowrap bgcolor=#F0F0F0>";

                if ($row) {
          while(is_array($row)){
                    echo $row["author"]."<<a href=\"mailto:$row[email]\">$row[email]</a>><br>";
                    $row=$q->getrow();
          }
                } else {
                    echo "No messages";
                }

                echo "</td></tr>";

            }

/* End of table */

            echo "</table>";
    }
  }
?>
</form>
Thanks to Rich Davey for most of this script.
</body>
</html>