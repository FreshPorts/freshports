<?
require( "../_private/commonlogin.php3");
require( "../_private/getvalues.php3");
require( "../_private/freshports.php3");
?>
<HTML>
<HEAD>
<meta name="Phorum Version" content="<?PHP echo $phorumver; ?>">
<meta name="Phorum DB" content="<?PHP echo $DB->type; ?>">
<meta name="PHP Version" content="<?PHP echo phpversion(); ?>">
<TITLE>phorum - <?PHP if(isset($ForumName)) echo $ForumName; ?><?PHP echo $title; ?></TITLE>
<STYLE>.nav {font-family: MS Sans Serif,Geneva,sans-serif;font-size: 8pt;}</STYLE>
</HEAD>
<BODY BGCOLOR="#FFFFFF" LINK="#0000FF" ALINK="#FF0000" VLINK="#330000">
<? include("../_private/header.inc") ?>
<table width="100%" border="0">
<tr><td colspan="2">
<div class=forum_title><b><?PHP echo $ForumName; ?></b></div>
</td></tr>
<tr><td valign="top" width="100%">
<center>

