<?php

  $t_gif="<img src=\"$forum_url/images/t.gif\" width=\"12\" height=\"21\" border=\"0\">";
  $l_gif="<img src=\"$forum_url/images/l.gif\" width=\"12\" height=\"21\" border=\"0\">";
  $p_gif="<img src=\"$forum_url/images/p.gif\" width=\"9\" height=\"21\" border=\"0\">";
  $m_gif="<img src=\"$forum_url/images/m.gif\" width=\"9\" height=\"21\" border=\"0\">";
  $c_gif="<img src=\"$forum_url/images/c.gif\" width=\"9\" height=\"21\" border=\"0\">";
  $i_gif="<img src=\"$forum_url/images/i.gif\" width=\"12\" height=\"21\" border=\"0\">";
  $n_gif="<img src=\"$forum_url/images/n.gif\" width=\"9\" height=\"21\" border=\"0\">";
  $space_gif="<img src=\"$forum_url/images/trans.gif\" width=\"5\" height=\"21\" border=\"0\">";
  $trans_gif="<img src=\"$forum_url/images/trans.gif\" width=\"12\" height=\"21\" border=\"0\">";

  function echo_data($image, $topic, $row_color){
    global $ForumTableWidth,$ForumTableHeaderColor,$ForumTableHeaderFontColor;
    global $ForumTableBodyColor1,$ForumTableBodyFontColor1,$ForumTableBodyColor2,$ForumTableBodyFontColor2;
    global $read_page,$ext,$collapse,$id,$UseCookies,$phflat,$$phflat;
    global $space_gif,$num,$old_message,$haveread,$use_haveread;
    global $lNew, $GetVars, $users, $moderators;
    $thread_total="";

    if(($row_color%2)==0){
      $bgcolor=$ForumTableBodyColor1;
      $font_color=$ForumTableBodyFontColor1;
    }
    else{
      $bgcolor=$ForumTableBodyColor2;
      $font_color=$ForumTableBodyFontColor2;
    }

    $subject ="<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" ".bgcolor($bgcolor).">\n";
    $subject.="<tr>\n<td>";
    $subject.=$space_gif;
    $subject.=$image."</td>\n<td><font color=\"$font_color\">&nbsp;";

    if(!empty($users[$topic["userid"]])){
        $author=$users[$topic["userid"]]["name"];
        if(isset($moderators[$topic["userid"]])){
            $author="<b>$author</b>";
        }
    } else {
        $author=chop($topic["author"]);
    }

    $datestamp = date_format($topic["datestamp"]);

    if($id==$topic["id"] && $read=true){
        $subject .= "<b>".$topic["subject"]."</b>";
        $author = "<b>".$author."</b>";
        $datestamp = "<b>".$datestamp."</b>";
    }
    else{
        $subject.="<a href=\"$read_page.$ext?f=$num&i=".$topic["id"];
	$reply_name='';
	if ($$phflat) {
	  $reply_name="#reply_".$topic["id"];
	}
        $subject.="&t=".$topic["thread"]."$GetVars$reply_name\">".$topic["subject"]."</a>";
    }

    $subject.="&nbsp;&nbsp;</font>";

    if($UseCookies){
      $isnew=false;
      if($use_haveread==true){
        if ($old_message<$topic["id"]) {
          if(!IsSet($haveread[$topic["id"]])) $isnew=true;
        }
      }
      elseif($old_message<$topic["id"]){
        $isnew=true;
      }
      if($isnew){
        $subject.="<font class=\"PhorumNewFlag\">".$lNew."</font>";
      }
    }

    $subject.="</td>\n</tr>\n</TABLE>";
?>
<tr valign=middle>
<td class="PhorumListRow" <?php echo bgcolor($bgcolor);?>><?php echo $subject;?></td>
<td class="PhorumListRow" <?php echo bgcolor($bgcolor);?> nowrap><font color="<?php echo $font_color;?>"><?php echo $author;?></font></td>
<td class="PhorumListRow" <?php echo bgcolor($bgcolor);?> nowrap><font color="<?php echo $font_color;?>"><?php echo $datestamp;?>&nbsp;</font></td>
</tr>
<?php
  }

  function thread($seed=0){

    global $row_color_cnt;
    global $messages,$threadtotal;
    global $font_color, $bgcolor;
    global $t_gif,$l_gif,$p_gif,$m_gif,$c_gif,$i_gif,$n_gif,$trans_gif;

    $image="";
    $images="";

    if(!IsSet($row_color_cnt)){
      $row_color_cnt=0;
    }

    $row_color_cnt++;

    if($seed!="0"){
      $parent=$messages[$seed]["parent"];
      if($parent!=0){
        if(!IsSet($messages[$parent]["images"])){
          $messages[$parent]["images"]="";
        }
        $image=$messages[$parent]["images"];
        if($messages[$parent]["max"]==$messages[$seed]["id"]){
          $image.=$l_gif;
        }
        else{
          $image.=$t_gif;
        }
      }

      if(@is_array($messages[$seed]["replies"])){
        if(IsSet($messages[$parent]["images"])){
          $messages[$seed]["images"]=$messages[$parent]["images"];
          if($seed==$messages["$parent"]["max"]){
            $messages[$seed]["images"].=$trans_gif;
          }
          else{
            $messages[$seed]["images"].=$i_gif;
          }
        }
        $image.=$m_gif;
      }
      else{
        if($messages[$seed]["parent"]!=0){
          $image.=$c_gif;
        }
        else{
          if($threadtotal[$messages[$seed]["thread"]]>1){
            $image.=$p_gif;
          }
          else{
            $image.=$n_gif;
          }
        }
      }

      echo_data($image, $messages[$seed], $row_color_cnt);
    }//end of: if($seed!="0")

    if(@is_array($messages[$seed]["replies"])){
      $count=count($messages[$seed]["replies"]);
      for($x=1;$x<=$count;$x++){
        $key=key($messages[$seed]["replies"]);
        thread($key);
        next($messages[$seed]["replies"]);
      }
    }
  }

  @reset($headers);
  $row=@current($headers);

  if(is_array($row)){
    if(!initvar("read")){
      reset($threads);
      $rec=current($threads);
      while(is_array($rec)){
        $thd=$rec["thread"];
        if(!isset($rec["tcount"])) $rec["tcount"]=0;
        $tcount=$rec["tcount"];
        $threadtotal[$thd]=$tcount;
        $rec=next($threads);
      }
    }
    else{
      $threadtotal[$thread]=count($headers);
    }
    $topics["max"]="0";
    $topics["min"]="0";
    While(is_array($row)){
      $x="".$row["id"]."";
      $p="".$row["parent"]."";
      $messages["$x"]=$row;
      $messages["$p"]["replies"]["$x"]="$x";
      $messages["$p"]["max"]=$row["id"];
      if(!isset($messages["max"])) $messages["max"]=0;
      if(!isset($messages["min"])) $messages["min"]=0;
      if($messages["max"]<$row["thread"]) $messages["max"]=$row["thread"];
      if($messages["min"]>$row["thread"]) $messages["min"]=$row["thread"];
      $row=next($headers);
    }
  }

?>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth;?>" cellspacing="0" cellpadding="0" border="0">
<tr>
    <td class="PhorumListHeader"<?php echo bgcolor($ForumTableHeaderColor);?>><font color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $lTopics;?><img src="images/trans.gif" border="0" width=1 height=24 align="absmiddle"></font></td>
    <td class="PhorumListHeader"<?php echo bgcolor($ForumTableHeaderColor);?> nowrap width=150><font color="<?php echo $ForumTableHeaderFontColor; ?>"><?php echo $lAuthor;?>&nbsp;</font></td>
    <td class="PhorumListHeader"<?php echo bgcolor($ForumTableHeaderColor);?> nowrap width=150><font color="<?php echo $ForumTableHeaderFontColor; ?>"><?php echo $lDate;?></font></td>
</tr>
<?php
  thread();
?>
</TABLE>
