<?
require( "/www/freshports.org/_private/commonlogin.php3");
require( "/www/freshports.org/_private/getvalues.php3");
?>

<HTML>
<HEAD>
<meta name="Phorum Version" content="<?PHP echo $phorumver; ?>">
<meta name="Phorum DB" content="<?PHP echo $DB->type; ?>">
<meta name="PHP Version" content="<?PHP echo phpversion(); ?>">
<TITLE>phorum - <?PHP if(isset($ForumName)) echo $ForumName; ?><?PHP echo $title; ?></TITLE>
<STYLE> BODY {font-family: Arial, Helvetica;font-size: 10pt;}
  TD {font-family: Arial, Helvetica;font-size: 10pt;}
  INPUT {font-family: Arial, Helvetica;font-size: 10pt;}
  TEXTAREA {font-family: Arial, Helvetica;font-size: 10pt;width: 500px;}
  .forum_title {font-family: Arial, Helvetica;font-size: large;}
  .forum {
 	font-family: Arial, Helvetica;
 	font-size: medium;
 }
  .nav {font-family: MS Sans Serif,Geneva,sans-serif;font-size: 8pt;}</STYLE>
</HEAD>
<BODY BGCOLOR="#FFFFFF" LINK="#0000CC">
<? include("/www/freshports.org/_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">
<div class=forum_title><b>forum
<?PHP 
if ($ForumName != '') {
   echo " - $ForumName";
}
?></b></div>
</td></tr>
<tr><td valign="top" width="100%">

