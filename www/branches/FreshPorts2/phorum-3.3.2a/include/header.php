<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "XHTML1-t.dtd">
<html>
<head>
<meta name="Phorum Version" content="<?php echo $phorumver; ?>" />
<meta name="Phorum DB" content="<?php echo $DB->type; ?>" />
<meta name="PHP Version" content="<?php echo phpversion(); ?>" />
<title>phorum<?php if(isset($ForumName)) echo " - $ForumName"; ?><?php echo initvar("title"); ?></title>
<link rel="STYLESHEET" type="text/css" href="<?php echo phorum_get_file_name("css"); ?>" />
</head>
<body bgcolor="<?php echo (empty($ForumBodyColor)) ? $default_body_color : $ForumBodyColor; ?>" link="<?php echo (empty($ForumBodyLinkColor)) ? $default_body_link_color : $ForumBodyLinkColor; ?>" alink="<?php echo (empty($ForumBodyALinkColor)) ? $default_body_alink_color : $ForumBodyALinkColor; ?>" vlink="<?php echo (empty($ForumBodyVLinkColor)) ? $default_body_vlink_color : $ForumBodyVLinkColor; ?>">
<div class="PhorumForumTitle"><b><?php echo $ForumName; ?></b></div>
<br>
