<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "XHTML1-t.dtd">
<html>
<head>
<title>phorum admin</title>
<?php if(!strstr($HTTP_USER_AGENT, "Mozilla/4") || strstr($HTTP_USER_AGENT, "MSIE")){ ?>
<style>
    body
    {
        color: #000000;
        background: #e1e1e1;
        font-size: 13px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        padding:5px;
        margin:0px;
    }

    th
    {
        font-size: 13px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-weight: bold;
        text-align: left;
        background-color: #F0F0F0;
        border-collapse: collapse;
        border-bottom-width : 1px;
        border-top-width : 0px;
        border-left-width : 0px;
        border-right-width : 0px;
        border-style : solid;
        border-color : Gray;
    }

    td
    {
        font-size: 13px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        text-align: left;
    }

    a
    {
        font-weight:bold;
        color:Blue;
        text-decoration:none;
        outline:none;
    }

    p
    {
        padding: 0px 0px 0px 0px;
        margin: 0px 0px 10px 0px;
    }

    img
    {
        border:none;
    }

    input, select
    {
        font-size : 13px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
    }

    input.login
    {
        border-width : 1px;
        border-style : solid;
        border-color : Gray;
        font-family : "Lucida Sans","Lucida Grande",Arial;
        font-size : 11px;
    }

    table.box-table
    {
        border-width : 1px;
        border-style : solid;
        border-color : Gray;
        background-color: White;
        border-collapse: collapse;
    }

    table.box-table td
    {
        border-collapse: collapse;
        border-bottom-width : 1px;
        border-top-width : 0px;
        border-left-width : 0px;
        border-right-width : 0px;
        border-style : solid;
        border-color : Gray;
    }

    td.table-header
    {
        font-size: 13px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-weight: bold;
        background-color: Navy;
        color: White;
        text-align: center;
    }

    td.table-header a
    {
        color: White;
    }

    .nav
    {
        font-size: 11px;
    }

    #message
    {
        width: 300px;
        border-width: 1px;
        border-style: solid;
        padding: 3px;
        background-color: White;
    }

    #title
    {
        font-size: 14px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-weight: bold;
        display: inline;
    }

</style>
<?php } ?>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
    <td width="50%" class="nav" valign="top"><?php if(!empty($q)){ ?><a href="<?php echo $PHP_SELF; ?>">Main Menu</a> | <A HREF="<?php echo "$forum_url/$forum_page.$ext"; ?>">Forum Index</a> | <a href="<?php echo $PHP_SELF; ?>?logout=1">Logout</a><?php } ?></td>
    <td width="50%" align="right" valign="top" class="nav" style="text-align: right;"><div id="title">PHORUM ADMIN</div><br /><?php
if(!empty($q)){
    if(@isset($DB->connect_id)){
      if($DB->connect_id){
        echo "Database Connection Established";
      }
      else{
        echo "<b>Database Connection Failed</b>";
      }
    }
    else{
      echo "<b>No Database Connection Available</b>";
    }
    ?><br />version: <?php echo $phorumver;
}
?>
</td>
</tr>
</table>
<center>
<p>
<?php
  if($GLOBALS["message"]){
    echo "<p><div align=\"center\" id=\"message\">$GLOBALS[message]</div>\n";
    $GLOBALS["message"]="";
  }
?>