<?PHP

  $name_cookie="phorum_name";
  if(isset($$name_cookie) && !isset($author)){
    $author=$$name_cookie;
  }
  elseif(!isset($author)){
    $author="";
  }

  $email_cookie="phorum_email";
  if(isset($$email_cookie) && !isset($email)){
    $email=$$email_cookie;
  }
  elseif(!isset($email)){
    $email="";
  }

  if(get_cfg_var("magic_quotes_gpc")){
    $email=stripslashes($email);
    $author=stripslashes($author);
    if(!empty($subject)) $subject=stripslashes($subject);
    $body=stripslashes($body);
  }
  
  if($read!=false){
    $caption = $lReplyMessage;
    if(!eregi("^re:", $qsubject)){
      $p_subject="RE: ".$qsubject;
    }
    else{
      $p_subject= $qsubject;
    }

    $parent=$id;
    if(!$$phflat){
      if(strlen($qbody)>1000){
        $qbody=substr($qbody,0,1000)."....";
      }
      $quote = " \n\n".$qauthor." ".$lWrote.":\n";
      $quote .= "-------------------------------\n";
      $quote .= eregi_replace("<br>", "", $qbody);
      $quote = htmlspecialchars($quote);
      $quote_button="&nbsp;&nbsp;<input type=\"Hidden\" name=\"hide\" value=\"".undo_htmlspecialchars($quote)."\"><input tabindex=\"100\" type=\"Button\" name=\"quote\" value=\"$lQuote\" onClick=\"this.form.body.value=this.form.body.value + this.form.hide.value; this.form.hide.value='';\">";
    }
  }
  else{
    $caption = $lStartTopic;
    $p_subject=$subject;
    $p_body=$body;    
  }
  $p_author=$author;
  $p_email=$email;
  
?>
<?PHP
  if($IsError && $action){
    echo "<p><b>$IsError</b>";
  }
?>
<form action="<?PHP echo "$post_page.$ext"; ?>" method="post" enctype="multipart/form-data">
<input type="Hidden" name="t" value="<?PHP  echo $thread; ?>">
<input type="Hidden" name="a" value="post">
<input type="Hidden" name="f" value="<?PHP echo $num; ?>">
<input type="Hidden" name="p" value="<?PHP echo $parent; ?>">
<?php echo $PostVars; ?>
<table width="100%" cellspacing="0" cellpadding="2" border="0">
<tr>
    <td colspan="2" <?PHP echo bgcolor($ForumNavColor); ?>>
      <table cellspacing="0" cellpadding="1" border="0">
        <tr>
          <td><?PHP echo $nav; ?></td>
        </tr>
      </table>
    </td>
</tr>
<tr>
    <td height="21" colspan="2" <?PHP echo bgcolor($ForumTableHeaderColor); ?>><FONT color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $caption; ?></font></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?PHP echo $ForumTableBodyFontColor1; ?>">&nbsp;<?PHP echo $lFormName;?>:</font></td>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="author" size="30" maxlength="30" value="<?PHP echo $p_author; ?>"></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?PHP echo $ForumTableBodyFontColor1; ?>">&nbsp;<?PHP echo $lFormEmail;?>:</font></td>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="email" size="30" maxlength="50" value="<?PHP echo $p_email; ?>"></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?PHP echo $ForumTableBodyFontColor1; ?>">&nbsp;<?PHP echo $lFormSubject;?>:</font></td>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="subject" size="30" maxlength="50" value="<?PHP echo $p_subject; ?>"></td>
</tr>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?> colspan=2 width="100%" nowrap align="left"><table cellpadding="5" cellspacing="0" border="0"><tr><td align="CENTER" valign="TOP"><textarea name="body" cols="45" rows="20" wrap="VIRTUAL"><?PHP echo $p_body; ?></textarea></td></tr></table></td>
</tr>
<? if($ForumModeration!="a"){ ?>
<tr>
    <td <?PHP echo bgcolor($ForumTableBodyColor1); ?> colspan=2 width="100%" nowrap align="left"><font color="<?PHP echo $ForumTableBodyFontColor1; ?>">
<?
//<input type="checkbox" name="email_reply" value="Y">
//<?PHP echo $lEmailMe; 
?>
</font></td>
</tr>
<? } ?>
<tr>
    <td width="100%" colspan="2" align="LEFT" <?PHP echo bgcolor($ForumTableBodyColor1); ?>><?PHP echo $quote_button; ?>&nbsp;<input type="Submit" name="post" value=" <?PHP echo $lFormPost;?> ">&nbsp;<br><img src="images/trans.gif" width=3 height=3 border=0></td>
</tr>
</table>
</form>
