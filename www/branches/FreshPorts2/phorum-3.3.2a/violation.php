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

  $sTitle=" sorry :(";
  require "./common.php";
  include phorum_get_file_name("header");
?>
<center>
<table class="PhorumListTable" width="$ForumTableWidth" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?php echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?php echo $ForumTableHeaderFontColor; ?>">&nbsp;<?php echo $lViolationTitle; ?></font></td>
  </tr>
  <tr>
    <td width="100%" align="LEFT" valign="MIDDLE" <?php echo bgcolor($ForumTableBodyColor2); ?>><?php echo $lViolation; ?></td>
</td>
</tr>
</table>
<?php

  include phorum_get_file_name("footer");

?>