<?php

  if(empty($phorum_auth) && $ForumSecurity > SEC_OPTIONAL && initvar("read")){
    $target=$REQUEST_URI;
    include "./login.$ext";
    return;
  }
  elseif(!empty($phorum_auth)){
    $author=$phorum_user["name"];
    $email=$phorum_user["email"];
  }
  else{

    $name_cookie="phorum_name";
    if(isset($$name_cookie) && empty($author)){
      $author=$$name_cookie;
    }
    elseif(!isset($author)){
      $author="";
    }

    $email_cookie="phorum_email";
    if(isset($$email_cookie) && empty($email)){
      $email=$$email_cookie;
    }
    elseif(!isset($email)){
      $email="";
    }

  }

  if(get_magic_quotes_gpc()){
    $email=stripslashes($email);
    $author=stripslashes($author);
    if(!empty($subject)) $subject=stripslashes($subject);
    $body=stripslashes($body);
  }

  $quote_button="";

  if(initvar("read")!=false){
    $caption = $lReplyMessage;
    if(!eregi("^re:", $qsubject)){
      $p_subject="Re: ".$qsubject;
    }
    else{
      $p_subject= $qsubject;
    }

    $parent=$id;
    if(!$$phflat){
      $quote = "$qauthor $lWrote:\n";
      $quote .= textwrap("\n$qbody", 63, "\n", "> ") . "\n";
      $quote = htmlspecialchars($quote);
      $quote_button="<input type=\"Hidden\" name=\"hide\" value=\"".$quote."\"><script language=\"JavaScript\"><!--\nthis.document.writeln('<input tabindex=\"100\" type=\"Button\" name=\"quote\" value=\"$lQuote\" onClick=\"this.form.body.value=this.form.body.value + this.form.hide.value; this.form.hide.value='+\"''\"+';\">');\n//--></script>";
    }
    $p_body="";
  }
  else{
    $caption = $lStartTopic;
    $p_subject=initvar("subject");
    $p_body=initvar("body");
  }
  $p_author=$author;
  $p_email=$email;

?>
<?php
  if(isset($IsError) && $action){
    echo "<p><b>$IsError</b>";
  }
?>
<?php
  if ($AllowAttachments && $ForumAllowUploads == 'Y' && $ForumMaxUploads<4) {
    $enctype = "multipart/form-data";
  } else {
    $enctype = "application/x-www-form-urlencoded";
  }
?>
<form action="<?php echo "$post_page.$ext"; ?>" method="post" enctype="<?php echo $enctype ?>" onSubmit="post.disabled=true;">
<input type="Hidden" name="t" value="<?php  echo $thread; ?>">
<input type="Hidden" name="a" value="post">
<input type="Hidden" name="f" value="<?php echo $num; ?>">
<input type="Hidden" name="p" value="<?php echo $parent; ?>">
<?php echo $PostVars; ?>
<table cellspacing="0" cellpadding="0" border="0">
<tr>
    <td <?php echo bgcolor($ForumNavColor); ?>>
      <table cellspacing="0" cellpadding="2" border="0">
        <tr>
          <td><?php echo empty($TopLeftNav) ? "&nbsp;" : $TopLeftNav; ?></td>
        </tr>
      </table>
    </td>
</tr>
<tr>
  <td <?php echo bgcolor($ForumNavColor); ?>>
    <table class="PhorumListTable" cellspacing="0" cellpadding="2" border="0">
    <tr>
        <td class="PhorumTableHeader" height="23" colspan="2" <?php echo bgcolor($ForumTableHeaderColor); ?>><FONT color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $caption; ?></font></td>
    </tr>
    <?php if(!empty($phorum_auth) && !empty($p_author)){ ?>
    <tr>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?php echo $ForumTableBodyFontColor1; ?>">&nbsp;<?php echo $lFormName;?>:</font></td>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?>><font color="<?php echo $ForumTableBodyFontColor1; ?>"><?php echo $p_author; ?></font><input type="hidden" name="author" value="<?php echo $p_author; ?>"></td>
    </tr>
    <tr>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?php echo $ForumTableBodyFontColor1; ?>">&nbsp;<?php echo $lFormEmail;?>:</font></td>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?>><font color="<?php echo $ForumTableBodyFontColor1; ?>"><?php echo $p_email; ?></font><input type="hidden" name="email" value="<?php echo $p_email; ?>"></td>
    </tr>
    <?php } else { ?>
    <tr>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?php echo $ForumTableBodyFontColor1; ?>">&nbsp;<?php echo $lFormName;?>:</font></td>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="author" size="30" maxlength="30" value="<?php echo $p_author; ?>"></td>
    </tr>
    <tr>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?php echo $ForumTableBodyFontColor1; ?>">&nbsp;<?php echo $lFormEmail;?>:</font></td>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="email" size="30" maxlength="200" value="<?php echo $p_email; ?>"></td>
    </tr>
    <?php } ?>
    <tr>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?> nowrap><font color="<?php echo $ForumTableBodyFontColor1; ?>">&nbsp;<?php echo $lFormSubject;?>:</font></td>
        <td height="23" <?php echo bgcolor($ForumTableBodyColor1); ?>><input type="Text" name="subject" size="30" maxlength="80" value="<?php echo $p_subject; ?>"></td>
    </tr>
    <?php
      if ($AllowAttachments && $ForumAllowUploads == 'Y' && $ForumMaxUploads<4) {
        for($x=0;$x<$ForumMaxUploads;$x++){
          echo "<tr>\n";
          echo '    <td height="21" ' . bgcolor($ForumTableBodyColor1) . ' nowrap><font color="' . $ForumTableBodyFontColor1 . '">&nbsp;' . $lFormAttachment . ':</font></td>';
          echo '    <td height="21" ' . bgcolor($ForumTableBodyColor1) . '><input type="File" name="attachment_'.$x.'" size="30" maxlength="64"></td>';
          echo "</tr>\n";
        }
      }
    ?>
    <tr>
        <td <?php echo bgcolor($ForumTableBodyColor1); ?> colspan=2 width="100%" nowrap align="left"><table cellpadding="5" cellspacing="0" border="0"><tr><td align="CENTER" valign="TOP"><font face="courier"><textarea class="PhorumBodyArea" name="body" cols="45" rows="20" wrap="VIRTUAL"><?php echo $p_body; ?></textarea></font></td></tr></table></td>
    </tr>
    <?php if(!empty($phorum_user["signature"])){ ?>
    <tr>
        <td <?php echo bgcolor($ForumTableBodyColor1); ?> colspan=2 width="100%" nowrap align="left"><font color="<?php echo $ForumTableBodyFontColor1; ?>"><input type="checkbox" name="use_sig" value="Y" checked><?php echo $lUseSig; ?></font></td>
    </tr>
    <?php } ?>
    <?php if($ForumModeration!="a" && ($ForumAllowEMailNotify || (!empty($phorum_auth)))){ ?>
    <tr>
        <td <?php echo bgcolor($ForumTableBodyColor1); ?> colspan=2 width="100%" nowrap align="left"><font color="<?php echo $ForumTableBodyFontColor1; ?>"><input type="checkbox" name="email_reply" value="Y"><?php echo $lEmailMe; ?></font></td>
    </tr>
    <?php } ?>
    <tr>
        <td width="100%" colspan="2" align="RIGHT" <?php echo bgcolor($ForumTableBodyColor1); ?>><?php echo $quote_button; ?>&nbsp;
    <?php  if ($AllowAttachments && $ForumAllowUploads == 'Y' && $ForumMaxUploads>3) { ?>
        <input type="Submit" name="attach" value=" <?php echo $lFormAttach;?> ">&nbsp;
    <?php } ?>
    <input type="Submit" name="post" value=" <?php echo $lFormPost;?> ">&nbsp;<br><img src="images/trans.gif" width=3 height=3 border=0></td>
    </tr>
    </table>
  </td>
</tr>
</table>
</form>
