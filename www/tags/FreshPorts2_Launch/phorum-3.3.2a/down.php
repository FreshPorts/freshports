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

  define("PHORUM_ADMIN", 1);
  require "./common.php";
  $title = $lForumDown;
  include phorum_get_file_name("header");
?>
<center>
<table class="PhorumListTable" width="<?php echo $default_table_width; ?>" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td class="PhorumTableHeader" <?php echo bgcolor($default_table_header_color); ?> valign="TOP" nowrap><font color="<?php echo $default_table_header_font_color; ?>">&nbsp;<?php echo $lForumDown; ?></font></td>
  </tr>
  <tr>
    <td width="100%" align="LEFT" valign="MIDDLE" <?php echo bgcolor($default_table_body_color_2); ?>><font color="<?php echo $default_table_body_font_color_1; ?>"><?php echo $lForumDownNotice; ?></font><br></td>
  </tr>
</table>
</center>
<?php

  include phorum_get_file_name("footer");
?>