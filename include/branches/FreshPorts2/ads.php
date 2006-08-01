<?php
	#
	# $Id: ads.php,v 1.1.2.7 2006-08-01 17:34:13 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	
	#
	# Google Ads need Javascript
	# Burst does not.
	#

	# assign a default value if not supplied
	if (!IsSet($PrimaryAdSource)) {
	  $PrimaryAdSource = '';
    }

    switch($PrimaryAdSource) {
      case 'GOOGLE':
        require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ads-google-adsense.php');
        break;

      case 'BURST':
      default:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ads-burst-media.php');
        break;
      }
?>