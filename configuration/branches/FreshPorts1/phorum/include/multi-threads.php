<?php

 
  $t_gif="<IMG SRC=\"$forum_url/images/t.gif\" WIDTH=12 HEIGHT=21 BORDER=0>";
  $l_gif="<IMG SRC=\"$forum_url/images/l.gif\" WIDTH=12 HEIGHT=21 BORDER=0>";
  $p_gif="<IMG SRC=\"$forum_url/images/p.gif\" WIDTH=9 HEIGHT=21 BORDER=0>";
  $m_gif="<IMG SRC=\"$forum_url/images/m.gif\" WIDTH=9 HEIGHT=21 BORDER=0>";
  $c_gif="<IMG SRC=\"$forum_url/images/c.gif\" WIDTH=9 HEIGHT=21 BORDER=0>";
  $i_gif="<IMG SRC=\"$forum_url/images/i.gif\" WIDTH=12 HEIGHT=21 BORDER=0>";
  $n_gif="<IMG SRC=\"$forum_url/images/n.gif\" WIDTH=9 HEIGHT=21 BORDER=0>";
  $space_gif="<IMG SRC=\"$forum_url/images/trans.gif\" WIDTH=5 HEIGHT=21 BORDER=0>";
  $trans_gif="<IMG SRC=\"$forum_url/images/trans.gif\" WIDTH=12 HEIGHT=21 BORDER=0>";
  
  function echo_data($image, $topic, $row_color){
    GLOBAL $ForumTableWidth,$ForumTableHeaderColor,$ForumTableHeaderFontColor;
    GLOBAL $ForumTableBodyColor1,$ForumTableBodyFontColor1,$ForumTableBodyColor2,$ForumTableBodyFontColor2;
    GLOBAL $read_page,$ext,$collapse,$id,$UseCookies;
    GLOBAL $space_gif,$num,$old_message,$haveread,$use_haveread;
    GLOBAL $lNew, $GetVars;
    $thread_total="";
    
    if(($row_color%2)==0){
      $bgcolor=$ForumTableBodyColor1;
      $font_color=$ForumTableBodyFontColor1;
    }
    else{
      $bgcolor=$ForumTableBodyColor2;
      $font_color=$ForumTableBodyFontColor2;
    }  

    $subject ="<TABLE CELLSPACING=0 CELLPADDING=0 BORDER=0";
    if($bgcolor!=""){
        $subject.=" BGCOLOR=\"".$bgcolor."\"";
    }
    $subject.=">\n";
    $subject.="<TR>\n<TD>";
    $subject.=$space_gif;
    $subject.=$image."</TD>\n<TD><FONT COLOR=\"$font_color\">&nbsp;";

    if($id==$topic["id"] && $read=true){
        $subject .= "<b>".$topic["subject"]."</b>";
        $author = "<b>".$topic["author"]."</b>";
        $datestamp = "<b>".date_format($topic["datestamp"])."</b>";
    }
    else{
        $subject.="<a href=\"$read_page.$ext?f=$num&i=".$topic["id"];
        $subject.="&t=".$topic["thread"]."$GetVars\">".$topic["subject"]."</a>";
        $author = $topic["author"];
        $datestamp = date_format($topic["datestamp"]);
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
        $subject.="<font face=\"MS Sans Serif,Geneva\" size=\"-2\" color=\"#FF0000\">".$lNew."</font>";
      }
    }

    $subject.="</TD>\n</TR>\n</TABLE>";
?>  
<TR VALIGN=middle>
<TD<?PHP echo bgcolor($bgcolor);?>><?php echo $subject;?></TD>
<TD<?PHP echo bgcolor($bgcolor);?> nowrap><FONT COLOR="<?php echo $font_color;?>"><?php echo $author;?></FONT></TD>
<TD<?PHP echo bgcolor($bgcolor);?> nowrap><FONT SIZE="-1" COLOR="<?php echo $font_color;?>"><?php echo $datestamp;?>&nbsp;</FONT></TD>
</TR>
<?php
  }

  function thread($seed=0){

    GLOBAL $row_color_cnt;
    GLOBAL $messages,$threadtotal;
    GLOBAL $font_color, $bgcolor;
    GLOBAL $t_gif,$l_gif,$p_gif,$m_gif,$c_gif,$i_gif,$n_gif,$trans_gif;    
    
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
    if(!$read){
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
<TABLE WIDTH="<?php echo $ForumTableWidth;?>" CELLSPACING=0 CELLPADDING=0 BORDER=0>
<TR>
    <TD HEIGHT=21<?PHP echo bgcolor($ForumTableHeaderColor);?> WIDTH="100%"><FONT COLOR="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $lTopics;?></FONT></TD>
    <TD HEIGHT=21<?PHP echo bgcolor($ForumTableHeaderColor);?> NOWRAP WIDTH=150><FONT COLOR="<?php echo $ForumTableHeaderFontColor; ?>"><?php echo $lAuthor;?>&nbsp;</FONT></TD>
    <TD HEIGHT=21<?PHP echo bgcolor($ForumTableHeaderColor);?> NOWRAP WIDTH=100><FONT COLOR="<?php echo $ForumTableHeaderFontColor; ?>"><?php echo $lDate;?></FONT></TD>
</TR>
<?php
  thread();
?>
</TABLE>
