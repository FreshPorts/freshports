<?PHP
  if (!isset($$phcollapse)) {
    $$phcollapse=0;
  }
?>
<table width="<?PHP echo $ForumTableWidth; ?>" cellspacing="0" cellpadding="0" border="0">
<tr>
    <td height="21" <?PHP echo bgcolor($ForumTableHeaderColor); ?> width="100%"><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lTopics;?></font></td>
    <td height="21" <?PHP echo bgcolor($ForumTableHeaderColor); ?> width="150" nowrap><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>"><?PHP echo $lAuthor;?>&nbsp;</font></td>
<?PHP if ( $$phcollapse != 0 && !$read) { ?>
    <td align="center" height="21" <?PHP echo bgcolor($ForumTableHeaderColor); ?> width="80" nowrap><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>"><?PHP echo $lReplies;?>&nbsp;</font></td>
    <td height="21" <?PHP echo bgcolor($ForumTableHeaderColor); ?> width="115" nowrap><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>"><?PHP echo $lLatest;?></font></td>
<?PHP }else{ ?>
    <td height="21" <?PHP echo bgcolor($ForumTableHeaderColor); ?> width="115" nowrap><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>"><?PHP echo $lDate;?></font></td>
<?PHP } ?>
</tr>
<?PHP
  $x=0;
  $loc=0;
  echo "<!--\n";
	$message = $msg_list->firstrow();
  if(isset($thread_list)){
	  $thread_list->firstrow();
  }
  echo $msg_list->numrows();
  echo "-->\n";  
	while (is_array($message)){
		if(($x%2)==0){
			$bgcolor=$ForumTableBodyColor1;
      $fcolor=$ForumTableBodyFontColor1;
		}
		else{
			$bgcolor=$ForumTableBodyColor2;
      $fcolor=$ForumTableBodyFontColor2;
		}
		$t_id=$message["id"];
		$t_thread=$message["thread"];
		$t_subject=chop($message["subject"]);
		$t_author=chop($message["author"]);
		$t_datestamp = date_format($message["datestamp"]);

		if( $$phcollapse != 0 && !$read ){
		  $t_latest=date_format($thread_list->field("latest", $x));
		  $t_maxid=$thread_list->field("maxid");
    }
    
  	$message = $msg_list->getrow();

		if($t_thread!=$t_id){
			$img = '&nbsp;<img src="images/l.gif" border=0 width=12 height=21 align="top">';
			if(is_array($message)){
				if($t_thread==$message["thread"]){
					$img='&nbsp;<img src="images/t.gif" border=0 width=12 height=21 align="top">';
				}
			}
		}
    		else{
			$img="";
			$loc=0;
		}

		if($id==$t_id && $read=true){
			$t_subject = "<b>$t_subject</b>";
			$t_author = "<b>$t_author</b>";
			$t_datestamp = "<b>$t_datestamp</b>";
		}
		else{
			$t_subject="<a href=\"$read_page.$ext?f=$num&i=$t_id&t=$t_thread$GetVars\">$t_subject</a>";
		}

    $color=bgcolor($bgcolor);
		echo "<tr>\n";
		echo '  <td height="20" '.$color.' nowrap><FONT color="'.$fcolor.'">'.$img.'&nbsp;'.$t_subject."&nbsp;</font>";

    if(isset($haveread[0])){
      $temp=$haveread[0];
    }
    else{
      $temp=0;
    }
    if($temp<$t_id && !IsSet($haveread[$t_id]) && $UseCookies){
      ?><font face="MS Sans Serif,Geneva" size="-2" color="#FF0000"><?PHP echo $lNew; ?></font><?PHP
    }
		echo "</td>\n";
		echo '  <td width="150" height="20" '.$color.' nowrap><FONT color="'.$fcolor.'">'.$t_author.'&nbsp;</font></td>'."\n";
		if( $$phcollapse != 0 && !$read ){
  		$t_count=$thread_list->field("tcount")-1;
    	$thread_list->getrow();
		  echo '  <td align="center" width="80" height="20" '.$color.' nowrap><FONT color="'.$fcolor.'" size=-1>'.$t_count."&nbsp;</font></td>\n";
		  echo '  <td width="115" height="20" '.$color.' nowrap><FONT color="'.$fcolor.'" size=-1>'.$t_latest."&nbsp;</font></td>\n";
		}
    else{
  		echo '  <td width="115" height="20" '.$color.' nowrap><FONT color="'.$fcolor.'" size=-1>'.$t_datestamp.'&nbsp;</font></td>'."\n";
    }
		echo "</tr>\n";
		$x++;
		$loc++;
	} // end while
?>
</table>