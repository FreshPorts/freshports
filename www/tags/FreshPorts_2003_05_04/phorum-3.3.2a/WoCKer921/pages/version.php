<?php check_security(); ?>
<table border="0" cellspacing="0" cellpadding="3" class="box-table"><br/>
<tr>
  <td colspan="2" align="center" valign="middle" class="table-header">Latest Phorum Version</td>
</tr>
<tr>
  <th valign="middle" bgcolor="#FFFFFF">Latest Version:</th>
  <td valign="middle" bgcolor="#FFFFFF"><?php echo $ver_arr[0]; ?></td>
</tr>
<tr>
  <th valign="middle" bgcolor="#FFFFFF">Release Date:</th>
  <td valign="middle" bgcolor="#FFFFFF"><?php echo $ver_arr[1]; ?></td>
</tr>
<tr>
  <th valign="middle" bgcolor="#FFFFFF">Download Locations:</th>
  <td valign="middle" bgcolor="#FFFFFF"><?php
$cnt=count($ver_arr);
for($x=2;$x<$cnt;$x++){
  $url=$ver_arr[$x];
  echo "<a href=\"$url\">$url</a>\n<br>";
}
?></td>
</tr>
</table>

