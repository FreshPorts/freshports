<?php
	#
	# $Id: adsense.php,v 1.1.2.3 2006-07-01 17:46:42 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');

function AdSense468x60() {

return '
<script type="text/javascript"><!--
google_ad_client = "pub-0711826105743221";
google_ad_width = 468;
google_ad_height = 60;
google_ad_format = "468x60_as";
google_ad_type = "text_image";
google_ad_channel ="";
google_color_border = "2D5893";
google_color_bg = "99AACC";
google_color_link = "000000";
google_color_url = "000099";
google_color_text = "003366";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
';
}

function AdSense728x90() {

return '
<script type="text/javascript"><!--
google_ad_client = "pub-0711826105743221";
google_alternate_color = "FFFFFF";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text_image";
google_ad_channel ="5402590257";
google_color_border = "336699";
google_color_bg = "FFFFFF";
google_color_link = "0000FF";
google_color_url = "008000";
google_color_text = "000000";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
 ';
 
}
  
?>