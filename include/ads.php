<?php
	#
	# $Id: ads.php,v 1.2 2006-12-17 11:55:52 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	
	#
	# Google Ads need Javascript
	# Burst does not.
	#

	# assign a default value if not supplied
	if (!IsSet($PrimaryAdSource)) {
	  $PrimaryAdSource = '';
    }

    switch($PrimaryAdSource) {
      case 'PHPPGADS':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/ads-phppgads.php');
        break;

      case 'GOOGLE':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/ads-google-adsense.php');
        break;

      case 'BURST':
      default:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/ads-burst-media.php');
        break;
      }

function Ad_Referral_120x60() {
  if (rand(0,1) == 1) {
  return '
  <script type="text/javascript"><!--
  google_ad_client = "pub-0711826105743221";
  google_ad_width = 120;
  google_ad_height = 60;
  google_ad_format = "120x60_as_rimg";
  google_cpa_choice = "CAAQrNukgwIaCDvcYORRSkDWKKTP6n4";
  google_ad_channel = "7878385579";
  //--></script>
  <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
  </script>
  ';
  } else {
  return '
  <script type="text/javascript"><!--
  google_ad_client = "pub-0711826105743221";
  google_ad_width = 120;
  google_ad_height = 60;
  google_ad_format = "120x60_as_rimg";
  google_cpa_choice = "CAAQzdGWhAIaCAhe1XbaOP58KIHD93M";
  google_ad_channel = "7878385579";
  //--></script>
  <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
  </script>
  ';
  }
}

