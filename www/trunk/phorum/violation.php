<?PHP
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   Copyright (C) 2000  Phorum Development Team                              //
//   http://www.phorum.org                                                    //
//                                                                            //
//   This program is free software. You can redistribute it and/or modify     //
//   it under the terms of the Phorum License Version 1.0.                    //
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
  if(file_exists("$include_path/header_$ForumConfigSuffix.php")){
    include "$include_path/header_$ForumConfigSuffix.php";
  }
  else{
    include "$include_path/header.php";
  }
?>
<center>
<table width="$ForumTableWidth" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td <?PHP echo bgcolor($ForumTableHeaderColor); ?> valign="TOP" nowrap><font color="<?PHP echo $ForumTableHeaderFontColor; ?>">&nbsp;<?PHP echo $lViolationTitle; ?></font></td>
  </tr>
  <tr>
    <td width="100%" align="LEFT" valign="MIDDLE" <?PHP echo bgcolor($ForumTableBodyColor2); ?>><?PHP echo $lViolation; ?></td>
</td>
</tr>
</table>
<?PHP

  if(file_exists("$include_path/footer_$ForumConfigSuffix.php")){
    include "$include_path/footer_$ForumConfigSuffix.php";    
  }
  else{
    include "$include_path/footer.php";
  }

?>