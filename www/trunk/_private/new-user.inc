 <table width="*" border="0" cellpadding="1">
<form action="<?php echo $PHP_SELF . "?origin=" . $origin ?>" method="POST" name=f>
          <tr>
            <td valign="top">
<? if (!$Customize) { ?>
              <input type="hidden" name="ADD" value="1">
              User ID:<br>
              <input SIZE="15" NAME="UserLogin" VALUE="<? echo $UserLogin ?>"><br><br>
<? } ?>
               Password:<br>
               <input TYPE="PASSWORD" NAME="Password1" VALUE="<?echo $Password1 ?>" size="20"><br><br>
               Confirm Password:<br>
               <input TYPE="PASSWORD" NAME="Password2" VALUE="<?echo $Password2 ?>" size="20">
            </td>
            <td valign="top">
               email address (optional):<br>
               <input SIZE="35" NAME="email" VALUE="<?echo $email ?>"><br><br>
               <input type="checkbox" name="emailsitenotices_yn" value="ON" <? if ($emailsitenotices_yn == "ON") {echo " checked";}?>>Put me on the announcement mailing list (low volume)<br>
<br>
We can send you an email when something on your watch list changes.<br>
Send me, at most, one message per: <select name="watchnotifyfrequency" size="1">
    <option <? if ($watchnotifyfrequency == "Z") echo "selected " ?> value="Z">Don't notify me</option>
    <option <? if ($watchnotifyfrequency == "D") echo "selected " ?> value="D">Day</option>
    <option <? if ($watchnotifyfrequency == "W") echo "selected " ?> value="W">Week (on Tuesdays)</option>
    <option <? if ($watchnotifyfrequency == "F") echo "selected " ?> value="F">Fortnight (9th and 23rd)</option>
    <option <? if ($watchnotifyfrequency == "M") echo "selected " ?> value="M">Month (23rd)</option>  </select>
<br><br>
            <input TYPE="submit" VALUE="<? if ($Customize) { echo "update";} else { echo "create";} ?> account" name="submit">
            <input TYPE="reset"  VALUE="reset form">
            </td>
          </tr>
        </table>
