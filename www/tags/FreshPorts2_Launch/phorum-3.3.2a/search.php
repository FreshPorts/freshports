<?php
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of either the current Phorum License (viewable at     //
//   phorum.org) or the Phorum License that was distributed with this file    //
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

  include "$include_path/read_functions.php";

  /////////////////////////////////////////////////////////////////
  // build the search terms array
  // this will build the array to pass to build_sql()

  function build_search_terms($search, $match) {
    $terms=array();

    // if this is an exact phrase match
    if($match==3){
      $terms[]=$search;
    }
    // not exact phrase, break up the terms
    else{
      if ( strstr( $search, '"' ) ){
        //first pull out all the double quoted strings
        if(strstr($search, "\"")){
          $search_string=$search;
          while(ereg('-*"[^"]*"', $search_string, $match)){
            $terms[]=trim(str_replace("\"", "", $match[0]));
            $search_string=substr(strstr($search_string, $match[0]), strlen($match[0]));
          }
        }
        $search = ereg_replace('-*"[^"]*"', '', $search );
      }

      //pull out the rest words in the string
      $regular_terms = explode( " ", $search);

      //merge them all together and return
      while (list ($key, $val) = each ($regular_terms)) {
        if($val!="")
          $terms[]=trim($val);
      }
    }
    return $terms;
  }

  /////////////////////////////////////////////////////////////////
  // build the sql statement's where clause
  // this will build the sql based on the given information

  function  build_terms_clause($terms, $date, $fields, $match){

    global $DB;
    static $where_clause;

    if(empty($where_clause)){
      if($date!=0){
        $cutoff=date("Y-m-d", mktime(0,0,0,date("m"),date("d")-$date));
        $where_clause .= " datestamp >= '$cutoff' AND ";
      }


      while (list ($junk, $term) = each ($terms)) {
        if(substr($term, 0, 1)=="-"){
          if(strstr($DB->type, "postgresql")){
            $notmod="!";
          } else {
            $notmod="NOT ";
          }
          $term=substr($term, 1);
        } else {
          $notmod="";
        }
        reset($fields);
        unset($likeArray);
        while (list ($key, $val) = each ($fields)) {
          $term=addslashes($term);
          if(strstr($DB->type, "postgresql")){
            $likeArray[]=" upper($val) $notmod"."~~ upper('%$term%') ";
          }
          else{
            $likeArray[]=" $val $notmod"."LIKE '%$term%' ";
          }
        }
        $termArray[] = " (".implode( $likeArray, " OR " ).") ";
      }

      $cmptype="AND";
      if($match!=1) $cmptype="OR";
      $where_clause.= " (".implode( $termArray, " $cmptype " ).") ";

      $where_clause.="order by datestamp desc";
    }

    return $where_clause;

  }

  /////////////////////////////////////////////////////////////////
  // build the sql statement
  // this will build the sql based on the given information

  function  build_sql($table_name, $terms, $date, $fields, $match){

    global $ForumTableName;

    $SQL = "select $table_name.id, $table_name.thread, author, subject, datestamp, body from $table_name, $table_name"."_bodies where $table_name.id = $table_name"."_bodies.id and $table_name.approved='Y' AND";

    $SQL.=build_terms_clause($terms, $date, $fields, $match);

    return $SQL;
  }

  if(!isset($fldauthor) && !isset($fldsubject) && !isset($fldbody)){
    $fields[] = "subject";
    $fields[] = "body";
    $fldauthor=0;
    $fldsubject=1;
    $fldbody=1;
  }
  else{
    empty($fldauthor) ? $fldauthor=0 : $fields[] = "author";
    empty($fldsubject) ? $fldsubject=0 : $fields[] = "subject";
    empty($fldbody) ? $fldbody=0 : $fields[] = "body";
  }

  initvar("date", 30);
  initvar("globalsearch");
  initvar("match");
  initvar("start", 1);

  //////////////////////////
  // START NAVIGATION     //
  //////////////////////////

    $menu=array();
    if($ActiveForums>1)
      // Forum List
      addnav($menu, $lForumList, "$forum_page.$ext?f=$ForumParent$GetVars");
    // New Topic
    addnav($menu, $lStartTopic, "$post_page.$ext?f=$num$GetVars");
    // Go To Top
    addnav($menu, $lGoToTop, "$list_page.$ext?f=$num$GetVars");
    // Log Out/Log In
    if($ForumSecurity){
      if(!empty($phorum_auth)){
        addnav($menu, $lLogOut, "login.$ext?logout=1$GetVars");
        addnav($menu, $lMyProfile, "profile.$ext?f=$f&id=$phorum_user[id]$GetVars");
      }
      else{
        addnav($menu, $lLogIn, "login.$ext?f=$f$GetVars");
      }
    }

    $TopLeftNav=getnav($menu);

  //////////////////////////
  // END NAVIGATION       //
  //////////////////////////

  if (empty($search)){
    $search="";
    $searchtext="";
  }
  else{
    $search=trim(stripslashes($search));
    $searchtext = htmlentities($search);
    $terms = build_search_terms($search, $match);
    if(count($terms)>0){

      if($globalsearch){
        $SQL="Select id, name, table_name from $pho_main where";
        if(isset($searchforums)){
          $SQL.=" id in (".implode(",", $searchforums).")";
        }
        else{
          $SQL.=" (active=1 or id=$num)";
        }
        if(empty($phorum_auth)){
          $SQL.=" and security!=".SEC_ALL;
        }
        $q->query($DB, $SQL);
        $row = $q->getrow();
      }
      else{
        $row=array("id"=>$num, "name"=>$ForumName, "table_name"=>$ForumTableName);
      }
      $totalFound=0;
      $messagesCopied=0;
      $messages=array();
      while(is_array($row)){
        $forums[$row["id"]]=$row["name"];
        $SQL=build_sql($row["table_name"], $terms, $date, $fields, $match);
        $results[$row["id"]] = new query($DB, $SQL);
        $numrows=$results[$row["id"]]->numrows();
        if($numrows==0){
          unset($results[$row["id"]]);
        }
        else{
          $results[$row["id"]]->getrow();
          $totalFound+=$numrows;
        }
        if($globalsearch){
          $row = $q->getrow();
        }
        else{
          $row=0;
        }
      }

      if($globalsearch){
        $q->free();
      }


      $winner=1;
      $MessagesCopied=0;
      $MessagesMatched=0;
      while($MessagesCopied<$ForumDisplay && $winner!=0){
        $winner=0;
        reset($results);
        $highdate="";
        while(list($forum, $res)=each($results)){
          if(!empty($res->row)){
            if($res->field("datestamp")>$highdate){
              $highdate=$res->field("datestamp");
              $winner=$forum;
            }
          }
        }

        if($winner!=0){
          $MessagesMatched++;
          if($MessagesMatched>=$start){
            $MessagesCopied++;
            $row=$results[$winner]->row;
            $row["forum"]=$winner;
            $messages["$row[datestamp]-$winner-$row[id]"]=$row;
          }
          $results[$winner]->getrow();
          // if these match then there are no more rows in this result.
          if(empty($results[$winner]->row["id"]) || $row["id"]==$results[$winner]->row["id"]){
            unset($results[$winner]);
          }
        }
      }

    }
  }

  $sTitle=" ".strtolower($lSearch);

  include phorum_get_file_name("header");
?>
<?php
  if(@is_array($terms)){
?>
<table width="<?php echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?php echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumNavFontColor; ?>"><?php echo $TopLeftNav; ?></font></td>
  </tr>
</table>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="4" border="0">
  <tr>
    <?php
      $end=count($messages)+$start-1;
      if($end>0){
        $range="$start-$end $lOf $totalFound";
      }
      else{
        $range="";
      }

    ?>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumTableHeaderFontColor; ?>"  class="PhorumTableHeader">&nbsp;<?php echo "$lSearchResults: $range";?></font></td>
  </tr>
<?php
    $bgcolor=bgcolor($ForumTableBodyColor1);
    if($totalFound>0){
      krsort($messages);
      $message=current($messages);
      $count=$start-1;
      While(is_array($message)){
        $count=$count+1;
        if(!isset($top_id)){
          $top_id=$message["id"];
        }
        $text=format_body($message["body"]);
        $text=chop(substr($text, 0, 200));
        $text=strip_tags($text);
        $text=str_replace(PHORUM_SIG_MARKER, "", $text);
        $link="$read_page.$ext?f=$message[forum]&i=$message[id]&t=$message[thread]$GetVars";
        $subject=chop($message["subject"]);
        $author=chop($message["author"]);
        $datestamp=date_format($message["datestamp"]);
        $forum=$forums[$message["forum"]];
        echo "<tr><td $bgcolor>\n";
        echo "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\">\n";
        echo "  <tr>\n";
        echo "    <td width=\"5%\" nowrap $bgcolor><b>&nbsp;$count.</b></td>\n";
        echo "    <td width=\"47%\" $bgcolor><a href=\"$link\"><b>$subject</b></a></td>\n";
        echo "    <td width=\"24%\" $bgcolor>$author&nbsp;&nbsp;&nbsp;</td>\n";
        echo "    <td width=\"24%\" $bgcolor>$datestamp</td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        echo "<blockquote>\n";
        echo "<font class=\"PhorumMessage\">$text</font><br><br>\n";
        echo "<B>$lBigForum: <A HREF=\"$list_page.$ext?f=$message[forum]$GetVars\">$forum</A></B>\n";
        echo "</blockquote>\n";
        echo "</td></tr>\n";
        $last_id=$message["id"];
        $message=next($messages);
      }
    }
    else{
      echo "<tr>\n";
      echo "  <td nowrap $bgcolor><b>$lNoMatches</b></td>\n";
      echo "</tr>\n";
      $count = 0;
    }

    $prevmatch='';
    $morematch='';


    if($totalFound>$count){
      $startvar=$count+1;
      $morematch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&globalsearch=$globalsearch&match=$match&date=$date&fldauthor=$fldauthor&fldsubject=$fldsubject&fldbody=$fldbody&start=$startvar$GetVars\"><FONT color=\"$ForumNavFontColor\">$lMoreMatches</font></a>";
    }
    if($start!=1){
      $startvar=$start-$ForumDisplay;
      $prevmatch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&globalsearch=$globalsearch&match=$match&date=$date&fldauthor=$fldauthor&fldsubject=$fldsubject&fldbody=$fldbody&start=$startvar$GetVars\"><FONT color=\"$ForumNavFontColor\">$lPrevMatches</font></a>";
    }
    if($prevmatch || $morematch){
      echo "<tr>\n  <td colspan=\"3\" $bgcolor>\n";
      echo "<center><br><br><div class=nav><FONT color=\"$ForumNavFontColor\"><b>";
      if($prevmatch) echo $prevmatch;
      if($prevmatch && $morematch) echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
      if($morematch) echo $morematch;
      echo "</font></div><br><br></center>\n";
      echo "  </td>\n</tr>\n";
    }

?>
</table>
<BR>
<?php
  }
?>
<form action="<?php echo "$PHP_SELF"; ?>" method="GET">
<?php echo $PostVars; ?>
<input type="Hidden" name="f" value="<?php echo $num; ?>">
<table width="<?php echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?php echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumNavFontColor; ?>"><?php echo $TopLeftNav; ?></font></td>
  </tr>
</table>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumTableHeaderFontColor; ?>"  class="PhorumTableHeader">&nbsp;<?php echo $lSearch;?></font></td>
  </tr>
  <tr>
    <td align="CENTER" valign="MIDDLE" <?php echo bgcolor($ForumTableBodyColor2); ?>>
    <br>
<table cellspacing="0" cellpadding="2" border="0">
<tr>
    <td align="right"><font color="<?php echo $ForumTableBodyFontColor2; ?>">&nbsp;&nbsp;<?php echo $lSearch;?>:&nbsp;&nbsp;</font></td>
    <td><input type="Text" name="search" size="40" value="<?php echo $searchtext; ?>">&nbsp;<input type="Submit" value="<?php echo $lSearch;?>">&nbsp;&nbsp;</td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><font color="<?php echo $ForumTableBodyFontColor2; ?>"><select name="globalsearch"><option value="0" <?php if($globalsearch==0) echo "selected"; ?>><?php echo $lSearchThisForum ?></option><option value="1" <?php if($globalsearch==1) echo "selected"; ?>><?php echo $lSearchAllForums ?></option></select>&nbsp;&nbsp;&nbsp;&nbsp;<select name="match"><option value="1" <?php if($match==1) echo "selected"; ?>><?php echo $lSearchAllWords ?></option><option value="2" <?php if($match==2) echo "selected"; ?>><?php echo $lSearchAnyWords ?></option><option value="3" <?php if($match==3) echo "selected"; ?>><?php echo $lSearchPhrase ?></option></select>&nbsp;&nbsp;&nbsp;&nbsp;<select name="date"><option value="30" <?php if($date==30) echo "selected"; ?>><?php echo $lSearchLast30; ?></option><option value="60" <?php if($date==60) echo "selected"; ?>><?php echo $lSearchLast60; ?></option><option value="90" <?php if($date==90) echo "selected"; ?>><?php echo $lSearchLast90; ?></option><option value="180" <?php if($date==180) echo "selected"; ?>><?php echo $lSearchLast180; ?></option><option value="0" <?php if($date==0) echo "selected"; ?>><?php echo $lSearchAllDates; ?></option></select></font></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><font color="<?php echo $ForumTableBodyFontColor2; ?>"><input type="checkbox" name="fldauthor" value="1" <?php if($fldauthor==1)  echo "checked"; ?>> <?php echo $lAuthor; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldsubject" value="1" <?php if($fldsubject==1)  echo "checked"; ?>> <?php echo $lFormSubject; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldbody" value="1" <?php if($fldbody==1)  echo "checked"; ?>> <?php echo $lMessageBodies; ?>&nbsp;&nbsp;&nbsp;</font></td>
</tr>
</table>
</font><br></td>
</td>
</tr>
</table>
</form>
<p>
<table class="PhorumListTable" width="<?php echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumTableHeaderFontColor; ?>" class="PhorumTableHeader">&nbsp;<?php echo $lSearchTips;?></font></td>
  </tr>
  <tr>
    <td width="<?php echo $ForumTableWidth; ?>" align="LEFT" valign="MIDDLE" <?php echo bgcolor($ForumTableBodyColor2); ?>><font color="<?php echo $ForumTableBodyFontColor2; ?>"><?php echo $lTheSearchTips;?><br></font></td>
  </tr>
</table>
<?php
  include phorum_get_file_name("footer");
?>
