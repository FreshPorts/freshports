<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
    <td align="center" class="table-header">Moderate For:</td>
</tr>
<tr>
<td nowrap>
<?php
    while(list($fid, $value)=each($PHORUM["admin_user"]["forums"])){
        $path=GetForumPath($fid);
        echo "<a href=\"$PHP_SELF?f=$fid&page=managemenu\">$path</a><br />\n";
    }
?>
</td>
</tr>
</table>
