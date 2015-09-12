<?php
	#
	# $Id: ads-google-adsense.php,v 1.2 2006-12-17 11:55:52 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/adsense.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/burstmedia.php');
	
	#
	# Google Ads need Javascript
	# Burst does not.
	#



function Ad_PhpPgAdsBase($Zone, $N) {

  return 
'
<script language=\'JavaScript\' type=\'text/javascript\' src=\'http://ads.unixathome.org/phpPgAds/adx.js\'></script>
<script language=\'JavaScript\' type=\'text/javascript\'>
<!--
   if (!document.phpAds_used) document.phpAds_used = \',\';
   phpAds_random = new String (Math.random()); phpAds_random = phpAds_random.substring(2,11);
   
   document.write ("<" + "script language=\'JavaScript\' type=\'text/javascript\' src=\'");
   document.write ("http://ads.unixathome.org/phpPgAds/adjs.php?n=" + phpAds_random);
   document.write ("&amp;what=zone:' . $Zone . '");
   document.write ("&amp;exclude=" + document.phpAds_used);
   if (document.referrer)
      document.write ("&amp;referer=" + escape(document.referrer));
   document.write ("\'><" + "/script>");
//-->
</script><noscript><a href=\'http://ads.unixathome.org/phpPgAds/adclick.php?n=' . $N . '\' target=\'_top\'><img src=\'http://ads.unixathome.org/phpPgAds/adview.php?what=zone:' . $Zone . '&amp;n=' . $N . '\' border=\'0\' alt=\'\'></a></noscript>
';
}

function Ad_125x125() {
  return Ad_PhpPgAdsBase(43, 'ab4a7d2c');
}

function Ad_468x60() {
#  return Ad_PhpPgAdsBase(44, 'addffeb2');
return '<script type="text/javascript"><!--
google_ad_client = "pub-0711826105743221";
google_alternate_ad_url = "http://ads.unixathome.org/AdSense/defaults/728x90.html";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text";
google_ad_channel ="";
google_color_border = "2D5893";
google_color_bg = "99AACC";
google_color_link = "000000";
google_color_url = "000099";
google_color_text = "003366";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>';
}

function Ad_728x90() {
#  return Ad_PhpPgAdsBase(42, 'a6a018dd');
  return '<script type="text/javascript"><!--
  google_ad_client = "pub-0711826105743221";
  google_alternate_ad_url = "http://ads.unixathome.org/AdSense/defaults/728x90.html";
  google_ad_width = 728;
  google_ad_height = 90;
  google_ad_format = "728x90_as";
  google_ad_type = "text";
  google_ad_channel ="";
  google_color_border = "2D5893";
  google_color_bg = "99AACC";
  google_color_link = "000000";
  google_color_url = "000099";
  google_color_text = "003366";
  //--></script>
  <script type="text/javascript"
    src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>';
}

function Ad_728x90PortDescription() {
return '<script type="text/javascript"><!--
google_ad_client = "pub-0711826105743221";
google_alternate_ad_url = "http://ads.unixathome.org/AdSense/defaults/728x90.html";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text";
google_ad_channel ="5402590257";
google_color_border = "2D5893";
google_color_bg = "99AACC";
google_color_link = "000000";
google_color_url = "000099";
google_color_text = "003366";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>';
}

function Ad_728x90PhorumBottom() {
  return Ad_PhpPgAdsBase(48, 'a3752dcd');
}

function Ad_728x90PhorumTop() {
  return Ad_PhpPgAdsBase(47, 'a67dfc4c');
}

function Ad_120x600() {
  return Ad_PhpPgAdsbase(11, 'af3231e2');
}

function Ad_160x600() {
  return Ad_PhpPgAdsBase(40, 'a6cfe162');
}

function Ad_468x60_Below() {
  return Ad_PhpPgAdsBase(45, 'a4123951');
}

function Ad_300x250() {
  return Ad_PhpPgAdsBase(41, 'a98095ab');
}
