<?php
	#
	# $Id: header.php,v 1.1.2.4 2003-11-11 16:29:16 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#
?>
<body bgcolor="#ffffff" link="#0000cc">
<?
# get the minutes (well, actually it's seconds now...)
$Minutes = date("s");
if ($Minutes >= 0 and $Minutes < 20) {
   $Image = "bsdcon-banner1.gif";
} else {
   if ($Minutes >= 20 and $Minutes < 40) {
      $Image = "bsdcon-banner2.gif";
   } else {
      $Image = "bsdcon-banner3.gif";
   }
}
?>

<a href="/">
<img src="/images/freshports.jpg" 
alt="freshports.org - the place for ports" width="512" height="110" border="0"></a>
