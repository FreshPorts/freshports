<?PHP
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of the Phorum License Version 1.0.                    //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   You should have received a copy of the Phorum License                    //
//   along with this program.                                                 //
////////////////////////////////////////////////////////////////////////////////

  require "./common.php";

  if($ActiveForums==1){
    $sSQL="Select id, folder from $pho_main where active=1";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    if($rec["folder"]==0){
      header("Location: $forum_url/$list_page.$ext?f=$rec[id]$GetVars");
      exit();
    }
  }

  $title = $lForumList;
  if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
    include "$include_path/header_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/header.php";
  }
  if($f!=0){
    $level='';
    if($ForumParent!=0){
      $level="f=$ForumParent";
    }
    $table_width=$ForumTableWidth;
    $table_header_color=$ForumTableHeaderColor;
    $table_header_font_color=$ForumTableHeaderFontColor;
    $table_body_color_1=$ForumTableBodyColor1;
    $table_body_font_color_1=$ForumTableBodyFontColor1;
    $nav_color=$ForumNavColor;
    $nav_font_color=$ForumNavFontColor;
?>
<table width="<?PHP echo $table_width; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($nav_color); ?> valign="TOP" nowrap><div class=nav><a href="<?PHP echo "$forum_page.$ext?$level$GetVars"; ?>"><font color="<?PHP echo $nav_font_color; ?>"><?PHP echo $lUpLevel; ?></font></a></font></div></td>
  </tr>
</table>
<?PHP
  }
  else{
    $table_width=$default_table_width;
    $table_header_color=$default_table_header_color;
    $table_header_font_color=$default_table_header_font_color;
    $table_body_color_1=$default_table_body_color_1;
    $table_body_font_color_1=$default_table_body_font_color_1;
    $nav_color=$default_nav_color;
    $nav_font_color=$default_nav_font_color;
  }
?>
<table width="<?PHP echo $table_width; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td width="100%" colspan=2 <?PHP echo bgcolor($table_header_color); ?>><FONT color="<?PHP echo $table_header_font_color; ?>">&nbsp;<?PHP echo $lAvailableForums;?></font></td>
</tr>
<?PHP
  if(isset($q)){
    $sSQL="Select id, name, table_name, parent, folder, description from ".$pho_main." where active=1 and parent=$f";
    if($SortForums) $sSQL.=" order by name";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
  } else {
    $rec = "";
  }
  if(is_array($rec)){
    while(is_array($rec)){
      $empty=false;
      $name=$rec["name"];
      $num=$rec["id"];
      if(!$rec["folder"]){
        $sSQL="select count(*) as posts from $rec[table_name] where approved='Y'";
        $tq = new query($DB, $sSQL);
        if($tq->numrows()){
          $trec=$tq->getrow();
          $num_posts=$trec["posts"];
        }
        else{
          $num_posts='0';
        }
        $sSQL="select max(datestamp) as max_date from $rec[table_name] where approved='Y'";
        $tq->query($DB, $sSQL);
        $trec=$tq->getrow();
        if(empty($trec["max_date"])){
          $last_post_date="";
        }
        else{
          $last_post_date=date_format($trec["max_date"]);
        }
      }
      echo "<tr bgcolor=\"$table_body_color_1\"><td width=\"60%\">";
      echo "<div class=forum><FONT size=4 color=\"$table_body_font_color_1\">";
      if($rec["folder"]){
        echo "<b>&nbsp;<a href=\"$forum_page.$ext?f=$num$GetVars\">$name</a></b></font></div></td>";
/*  Whoever added this did a bad job.
        $sSQL="SELECT id,name FROM ".$pho_main." WHERE parent=$rec[id] AND active=1";
        $aq=new query($DB, $sSQL);
        $aq->query($DB, $sSQL);
        while ($anchor=$aq->getrow()) {
          echo "&nbsp";
          echo "<a style='a {text-decoration:none; color:#800000}'";
          echo "href='list.php?f=";
          echo $anchor["id"];
          echo "'>";
          echo $anchor["name"];
          echo "</a>, ";
        }
*/
        echo "<td width=\"40%\"><font size=-1 color=\"$table_body_font_color_1\">&nbsp;&nbsp;$lForumFolder</font></td></tr>";
      }
      else{
        echo "<b>&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\">$name</a></b></font></div></td>";
        echo "<td width=\"40%\"><font size=-1 color=\"$table_body_font_color_1\">&nbsp;&nbsp;$lNumPosts: <b>$num_posts</b>&nbsp;&nbsp;&nbsp;&nbsp;$lLastPostDate: <b>$last_post_date</b></font></td></tr>";
      }
      echo "<tr bgcolor=\"$table_body_color_1\"><td colspan=2>";
      echo "<dl><dt><dd><font size=-1 color=\"$table_body_font_color_1\">&nbsp;";
      echo $rec["description"];
      echo "</font></dl></td></tr>\n";
      $rec=$q->getrow();
    }
?>
<?PHP
  }
  else{
?>
<tr>
    <td width="100%" colspan=2 <?PHP echo bgcolor($table_body_color_1); ?>><FONT color="<?PHP echo $table_body_font_color_1; ?>">&nbsp;<?PHP echo $lNoActiveForums;?></font></td>
</tr>
<?PHP
  }
?>
</table>
<?PHP
  if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
    include "$include_path/footer_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/footer.php";
  }
?>
