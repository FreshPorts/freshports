<table width="100%" border="0" cellpadding="5" cellspacing="4">
<form action="<?php echo $PHP_SELF . "?origin=" . $origin ?>" method="POST">
          <tr>
            <td valign="top">
<? if (!$Customize) { ?>
              <input type="hidden" name="ADD" value="1">
              User ID:<br>       
              <input SIZE="15" NAME="UserID" VALUE="<? echo $UserID ?>"><br><br>
<? } ?>
               Password:<br>
               <input TYPE="PASSWORD" NAME="Password1" VALUE="<?echo $Password1 ?>" size="20"><br><br>
               Confirm Password:<br>
               <input TYPE="PASSWORD" NAME="Password2" VALUE="<?echo $Password2 ?>" size="20">
<hr>
<br>
Maximum number of ports per page: <input SIZE="2" NAME="MaxNumberOfPorts" VALUE="<? echo $MaxNumberOfPorts ?>"><br>
Number of days a port is marked as new: <input SIZE="2" NAME="DaysMarkedAsNew" VALUE="<? echo $DaysMarkedAsNew ?>">
<br><br>
<hr>            </td>
            <td valign="top" colspan="2">
               email address (optional):<br>
               <input SIZE="35" NAME="email" VALUE="<?echo $email ?>"><br><br>
               <input type="checkbox" name="emailsitenotices_yn" value="ON" <? if ($emailsitenotices_yn == "ON") {echo " checked";}?>>Put me on the announcement mailing list (low volume)<br>
<br>                                
We can send you an email when something on your watch lists changes.<br>
Send me, at most, one message per:
<select name="watchnotifyfrequency" size="1">
    <option <? if ($watchnotifyfrequency == "Z") echo "selected " ?> value="Z">Don't notify me</option>
    <option <? if ($watchnotifyfrequency == "D") echo "selected " ?> value="D">Day</option>
    <option <? if ($watchnotifyfrequency == "W") echo "selected " ?> value="W">Week (on Tuesdays)</option>
    <option <? if ($watchnotifyfrequency == "F") echo "selected " ?> value="F">Fortnight (9th and 23rd)</option>
    <option <? if ($watchnotifyfrequency == "M") echo "selected " ?> value="M">Month (23rd)</option>  </select>                       
              </td>
          </tr>
<tr>
<td valign="top">
<b>Port details</b><br>(everywhere but Port Description page):<br>
<blockquote>
<input type="checkbox" name="ShowShortDescription" <? echo freshports_YNToCheckbox($ShowShortDescription ) ?>>Show Short description<br>
<input type="checkbox" name="ShowMaintainedBy"     <? echo freshports_YNToCheckbox($ShowMaintainedBy     ) ?>>Show Maintained by<br>
<input type="checkbox" name="ShowLastChange"       <? echo freshports_YNToCheckbox($ShowLastChange       ) ?>>Show Last Change<br>
<input type="checkbox" name="ShowDescriptionLink"  <? echo freshports_YNToCheckbox($ShowDescriptionLink  ) ?>>Show Description Link<br>
<input type="checkbox" name="ShowChangesLink"      <? echo freshports_YNToCheckbox($ShowChangesLink      ) ?>>Show Changes Link<br>
<input type="checkbox" name="ShowDownloadPortLink" <? echo freshports_YNToCheckbox($ShowDownloadPortLink ) ?>>Show Download Port link<br>
<input type="checkbox" name="ShowPackageLink"      <? echo freshports_YNToCheckbox($ShowPackageLink      ) ?>>Show Package Link<br>
<input type="checkbox" name="ShowHomepageLink"     <? echo freshports_YNToCheckbox($ShowHomepageLink     ) ?>>Show Homepage Link<br>
</blockquote>
<hr>
<br>
<b>Date Format</b><br>
Predefined formats:
<select name="FormatDateSelect" size="1">
    <option value="" selected>A selection here overrides the entry below</option>
    <option value="%Y/%m/%d">yyyy/mm/dd</option>
    <option value="%Y/%c/%e">yyyy/m/d</option>
    <option value="%c/%e">m/d</option>
    <option value="%m/%d">mm/dd</option>
    <option value="%e/%c">d/m</option>
    <option value="%d/%m">dd/mm</option>
    <option value="%c/%e/%Y">m/d/yyyy</option>
    <option value="%d/%m/%Y">mm/dd/yyyy</option>
  </select>
<br>
your current format:<br>
<input SIZE="20" NAME="FormatDateCustom" VALUE="<? echo $FormatDate ?>"><br>
gives this date: <? echo $SampleFormatDate ?>
<br>
<hr>
<br>
<b>Time Format</b><br>
Predefined formats:
<select name="FormatTimeSelect" size="1">
    <option value="" selected>A selection here overrides the entry below</option>
    <option value="%r">hh:mm:ss am/pm</option>
    <option value="%T">HH:MM:SS (24hr)</option>
  </select>
your current format:<br>
<input SIZE="20" NAME="FormatTimeCustom" VALUE="<? echo $FormatTime ?>"><br>
gives this time: <? echo $SampleFormatTime ?>
<br>
<hr>
<br>
            <input TYPE="submit" VALUE="<? if ($Customize) { echo "update";} else { echo "create";} ?> account" name="submit">      
            <input TYPE="reset"  VALUE="reset form">
</td>
<td>
<hr>
Please use these specifiers for custom date/time values (as blatently stolen from the mysql manual).<br>
<br>
<? include( "./_private/date-format.inc"); ?>
</td></tr>
        </table>
