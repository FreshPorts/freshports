<?php
	#
	# $Id: burstmedia.php,v 1.1.2.13 2004-09-22 23:08:20 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/constants.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/burstmedia.php');


function BurstMediaAd() {

echo '

<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000);

document.write(\'<scr\'+\'ipt src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/v=2.0S/sz=468x60A|728x90A/\'+rnum+\'/NI/RETURN-CODE/JS/"><\/scr\'+\'ipt>\');


</script>
<noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.0S/sz=468x60A|728x90A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.0S/sz=468x60A|728x90A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->

';

}

function BurstSkyscraperAd() {

echo '

<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000);
document.write(\'<scr\'+\'ipt src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/v=2.0S/sz=120x600A|160x600A/\'+rnum+\'/RETURN-CODE/JS/"><\/scr\'+\'ipt>\');
</script>
<noscript><a href="http://www.burstnet.com/ads/sk4556a-map.cgi/ns/v=2.0S/sz=120x600A|160x600A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/sk4556a.cgi/ns/v=2.0S/sz=120x600A|160x600A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->

';
}





function Burst_125x125() {

echo '

<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000);
document.write(\'<scr\'+\'ipt src="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/v=2.0S/sz=125x125A/\'+rnum+\'/RETURN-CODE/JS/"><\/scr\'+\'ipt>\');
</script>
<noscript><a href="http://www.burstnet.com/ads/cb4556a-map.cgi/ns/v=2.0S/sz=125x125A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/ns/v=2.0S/sz=125x125A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->

';

}

function Burst_468x60_Below() {
echo '

<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000);
document.write(\'<scr\'+\'ipt src="http://www.burstnet.com/cgi-bin/ads/ba4556a.cgi/v=2.0S/sz=468x60B/\'+rnum+\'/RETURN-CODE/JS/"><\/scr\'+\'ipt>\');
</script>
<noscript><a href="http://www.burstnet.com/ads/ba4556a-map.cgi/ns/v=2.0S/sz=468x60B/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ba4556a.cgi/ns/v=2.0S/sz=468x60B/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->

';
}

function Burst_300x250() {

GLOBAL $ShowAds;

	if ($ShowAds) {
echo '

<!-- BEGIN RICH-MEDIA BURST! CODE -->
<script language="JavaScript" type="text/javascript">
rnum=Math.round(Math.random() * 100000);
document.write(\'<scr\'+\'ipt src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/v=2.0S/sz=300x250A/\'+rnum+\'/RETURN-CODE/JS/"><\/scr\'+\'ipt>\');
</script>
<noscript><a href="http://www.burstnet.com/ads/ad4556a-map.cgi/ns/v=2.0S/sz=300x250A/" target="_top">
<img src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi/ns/v=2.0S/sz=300x250A/" border="0" alt="Click Here"></a>
</noscript>
<!-- END BURST CODE -->

';

	}
}

function Burst_300x250_table() {

	echo '<table border="0" cellpadding="8" cellspacing="0" align="right"><tr><td>' . "\n";
	echo Burst_300x250();
	echo '</td></tr></table>' ."\n";

}

?>