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

    static $where_clause;

    if(empty($where_clause)){
      if($date!=0){
        $cutoff=date("Y-m-d", mktime(0,0,0,date("m"),date("d")-$date));
        $where_clause .= " datestamp >= '$cutoff' AND ";
      }

      while (list ($junk, $term) = each ($terms)) {
        $cmpfunc="LIKE";
        if(substr($term, 0, 1)=="-"){
          $term=substr($term, 1);
          $cmpfunc="NOT LIKE";
        }
        reset($fields);
        unset($likeArray);
        while (list ($key, $val) = each ($fields)) {
          $likeArray[]=" $val $cmpfunc '%$term%' ";
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

    GLOBAL $ForumTableName;

    $SQL = "select $table_name.id, $table_name.thread, author, subject, datestamp, body from $table_name, $table_name"."_bodies where $table_name.id = $table_name"."_bodies.id and $table_name.approved='Y' AND";

    $SQL.=build_terms_clause($terms, $date, $fields, $match);

    return $SQL;
  }

  if (!isset($search)){
    $search="";
  }

  $search=trim(stripslashes($search));
  $searchtext = $search;

  $searchtext = htmlentities($searchtext);

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
  initvar("match", 1);
  initvar("start_num", 0);

  if($ActiveForums>1){
    $nav = "<div class=nav><a href=\"$forum_page.$ext?f=$ForumParent$GetVars\"><font color='$ForumNavFontColor'>".$lForumList."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$post_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;</font></div>";
  }
  else{
    $nav = "<div class=nav><FONT color='$ForumNavFontColor'><a href=\"$post_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lStartTopic."</font></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$list_page.$ext?f=$num$GetVars\"><font color='$ForumNavFontColor'>".$lGoToTop."</font></a>&nbsp;</font></div>";
  }

  if($search!=""){
    $terms = build_search_terms($search, $match);
    if(count($terms)>0){
      if(isset($x)){
        list($action,$start_num)=explode(",", $x);
      }

      $SQL=build_sql($ForumTableName, $terms, $date, $fields, $match);

//      echo "\n<!-- $SQL -->\n";

      $q->query($DB, $SQL);

      if($err=$q->error()){
        echo $err;
      }
      else{
        $totalFound=$q->numrows();
        $q->seek($start_num);
        $message = $q->getrow();
        $rowcount=0;
        while(is_array($message) && $rowcount<$ForumDisplay){
          $rowcount++;
          $messages[]=$message;
          $message = $q->getrow();
        }
        $q->free();
      }
      $rows = @count($messages);
    }

  }

  $sTitle=" ".strtolower($lSearch);

  if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
    include "$include_path/header_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/header.php";
  }
?>
<?PHP
  if(@is_array($terms)){
?>
<p>
<table width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumNavFontColor; ?>"><?PHP echo $nav; ?></font></td>
  </tr>
</table>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="4" border="0">
  <tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo "$lSearchResults: $totalFound";?></font></td>
  </tr>
<tr><td width="<?PHP echo $ForumTableWidth; ?>" valign="TOP" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><font color="<?PHP echo $ForumTableBodyFontColor2; ?>">
<?PHP
    if($rows>0){
      $message=current($messages);
      $count=$start_num;
      While(is_array($message)){
        $count=$count+1;
        if(!isset($top_id)){
          $top_id=$message["id"];
        }
        echo "<dl><dt><b>$count. </b><a href=\"$read_page.$ext?f=$num&i=".$message["id"]."&t=".$message["thread"]."$GetVars\"><b>".chop($message["subject"])."</b></a> - ".chop($message["author"])."<br>\n<dd>";
        $text=chop(substr($message["body"], 0, 200));
        if(function_exists("strip_tags")){
          $text=strip_tags($text);
        }
        else{
          $text=ereg_replace("<[^>]*>", "", $text);
          $text=ereg_replace("^<[^>]*>", "", $text);
          $text=ereg_replace("<[^>]*>$", "", $text);
        }
        echo $text."<br>";
        echo "<font size=-2>$lDate: ".date_format($message["datestamp"])."</font><br>\n";
        echo "</dl><p>\n";
        $last_id=$message["id"];
        $message=next($messages);
      }
    }
    else{
      echo $lNoMatches;
    }
    $prevmatch='';
    $morematch='';
    if($start_num >= $ForumDisplay){
      $start_num=$start_num-$ForumDisplay;
      $prevmatch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&match=$match&date=$date&fldauthor=$fldauthor&fldsubject=$fldsubject&fldbody=$fldbody&x=2,$start_num$GetVars\"><FONT color=\"$ForumNavFontColor\">$lPrevMatches</font></a>";
    }
    if($rows>=$ForumDisplay){
      $morematch="<a href=\"$search_page.$ext?f=$num&search=".urlencode($search)."&match=$match&date=$date&fldauthor=$fldauthor&fldsubject=$fldsubject&fldbody=$fldbody&x=1,$count$GetVars\"><FONT color=\"$ForumNavFontColor\">$lMoreMatches</font></a>";
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
<BR>
<?PHP
  }
?>
<form action="<?PHP echo "$search_page.$ext"; ?>" method="GET">
<?PHP echo $PostVars; ?>
<input type="Hidden" name="f" value="<?PHP echo $num; ?>">
<table width="<?PHP echo $ForumTableWidth; ?>" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td <?PHP echo bgcolor($ForumNavColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumNavFontColor; ?>"><?PHP echo $nav; ?></font></td>
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
    <td align="right"><font color="<?PHP echo $ForumTableBodyFontColor2; ?>">&nbsp;&nbsp;<?PHP echo $lSearch;?>:&nbsp;&nbsp;</font></td>
    <td><input type="Text" name="search" size="30" value="<?PHP echo $searchtext; ?>">&nbsp;<input type="Submit" value="<?PHP echo $lSearch;?>">&nbsp;&nbsp;</td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><font color="<?PHP echo $ForumTableBodyFontColor2; ?>"><select name="match"><option value="1" <?PHP if($match==1) echo "selected"; ?>><?PHP echo $lSearchAllWords ?></option><option value="2" <?PHP if($match==2) echo "selected"; ?>><?PHP echo $lSearchAnyWords ?></option><option value="3" <?PHP if($match==3) echo "selected"; ?>><?PHP echo $lSearchPhrase ?></option></select>&nbsp;&nbsp;&nbsp;&nbsp;<select name="date"><option value="30" <?PHP if($date==30) echo "selected"; ?>><?PHP echo $lSearchLast30; ?></option><option value="60" <?PHP if($date==60) echo "selected"; ?>><?PHP echo $lSearchLast60; ?></option><option value="90" <?PHP if($date==90) echo "selected"; ?>><?PHP echo $lSearchLast90; ?></option><option value="180" <?PHP if($date==180) echo "selected"; ?>><?PHP echo $lSearchLast180; ?></option><option value="0" <?PHP if($date==0) echo "selected"; ?>><?PHP echo $lSearchAllDates; ?></option></select></font></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td><font color="<?PHP echo $ForumTableBodyFontColor2; ?>"><input type="checkbox" name="fldauthor" value="1" <?PHP if($fldauthor==1)  echo "checked"; ?>> <?PHP echo $lAuthor; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldsubject" value="1" <?PHP if($fldsubject==1)  echo "checked"; ?>> <?PHP echo $lFormSubject; ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" name="fldbody" value="1" <?PHP if($fldbody==1)  echo "checked"; ?>> <?PHP echo $lMessageBodies; ?>&nbsp;&nbsp;&nbsp;</font></td>
</tr>
</table>
</font><br></td>
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
    <td width="<?PHP echo $ForumTableWidth; ?>" align="LEFT" valign="MIDDLE" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><font color="<?PHP echo $ForumTableBodyFontColor2; ?>"><?PHP echo $lTheSearchTips;?><br></font></td>
  </tr>
</table>
<?PHP

  if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
    include "$include_path/footer_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/footer.php";
  }

?>