<HTML>
<HEAD>
<TITLE>phorum admin</TITLE>
<?PHP if(strstr($HTTP_USER_AGENT, "MSIE")){ ?>
<STYLE>   INPUT.TEXT  {
    font-family : Arial,Helvetica;
    font-size : 12pt;
    width : 150px;
    background-color : White;
    border-style : inset;
    border-width : 2px;
    border-color : White;
    }
    
    INPUT.BUTTON  {
    font-family : Arial,Helvetica;
    font-size : 10pt;
    width : 100px;
    border-width : 2px;
    background-color : Silver;
    border-color : White;
    font-weight : bold;
    }
    
    SELECT  {
    font-family : Arial,Helvetica;
    font-size : 12pt;
    width : 150px;
    border-width : 2px;
    border-style : inset;
    background-color : White;
    border-color : White;
    }
    
    SELECT.BIG  {
    width: auto;
    }
    
    TEXTAREA  {
    font-family : Arial,Helvetica;
    font-size : 12pt;
    width : 500px;
    border-width : 2px;
    border-style : inset;
    background-color : White;
    border-color : White;
   }
   
    .message  {
   	width : 400px;
   	border-width : 2px;
   	background-color : Silver;
   	border-color : White;
   	border-style : inset;
   	text-align : center;
   }
   
    }
</STYLE>
<?PHP } ?>
</HEAD>
<BODY BGCOLOR="#E1E1E1" VLINK="#000080" LINK="#0000FF" alink="#808000">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
    <td width="50%"><font face="Arial,Helvetica">version: <?PHP echo $phorumver; ?>
<?PHP  
  if($use_security){
    if($phorum_logged_in && $fullaccess){
      echo " | <a href=\"$myname?logout=1\">logout</a>";
    }
  }
?></td>
    <td width="50%" align="right"><font face="Arial,Helvetica"><?
if(isset($DB->connect_id)){
  if($DB->connect_id){
    echo "Database Connection Established";
  }
  else{
    echo "<b>No Database Connection Available</b>";
  }  
}
else{
  echo "<b>No Database Connection Available</b>";
}  
?></font></td>
</tr>
</table>
<br>
<center>
<font size=+4 face="Impact,Arial,Helvetica" >phorum </font><font size=+4 face="Impact,Arial,Helvetica" color="#800000">admin</font><p>
<? if($fullaccess){ ?>
<P ALIGN=CENTER><A HREF="<? echo $myname; ?>"><font face="Arial,Helvetica"><b>main</b></font></a> | <A HREF="<?PHP echo "$forum_url/$forum_page.$ext"; ?>"><font face="Arial,Helvetica"><b>forum</b></font></a></P>
<? } ?>
<p>
