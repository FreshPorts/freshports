<?php
	#
	# $Id: ads.php,v 1.1.2.2 2006-07-01 17:42:28 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/adsense.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/burstmedia.php');
	
	#
	# Google Ads need Javascript
	# Burst does not.
	#


function Ad_468x60() {

return AdSense468x60() . "\n" . 
'
<noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.0S/sz=468x60A|728x90A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.0S/sz=468x60A|728x90A/" border="0" alt="Click Here"></a>
</noscript>
';

}


function Ad_728x90() {

return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=a14b3a43" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:23&amp;n=a14b3a43" border="0" alt=""></a>

';

}


function Ad_120x160() {

return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=ad672337" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:11&amp;n=ad672337" border="0" alt=""></a>

';
}


function Ad_160x160() {

return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=ac3ee8c9" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:22&amp;n=ac3ee8c9" border="0" alt=""></a>

';
}





function Ad_125x125() {

return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=aca219aa" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:26&amp;n=aca219aa" border="0" alt=""></a>

';

}

function Ad_468x60_Below() {
return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=ad3e7d4a" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:4&amp;n=ad3e7d4a" border="0" alt=""></a>

';
}

function Ad_300x250() {

return '

<a href="http://ads.unixathome.org/phpPgAds/adclick.php?n=a831bb78" target="_blank"><img src="http://ads.unixathome.org/phpPgAds/adview.php?what=zone:25&amp;n=a831bb78" border="0" alt=""></a>

';

}


?>