<?php
////////////////////////////////////////////////////////////////////
//CONGIG:
// You will need to change ./ to the path where phorum is located.
$phorumdir="./";
//Number of members to show per page
$topics_per_page="20";
//IMG PATHS:
//we would reccomend to download those images and upload them on your server.
//For AOL
$AolIMG="http://phorum.org/support/images/aol.gif";
//For Yahoo Messanger
$YahooIMG="http://phorum.org/support/images/yahoo.gif";
//For MSN
$MsnIMG="http://phorum.org/support/images/msn.gif";
//Lang:
$lNextPage="Next";
//END
////////////////////////////////////////////////////////////////////
//IF YOU DONT KNOW PHP, dont edit after that line                 //
////////////////////////////////////////////////////////////////////
//Get dir where its right now
$olddir=getcwd();
//Change dir to the one that specified in $phorumdir
chdir($phorumdir);
//We gotta have that
require "common.php";
//Sortbys
if (!isset($sortby))
$sortby = '';
switch ($sortby) {
  case '':
    $sortby = "id ASC";
    $sortlink = "";
  break;
  case 'name':
    $sortby = "name ASC";
    $sortlink = "name";
  break;
}
//For first page
if(!$start) $start = 0;


//HEADER
include phorum_get_file_name("header");

?>
<table border="0" cellpadding="1" cellspacing="0" valign="top" width="100%">
<tr><td align=right>
<?php
//Count users
$sSQL="select count(*) as users from $pho_main"."_auth";
$q->query($DB, $sSQL);
  if($q->numrows()){
    $trec=$q->getrow();
    $all_topics=$trec["users"];
  }
  else{
    $all_topics='0';
  }
//NEXT PAGE
$count = 1;
$next = $start + $topics_per_page;
if($all_topics > $topics_per_page) {
  if ($next < $all_topics) {
    echo "<font size=-1>\n<a href=\"memberlist.$ext?start=$next&sortby=$sortlink\">$lNextPage</a> | ";
  }
  for($x = 0; $x < $all_topics; $x++) {
    if(0 == ($x % $topics_per_page)){
      if($x == $start)
        echo "$count\n";
      else
        echo "<a href=\"memberlist.$ext?&start=$x&sortby=$sortlink\">$count</a>\n";

      $count++;
      if(!($count % 10))
        echo "<BR>";
    }
  }
}
$next = 0;
$ranking = $start;


?>

</td>
</tr>
</table>
<?PHP
//GET users from table
$sql="Select * from $pho_main"."_auth ORDER BY $sortby LIMIT $start, $topics_per_page";
$q->query($DB, $sql);
$rec=$q->getrow();
?>
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<TR <?PHP echo bgcolor($default_table_header_color); ?>>
  <td nowrap>&nbsp;</td>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>">&nbsp;<B><a href="<?php echo $PHP_SELF ?>?sortby=name&start=<?php echo $start ?>"><?php echo $lName ?></a></B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B><?php echo $lEmail ?></B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B><?php echo $lWebpage ?></B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B>ICQ</B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B>AOL</B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B>YAHOO</B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B>MSN</B></font></TD>
  <td height="25" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"><B>JABBER</B></font></TD>
</TR>
<?php
do{
  if ($rec['email']){
    $email = "<a href=\"".htmlencode("mailto:".$rec['email'])."\">".htmlencode($rec['email'])."</a>";
  }else{
    $email = "&nbsp;";
  }
  if ($rec['webpage']){
    $www = "<a href=\"$rec[webpage]\" target='blank'>Website</a>";
  }else{
    $www = "&nbsp;";
  }
  if ($rec['icq']){
    $icq = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=$rec[icq]\">$rec[icq]</a>";
  }else{
    $icq = "&nbsp;";
  }
  if ($rec['aol']){
    $aol = "<a href=\"aim:goim?screenname=$rec[aol]&message=Hi+$rec[aol].+Are+you+there?\"><img src=\"$AolIMG\" width=\"30\" height=\"17\" border=\"0\" alt=\"Aol $rec[aol]\"></a></TD>";
  }else{
    $aol = "&nbsp;";
  }
  if ($rec['yahoo']){
    $yahoo= "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=$rec[yahoo]&.src=pg\"><img src=\"$YahooIMG\" width=\"16\" height=\"16\" border=\"0\" alt=\"Yahoo $rec[yahoo]\"></a>";
  }else{
    $yahoo = "&nbsp;";
  }
  if ($rec['msn']){
    $msn = "<a href=\"profile.$ext?id=$rec[id]\"><img src=\"$MsnIMG\" width=\"16\" height=\"16\" border=\"0\" alt=\"MSN $rec[msn]\"></a>";
  }else{
    $msn = "&nbsp;";
  }
  if ($rec['jabber']){
    $jabber = $rec['jabber'];
  }else{
    $jabber = "&nbsp;";
  }
?>
        <tr>
                <td bgcolor="<?php echo $default_table_body_color_1?>" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>">&nbsp;<?php echo ++$ranking?>&nbsp;</font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap><font color="<?PHP echo $default_table_body_font_color_1; ?>">&nbsp;<a href="profile.<?php echo $ext?>?id=<?php echo $rec['id']?>"><?php echo $rec['name']?></a></font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $email?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $www?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $icq?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $aol?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $yahoo?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $msn?> </font></TD>
                <td bgcolor="<?php echo $default_table_body_color_1?>" height="30" nowrap align="center"><font color="<?PHP echo $default_table_body_font_color_1; ?>"> <?php echo $jabber?> </font></TD>
        </tr>
<?php
        $rec=$q->getrow();
        } while (is_array($rec));
        echo "</table> \n";

?>
<table border="0" cellpadding="1" cellspacing="0" valign="top" width="100%">
<tr><td align=right>
<?php

$count = 1;
$next = $start + $topics_per_page;
if($all_topics > $topics_per_page)
{
   if ($next < $all_topics)
   {
           echo "<font size=-1>\n<a href=\"memberlist.$ext?start=$next&sortby=$sortlink\">$lNextPage</a> | ";
   }
   for($x = 0; $x < $all_topics; $x++)
   {
      if(0 == ($x % $topics_per_page))
      {
                         if($x == $start)
                           echo "$count\n";
                         else
                           echo "<a href=\"memberlist.$ext?&start=$x&sortby=$sortlink\">$count</a>\n";

                         $count++;
                         if(!($count % 10))
                                 echo "<BR>";
      }
   }
}


echo "<BR>\n";
?>
</td></tr>
</table>
<?PHP
  include phorum_get_file_name("footer");

  chdir($olddir);
?>
