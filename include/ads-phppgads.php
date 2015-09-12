<?php
	#
	# $Id: ads-phppgads.php,v 1.3 2007-08-26 22:20:40 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');
	
function Ad_PhpPgAdsBase($Zone, $N) {

	$Code = '
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
</script><noscript><a href=\'http://ads.unixathome.org/phpPgAds/adclick.php?n=' . $N . '\' target=\'_blank\'><img src=\'http://ads.unixathome.org/phpPgAds/adview.php?what=zone:' . $Zone . '&amp;n=' . $Zone . '\' border=\'0\' alt=\'\'></a></noscript>

	';
	
	return $Code;
}

function Ad_125x125() {
	$Code = Ad_PhpPgAdsBase(59, 'a30fb206');
	return $Code;
}

function Ad_468x60() {
	$Code = Ad_PhpPgAdsBase(56, 'a0185fbb');	# really at 728x90 (for Verio)
	return $Code;
}

function Ad_728x90() {
	$Code = Ad_PhpPgAdsBase(56, 'ab34d058');
	return $Code;
}

function Ad_728x90PortDescription() {
  return Ad_728x90();
}

function Ad_728x90PhorumBottom() {
  return Ad_468x60_Below();
}

function Ad_728x90PhorumTop() {
  return Ad_728x90();
}

function Ad_120x600() {
	$Code = Ad_PhpPgAdsBase(58, 'aba75b2d');	# actualy 160x600 for Verio
	return $Code;
}

function Ad_160x600() {
	$Code = Ad_PhpPgAdsBase(58, 'a02ff4f4');
	return $Code;
}

function Ad_468x60_Below() {
  return '';
}

function Ad_300x250() {
	$Code = Ad_PhpPgAdsBase(60, 'ae3d342e');
	return $Code;
}
