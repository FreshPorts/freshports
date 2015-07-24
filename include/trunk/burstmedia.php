<?php
	#
	# $Id: burstmedia.php,v 1.2 2006-12-17 11:55:52 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#

	require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/constants.php');


function BurstMediaAd() {

return '

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

return '

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

return '

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
return '

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
return '

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

