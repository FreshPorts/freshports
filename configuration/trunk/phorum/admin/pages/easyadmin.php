<?PHP /* Easy Admin */ ?>
<?PHP 

// Set up variables

  $t_gif='<IMG SRC="../images/t.gif" WIDTH=12 HEIGHT=21 BORDER=0>';
  $l_gif='<IMG SRC="../images/l.gif" WIDTH=12 HEIGHT=21 BORDER=0>';
  $p_gif='<IMG SRC="../images/p.gif" WIDTH=9 HEIGHT=21 BORDER=0>';
  $m_gif='<IMG SRC="../images/m.gif" WIDTH=9 HEIGHT=21 BORDER=0>';
  $c_gif='<IMG SRC="../images/c.gif" WIDTH=9 HEIGHT=21 BORDER=0>';
  $i_gif='<IMG SRC="../images/i.gif" WIDTH=12 HEIGHT=21 BORDER=0>';
  $n_gif='<IMG SRC="../images/n.gif" WIDTH=9 HEIGHT=21 BORDER=0>';
  $space_gif='<IMG SRC="../images/trans.gif" WIDTH=5 HEIGHT=21 BORDER=0>';
  $trans_gif='<IMG SRC="../images/trans.gif" WIDTH=12 HEIGHT=21 BORDER=0>';

  $cutoff = 800; // See the faq.

  if (isset($mythread)) $thread = $mythread;

  if (!isset($navigate)) $navigate = 0;


// Declare functions

  function echo_data($image, $topic, $row_color){
    GLOBAL $read_page,$ext,$id,$myname,$max;
    GLOBAL $space_gif,$num,$old_message,$navigate;
    $thread_total="";
    
    if(($row_color%2)==0){
      $bgcolor="#c0c0c0";
      $font_color="#000000";
    }
    else{
      $bgcolor="#FFFFFF";
      $font_color="#000000";
    }  

    $subject ="<TABLE CELLSPACING=0 CELLPADDING=0 BORDER=0";
    if($bgcolor!=""){
        $subject.=" BGCOLOR=\"".$bgcolor."\"";
    }
    $subject.=">\n";
    $subject.="<TR>\n<TD>";
    $subject.=$space_gif;
    $subject.=$image."</TD>\n<TD><FONT face=\"Arial,Helvetica\" COLOR=\"$font_color\">&nbsp;";

    $subject.="<a href=\"../$read_page.$ext?admview=1&f=$num&i=".$topic["id"];
    $subject.="&t=".$topic["thread"]."\">".$topic["subject"]."</a>";
    $author = $topic["author"];
    $approved = $topic["approved"];
    $datestamp = date_format($topic["datestamp"]);

    $subject.="&nbsp;&nbsp;</font>";

    $subject.="</TD>\n</TR>\n</TABLE>";

?>  
<TR VALIGN=middle>
<TD bgcolor=<?PHP echo $bgcolor; ?>><?php echo $subject;?></TD>
<TD bgcolor=<?PHP echo $bgcolor; ?> nowrap><FONT face="Arial,Helvetica" COLOR="<?php echo $font_color;?>"><?php echo $author;?></FONT></TD>
<TD bgcolor=<?PHP echo $bgcolor; ?> nowrap><FONT face="Arial,Helvetica" SIZE="-2" COLOR="<?php echo $font_color;?>"><?php echo $datestamp;?>&nbsp;</FONT></TD>
<TD bgcolor=<?PHP echo $bgcolor; ?> nowrap><FONT face="Arial,Helvetica" SIZE="-2" COLOR="<?php echo $font_color;?>"><a href="<?PHP echo $myname; ?>?page=easyadmin&action=del&type=quick&id=<?php echo $topic["id"]; ?>&num=<?PHP echo $num; ?>&navigate=<?PHP echo $navigate; ?>&thread=<?PHP echo $max; ?>">Delete</A>&nbsp;|&nbsp;<a href="<?PHP echo $myname; ?>?page=edit&srcpage=easyadmin&id=<?php echo $topic["id"]; ?>&num=<?PHP echo $num; ?>&navigate=<?PHP echo $navigate; ?>&mythread=<?PHP echo $max; ?>">Edit</A>&nbsp;|&nbsp;<a href="<?PHP echo $myname; ?>?page=easyadmin&action=moderate&approved=<?php echo $approved; ?>&id=<?php echo $topic["id"]; ?>&num=<?PHP echo $num; ?>&navigate=<?PHP echo $navigate; ?>&mythread=<?PHP echo $max; ?>"><?php if ($approved == 'Y') { echo "Hide"; } else { echo "Approve"; } ?></A></FONT></TD>
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

// Begin main()

  if($DB->type=="postgresql"){
    $limit="";
    $q->query($DB, "set QUERY_LIMIT TO '$ForumDisplay'");
  }
  else{
    $limit=" limit $ForumDisplay";
  }

  if($thread==0 || $navigate==0){
    $sSQL = "Select max(thread) as thread from $ForumTableName";
    $q->query($DB, $sSQL);
    if($q->numrows()>1){
      $maxthread=$q->field("thread", 0);
    }
    else{
      $maxthread=0;
    }
    $cutoff_thread=$maxthread-$cutoff;
    $sSQL = "Select thread from $ForumTableName where thread > $cutoff_thread order by thread desc".$limit;
  }
  else{
    if($navigate==1){
      $cutoff_thread=$thread+$cutoff;
      $sSQL = "Select thread from $ForumTableName where thread < $cutoff_thread and thread > $thread order by thread".$limit;
      $q=new query($DB, $sSQL);
      if($rows=$q->numrows()){
        $thread = $q->field("thread",$rows-1);
      }
      $thread=$thread+1;
    }    
    $cutoff_thread=$thread-$cutoff;
    $sSQL = "Select thread from $ForumTableName where thread < $thread and thread > $cutoff_thread order by thread desc".$limit;
  }
  $thread_list = new query($DB, $sSQL);
  if($DB->type=="postgresql"){
    $q->query($DB, "set QUERY_LIMIT TO '0'");
  }
  $rows = $thread_list->numrows();

// This needs to be fixed...
//  if($rows==0 && $navigate!=0){
//    Header("Location: $list_page.$ext?num=$num$GetVars");
//    exit();
//  }

  $rec=$thread_list->getrow();
  if(isset($rec['thread'])){
    $max = $thread_list->field("thread", 0);
    $min = $thread_list->field("thread", $rows-1);
  }
  else{
    $max=0;
    $min=0;
  }

  $sSQL = "Select id,parent,thread,subject,author,datestamp,approved from $ForumTableName where thread<=$max and thread>=$min order by thread desc, id asc";

  $msg_list = new query($DB, $sSQL);

  $row=$msg_list->firstrow();

  if(is_array($row)){
    if(!$read){
      $rec=$thread_list->firstrow();
      while(is_array($rec)){
        $thd=$rec["thread"];
        if(!isset($rec["tcount"])) $rec["tcount"]=0;
        $tcount=$rec["tcount"];
        $threadtotal[$thd]=$tcount;
        $rec=$thread_list->getrow();
      }
    }
    else{
      $threadtotal[$thread]=$msg_list->numrows();
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
      $row=$msg_list->getrow();
    }
  }
  
?>
<TABLE WIDTH="100%" CELLSPACING=0 CELLPADDING=0 BORDER=0>
<TR>
    <TD HEIGHT=21 bgcolor="#000080"><FONT face="Arial,Helvetica" COLOR="#FFFFFF">&nbsp;<?php echo $lTopics;?></FONT></TD>
    <TD HEIGHT=21 bgcolor="#000080" NOWRAP WIDTH=150><FONT face="Arial,Helvetica" COLOR="#FFFFFF"><?php echo $lAuthor;?>&nbsp;</FONT></TD>
    <TD HEIGHT=21 bgcolor="#000080" NOWRAP WIDTH=40><FONT face="Arial,Helvetica" COLOR="#FFFFFF"><?php echo $lDate;?></FONT></TD>
    <TD HEIGHT=21 bgcolor="#000080" NOWRAP WIDTH=40><FONT face="Arial,Helvetica" COLOR="#FFFFFF">Actions</FONT></TD>
</TR>
<?php
  thread();
?>
</TABLE>

<table width="100%" cellspacing="0" cellpadding="3" border="0">
<tr>
  <td align="right" bgcolor="#000080"><FONT face="Arial,Helvetica" color="#FFFFFF">&nbsp;<a href="<?PHP echo $myname; ?>?page=easyadmin&num=<?PHP echo $num; ?>&thread=<?PHP echo $max; ?>&navigate=1"><FONT face="Arial,Helvetica" color="#FFFFFF"><?PHP echo $lNewerMessages;?></font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?PHP echo $myname; ?>?page=easyadmin&num=<?PHP echo $num; ?>&thread=<?PHP echo $min; ?>&navigate=-1"><FONT face="Arial,Helvetica" color="#FFFFFF"><?PHP echo $lOlderMessages;?></font></a>&nbsp;</font></td>
</tr>
</table>
