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

  if($num==0 || $ForumName==''){
    Header("Location: $forum_url/$forum_page.$ext?$GetVars");
    exit;
  }

  if (!isset($search)){
    $search="";
  }

  $search=trim(stripslashes($search));

  $searchtext = htmlentities($search);

  if(!isset($fldauthor) && !isset($fldsubject) && !isset($fldbody)){
    $fields[] = "subject";
    $fields[] = "author";
    $fldauthor=1;
    $fldsubject=1;
    $fldbody=0;
  }
  else{
    isset($fldauthor) ? $fields[] = "author" : $fldauthor=0;
    isset($fldsubject) ? $fields[] = "subject" : $fldsubject=0;
    isset($fldbody) ? $fields[] = "body" : $fldbody=0;
  }
  
  if(!isset($date)){
    $date=30;
  }

  if(count($forums)>1){
    $nav = "<div class=nav><a href=\"$forum_page.$ext?f=$Parent$GetVars\"><font color='$ForumNavFontColor'>".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;</font></div>";
  }
  else{
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'><a href=\"$post_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;</font></div>";
  }

  if($search!=""){
    if(isset($x)){
      list($id,$action,$start_num,$top_id)=explode(",", $x);
      if($action==1) $prevtopid=$top_id;
    }
    $tokNum = 0;
    $tokens = array();
    if($match!=3){
      //Build Tokens.  There's a gawd awful
      //way of doing this with a regex, but it's messy  
      $InQuotedString = 0;
      $params = split(" ", $search);
      $tokens[$tokNum] = "";
      for($i=0; $i<count($params); $i++){
        if(!IsSet($tokens[$tokNum])){
          $tokens[$tokNum] = "";
        }
        $param = $params[$i];
        if(ereg("^\"", $param) || ereg("^[+-]\"", $param)){
          $InQuotedString = 1;
        }
        if($InQuotedString == 1){
          $tokens[$tokNum] .= ereg_replace("\"", "", $param) . " ";
        }
        else{
          $tokens[$tokNum++] = $param;
        }
  
        if(ereg("\"$", $param)){
          $InQuotedString = 0;
          $tokens[$tokNum] = chop($tokens[$tokNum]);
  //        echo "\n<!--".$tokens[$tokNum]."-->\n";
          $tokNum++;
        }    
      }
    }
    else{
      $tokens[$tokNum] = ereg_replace("\"", "", chop($search));  
    }

    echo "<p>";

    $SQL="";
    
    if($id==0){
      $SQL = "select $ForumTableName.id, $ForumTableName.thread, author, subject, datestamp, body from $ForumTableName, $ForumTableName"."_bodies where $ForumTableName.id = $ForumTableName"."_bodies.id and $ForumTableName.approved='Y' AND (";
    }
    elseif($action==1){    
      $SQL = "select $ForumTableName.id, $ForumTableName.thread, author, subject, datestamp, body from $ForumTableName, $ForumTableName"."_bodies where $ForumTableName.id = $ForumTableName"."_bodies.id and $ForumTableName.id<$id and $ForumTableName.approved='Y' AND (";    
    }
    else{    
      $SQL = "select $ForumTableName.id, $ForumTableName.thread, author, subject, datestamp, body from $ForumTableName, $ForumTableName"."_bodies where $ForumTableName.id = $ForumTableName"."_bodies.id and $ForumTableName.id<=$top_id and $ForumTableName.approved='Y' AND (";    
    }
    
    if($date!=0){
      $cutoff=date("Y-m-d", mktime(0,0,0,date("m"),date("d")-$date));
      $SQL .= " datestamp >= '$cutoff' ) AND (";
    }
    
    for($i=0; $i<count($tokens); $i++){
      for($x=0; $x<count($fields); $x++){
        $token = ereg_replace(" $", "", $tokens[$i]);
        if(ereg("^\\+", $token)){
          $token = ereg_replace("^\\+", "", $token);
          $SQL .= "$fields[$x] like '%$token%'";
          if($x<count($fields)-1){
             $SQL .= " OR ";
          }
         }
        elseif(ereg("^\\-", $token)){
          $token = ereg_replace("^\\-", "", $token);
          $SQL .= "$fields[$x] NOT like '%$token%'";
          if($x<count($fields)-1){
            if($match==1){
              $SQL .= ") AND (";
            }
            else{
              $SQL .= ") OR (";
            }
          }
        }
        else{
          $SQL .= "$fields[$x] like '%$token%'";
          if($x<count($fields)-1){
            $SQL .= " OR ";
          }
        }
      }
      if($i<count($tokens)-1){
        if($match==1){
          $SQL .= ") AND (";
        }
        else{
          $SQL .= ") OR (";
        }
      }
      else{
        $SQL .= ")";
      }
    }

    if($DB->type=="postgresql"){
      $limit="";
    }
    else{
      $limit=" limit 20";
    }

    $SQL .= " order by id desc".$limit;
  
    echo "\n<!--$SQL-->\n";

    $q->query($DB, $SQL);
    
    if($err=$q->error()){
      echo $err;
    }

    $rows = $q->numrows();
  }
    $sTitle=" search";
  if(file_exists("$include_path/header_$ForumTableName.php")){
    include "$include_path/header_$ForumTableName.php";    
  }
  else{
    include "$include_path/header.php";
  }
?>
<?PHP
  if($search!=""){
?>
<p>
<table width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><?PHP echo $nav; ?></td>
  </tr>
</table>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="4" border="0">
  <tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lSearchResults;?></font></td>
  </tr>
<tr><td width="<?PHP echo $ForumTableWidth; ?>" valign="TOP" <?PHP echo bgcolor($ForumTableBodyColor2); ?>>
<?PHP
    if($rows>0){
      $message = $q->getrow();
      $count=$start_num;
      While(is_array($message)){
        $count++;        
        if(!isset($top_id)){
          $top_id=$message["id"];
        }
        echo "<dl><dt><b>$count. </b><a href=\"$read_page.$ext?f=$num&i=".$message["id"]."&t=".$message["thread"]."$GetVars\"><b>".chop($message["subject"])."</b></a> - ".chop($message["author"])."<br>\n<dd>";
        $text=ereg_replace("<[^>]*>", "", chop(substr($message["body"], 0, 200)));
        $text=ereg_replace("^<[^>]*>", "", $text);
        $text=ereg_replace("<[^>]*>$", "", $text);
        echo $text."<br>";
        echo "<font size=-2>$lDate: ".date_format($message["datestamp"])."</font><br>\n";
        echo "</dl><p>\n";
        $last_id=$message["id"];
        $message = $q->getrow();
      }
    }
    else{
      echo $lNoMatches;
    }
    $prevmatch='';
    $morematch='';
    if($start_num >= 20){
      $topid=$top_id+1;
      $start_num=$start_num-20;
      $prevmatch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&x=$prevtopid,2,$start_num,$top_id$GetVars\"><FONT color=\"$ForumNavFontColor\">$lPrevMatches</font></a>";
    }
    if($rows>=20){
      $morematch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&x=$last_id,1,$count,$top_id$GetVars\"><FONT color=\"$ForumNavFontColor\">$lMoreMatches</font></a>";
    }
    if($prevmatch || $morematch){
      echo "<center><br><br><div class=nav><FONT color=\"$ForumNavFontColor\"><b>";
      if($prevmatch) echo $prevmatch;
      if($prevmatch && $morematch) echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
      if($morematch) echo $morematch;        
      echo "</font></div><br><br></center>";
    }
?>
</font>
</td></tr></table>
<?PHP    
  }
?>
<form action="<?PHP echo "$search_page.$ext"; ?>" method="GET">
<?PHP echo $PostVars; ?>
<input type="Hidden" name="f" value="<?PHP echo $num; ?>">
<table width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><?PHP echo $nav; ?></td>
  </tr>
</table>
<table  width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lSearch;?></font></td>
  </tr>
  <tr>
    <td align="CENTER" valign="MIDDLE" <?PHP echo bgcolor($ForumTableBodyColor2); ?>>
    <br>
<table cellspacing="0" cellpadding="2" border="0">
<tr>
    <td align="right">&nbsp;&nbsp;Search:&nbsp;&nbsp;</td>
    <td><input type="Text" name="search" size="30" value="<?PHP echo $searchtext; ?>">&nbsp;<input type="Submit" value="<?PHP echo $lSearch;?>">&nbsp;&nbsp;</td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><select name="match"><option value="1" <? if($match==1) echo "selected"; ?>>All Words</option><option value="2" <? if($match==2) echo "selected"; ?>>Any Word</option><option value="3" <? if($match==3) echo "selected"; ?>>Exact Phrase</option></select>&nbsp;&nbsp;&nbsp;&nbsp;<select name="date"><option value="30" <? if($date==30) echo "selected"; ?>>Last 30 Days</option><option value="60" <? if($date==60) echo "selected"; ?>>Last 60 Days</option><option value="90" <? if($date==90) echo "selected"; ?>>Last 90 Days</option><option value="180" <? if($date==180) echo "selected"; ?>>Last 180 Days</option><option value="0" <? if($date==0) echo "selected"; ?>>All Dates</option></select></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><input type="checkbox" name="fldauthor" value="1" <? if($fldauthor==1)  echo "checked"; ?>> <? echo $lAuthor; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldsubject" value="1" <? if($fldsubject==1)  echo "checked"; ?>> <? echo $lFormSubject; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldbody" value="1" <? if($fldbody==1)  echo "checked"; ?>> <? echo $lMessageBodies; ?>&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>
<br></td>
</td>
</tr>
</table>
</form>
<p>
<table width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lSearchTips;?></font></td>
  </tr>
  <tr>
    <td width="<?PHP echo $ForumTableWidth; ?>" align="LEFT" valign="MIDDLE" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><?PHP echo $lTheSearchTips;?><br></td>
  </tr>
</table>
<?PHP

  if(file_exists("$include_path/footer_$ForumTableName.php")){
    include "$include_path/footer_$ForumTableName.php";    
  }
  else{
    include "$include_path/footer.php";
  }

?>
