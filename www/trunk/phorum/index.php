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
    $sSQL="Select id from forums where active=1";
    $q->query($DB, $sSQL);
    $rec=$q->getrow();
    header("Location: $list_page.$ext?f=$rec[id]$GetVars");
  	exit();
  }

  $title = $lForumList;
  if(file_exists("$include_path/header_$TableName.php")){
    include "$include_path/header_$TableName.php";
  }
  else{
    include "$include_path/header.php";
  }
  if($f!=0){
    $level='';
    if($ForumParent!=0){
      $level="f=$ForumParent";
    }    
    $nav="<div class=nav><a href=\"$forum_page.$ext?$level$GetVars\"><font color='$default_nav_font_color'>".$lUpLevel."</font></a></font></div>";
  }
  else{
    $nav='';
  }
?>
<? if($nav!=''){ ?>
<table width="<?PHP echo $default_table_width; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($default_nav_color); ?> valign="TOP" nowrap><?PHP echo $nav; ?></td>
  </tr>
</table>
<? } ?>
<table width="<?PHP echo $default_table_width; ?>" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td width="100%" colspan=2 <?PHP echo bgcolor($default_table_header_color); ?>><FONT color="<?PHP echo $default_table_header_font_color; ?>">&nbsp;<?PHP echo $lAvailableForums;?></font></td>
</tr>
<?PHP
  $sSQL="Select id, name, table_name, parent, folder, description from forums where active=1 and parent=$f";
  if($SortForums) $sSQL.=" order by name";
  $q->query($DB, $sSQL);
  if($q->numrows()){
    while($rec=$q->getrow()){
      $empty=false;
      $name=$rec["name"];
      $num=$rec["id"];
      if(!$rec["folder"]){
        $sSQL="select count(*) as posts from $rec[table_name] where approved='Y'";
        $tq = new query($DB, $sSQL);
        if($tq->numrows()){
          $num_posts=$tq->field("posts", 0);
        }
        else{
          $num_posts='0';
        }        
        $sSQL="select max(datestamp) as max_date from $rec[table_name] where approved='Y'";
        $tq->query($DB, $sSQL);
        $last_post_date=$tq->field("max_date", 0);
        if($last_post_date==0){
          $last_post_date=date_format("0000-00-00");
        }
        else{
          $last_post_date=date_format($last_post_date);
        }
      }
      echo "<tr bgcolor=\"$default_table_body_color_1\"><td width=\"60%\">";
      echo "<div class=forum><FONT size=4 color=\"$default_table_body_font_color_1\">";
      if($rec["folder"]){
        echo "<b>&nbsp;<a href=\"$forum_page.$ext?f=$num$GetVars\">$name</a></b></font></div></td>";
        echo "<td width=\"40%\"><font size=-1 color=\"$default_table_body_font_color_1\">&nbsp;&nbsp;$lForumFolder</font></td></tr>";
      }
      else{
        echo "<b>&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\">$name</a></b></font></div></td>";
        echo "<td width=\"40%\"><font size=-1 color=\"$default_table_body_font_color_1\">&nbsp;&nbsp;$lNumPosts: <b>$num_posts</b>&nbsp;&nbsp;&nbsp;&nbsp;$lLastPostDate: <b>$last_post_date</b></font></td></tr>";
      }
      echo "<tr bgcolor=\"$default_table_body_color_1\"><td colspan=2>";
      echo "<dl><dt><dd><font size=-1 color=\"$default_table_body_font_color_1\">&nbsp;";
      echo $rec["description"];
      echo "</font></dl></td></tr>\n";    
    }
?>
<?PHP
  }
  else{
?>
<tr>
    <td width="100%" colspan=2 <?PHP echo bgcolor($default_table_body_color_1); ?>><FONT color="<?PHP echo $default_table_body_font_color_1; ?>">&nbsp;<?PHP echo $lNoActiveForums;?></font></td>
</tr>
<?PHP
  }
?>
</table>
<?PHP 
  if(file_exists("$include_path/footer_$TableName.php")){
    include "$include_path/footer_$TableName.php";    
  }
  else{
    include "$include_path/footer.php";
  }
?>