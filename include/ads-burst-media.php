<?php
	#
	# $Id: ads-burst-media.php,v 1.2 2006-12-17 11:55:52 dan Exp $
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
  return '
<!-- BEGIN RICH-MEDIA Burst CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };
document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/v=2.1S/sz=125x125A/\'+rnum+\'/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');
</script><noscript><a href="http://www.burstnet.com/ads/cb4556a-map.cgi/ns/v=2.1S/sz=125x125A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/ns/v=2.1S/sz=125x125A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->
';
}

function Ad_468x60() {
  return '
<!-- BEGIN RICH-MEDIA NETWORK CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };

document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/v=2.1S/sz=468x60A/\'+rnum+\'/NI/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');

</script><noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.1S/sz=468x60A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.1S/sz=468x60A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->
  ';
}

function Ad_728x90() {
  return '
<!-- BEGIN RICH-MEDIA NETWORK CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };

document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/v=2.1S/sz=468x60A|728x90A/\'+rnum+\'/NI/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');

</script><noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.1S/sz=468x60A|728x90A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.1S/sz=468x60A|728x90A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->
  ';
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
  return '
<!-- BEGIN RICH-MEDIA NETWORK CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };
document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/v=2.1S/sz=120x600A/\'+rnum+\'/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');
</script><noscript><a href="http://www.burstnet.com/ads/sk4556a-map.cgi/ns/v=2.1S/sz=120x600A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/ns/v=2.1S/sz=120x600A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->
  ';
}

function Ad_160x600() {
  return '
  <!-- BEGIN RICH-MEDIA NETWORK CODE -->
  <script language="JavaScript" type="text/javascript">
  rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
  if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };
  document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/v=2.1S/sz=120x600A|160x600A/\'+rnum+\'/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');
  </script><noscript><a href="http://www.burstnet.com/ads/sk4556a-map.cgi/ns/v=2.1S/sz=120x600A|160x600A/" target="_top">
  <img src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/ns/v=2.1S/sz=120x600A|160x600A/" border="0" alt="Click Here"></a>
  </noscript>
  <!-- END BURST CODE -->
  
  ';
}

function Ad_468x60_Below() {
  return '
  <!-- BEGIN RICH-MEDIA Burst CODE -->
  <script language="JavaScript" type="text/javascript">
  rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
  if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };
  document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/ba4556a.cgi/v=2.1S/sz=468x60B/\'+rnum+\'/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');
  </script><noscript><a href="http://www.burstnet.com/ads/ba4556a-map.cgi/ns/v=2.1S/sz=468x60B/" target="_top">
  <img src="http://www.burstnet.com/cgi-bin/ads/ba4556a.cgi/ns/v=2.1S/sz=468x60B/" border="0" alt="Click Here"></a>
  </noscript>
  <!-- END BURST CODE -->
  
  ';
}

function Ad_300x250() {
  return '
  <!-- BEGIN RICH-MEDIA Burst CODE -->
  <script language="JavaScript" type="text/javascript">
  rnum=Math.round(Math.random() * 100000); ts=String.fromCharCode(60);
  if (window.self != window.top) { nf=\'\' } else { nf=\'NF/\' };
  document.write(ts+\'script src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/v=2.1S/sz=300x250A/NZ/\'+rnum+\'/\'+nf+\'RETURN-CODE/JS/">\'+ts+\'/script>\');
  </script><noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.1S/sz=300x250A/" target="_top">
  <img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.1S/sz=300x250A/" border="0" alt="Click Here"></a>
  </noscript>
  <!-- END BURST CODE -->
  ';
}
