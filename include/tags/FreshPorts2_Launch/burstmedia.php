<?
	# $Id: burstmedia.php,v 1.1.2.2 2002-05-18 05:58:28 dan Exp $
	#
	# Copyright (c) 1998-2002 DVL Software Limited

	require_once($_SERVER["DOCUMENT_ROOT"] . "/include/constants.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/include/burstmedia.php");


function BurstMediaCode() {
#
# This is required on all pages which contain Burst Ads.  It's the base code.
#

echo '
<!-- BEGIN RICH-MEDIA BURST! CODE --> 
<SCRIPT TYPE="text/javascript"> 
<!-- /* © 1997-2001 BURST! Media, LLC. All Rights Reserved.*/ 
function ShowBurstAd(adcode, width, height) {
 var bN = navigator.appName;
 var bV = parseInt(navigator.appVersion);   
 var base=\'http://www.burstnet.com/\';   
 var Tv=\'\';   
 var agt=navigator.userAgent.toLowerCase();   
 if (bV>=4)  
 {ts=window.location.pathname+window.location.search;
  i=0; Tv=0; while (i< ts.length)
    { Tv=Tv+ts.charCodeAt(i); i=i+1; } Tv="/"+Tv;}
  else   {Tv=escape(window.location.pathname);
  if( Tv.charAt(0)!=\'/\' ) Tv="/"+Tv; 
    else if (Tv.charAt(1)=="/")
 Tv="";     
 if( Tv.charAt(Tv.length-1) == "/")
   Tv = Tv + "_";}   
 if (bN==\'Netscape\'){        
  if ((bV>=4)&&(agt.indexOf("mac")==-1))  
 { document.write(\'<s\'+\'cript src="\'+ 
  base+\'cgi-bin/ads/\'+adcode+\'.cgi/RETURN-CODE/JS\' 
  +Tv+\'">\'); 
  document.write(\'</\'+\'script>\');
 }
   else if (bV>=3) {document.write(\'<\'+\'a href="\'+base+\'ads/\' + 
  adcode + \'-map.cgi\'+Tv+\'"target=_top>\');
  document.write(\'<img src="\' + base + \'cgi-bin/ads/\' +
  adcode + \'.cgi\' + Tv + \'" width="\' + width + \'" height="\' + height + \'"\' +
  \' border="0" alt="Click Here"></a>\');}   
}
if (bN==\'Microsoft Internet Explorer\')    
document.write(\'<ifr\'+\'ame id="BURST" src="\'+base+\'cgi-bin/ads/\'  
+     
adcode + \'.cgi\' + Tv + \'/RETURN-CODE" width="\' + width + \'" height="\' + height + \'"\' +     
\'marginwidth="0" marginheight="0" hspace="0" vspace="0" \' +     
\'frameborder="0" scrolling="no"></ifr\'+\'ame>\');  
}
//-->
</script>
<!-- END BURST CODE -->
';
}

function BurstMediaAd() {
#
# This goes at the top of each article and show the ad, the graphic, and the links
#
GLOBAL $AddressForAds;

echo '
	<!-- BEGIN RICH-MEDIA BURST! CODE --> 
	<SCRIPT TYPE="text/javascript" >
	<!--
	ShowBurstAd(\'ad4556a\',\'468\',\'60\');
	// --></script>
	<noscript><a href="http://www.burstnet.com/ads/ad4556a-
	map.cgi/ns" target="_top"><img src="http://www.burstnet.com/cgi-
	bin/ads/ad4556a.cgi/ns" <width="468" height="60" 
	border="0" alt="Click Here"></a> 
	</noscript>
	<!-- END BURST CODE -->
';

#<BR>
#
#<small>Your ad here.&nbsp; Please <a href="mailto:" . $AddressForAds . "?subject=your ad here">contact us</A> for details.</small>

}

function BurstSkyscraperAd() {

GLOBAL	$DiaryBGCOLOR;
GLOBAL	$DiaryTEXT;

echo '
<SCRIPT TYPE="text/javascript">
<!-- /* © 1997-2002 BURST! Media, LLC. All Rights Reserved.*/
var TheAdcode = \'sk4556a\';
var bN = navigator.appName;
var bV = parseInt(navigator.appVersion);
var base=\'http://www.burstnet.com/\';
var Tv=\'\';
var backColor=\'' . str_replace('#', '', $DiaryBGCOLOR) . '\';
var fontColor=\'' . str_replace('#', '', $DiaryTEXT)    . '\';
var agt=navigator.userAgent.toLowerCase();
if (bV>=4)
  {ts=window.location.pathname+window.location.search;
   i=0; Tv=0; while (i< ts.length)
      { Tv=Tv+ts.charCodeAt(i); i=i+1; } Tv="/"+Tv;}
  else   {Tv=escape(window.location.pathname);
   if( Tv.charAt(0)!=\'/\' ) Tv="/"+Tv;
          else if (Tv.charAt(1)=="/")
Tv="";
  if( Tv.charAt(Tv.length-1) == "/")
    Tv = Tv + "_";}   document.write(\'<ifr\'+\'ame id=BURST src="\'+base+\'cgi-bin/ads/\' +
  TheAdcode + \'.cgi\' + Tv +  \'/zg\' + backColor + \'x\' + fontColor + \'l\' + fontColor + 
  fontColor + \'k\' + fontColor + \'/RETURN-CODE" width=165 height=600\' +
  \'v\' +\'marginwidth=0 marginheight=0 hspace=0 vspace=0 \' +
  \'frameborder=0 scrolling=no>\');
  document.write(\'<A HREF="http://www.FreshPorts.org/"><img src="/images/freshports-160x600.gif" width=160 height=600 BORDER="0"></A>\');
  document.write(\'</ifr\'+\'ame>\');   
// -->
</script>';
}



function Burst_120x160() {

echo '	<!-- BEGIN BURST! CODE --> 
	<!--webbot bot="HTMLMarkup" startspan -->
	<IFRAME SRC="http://www.burstnet.com/cgi-bin/ads/bt4556a.cgi/RETURN-CODE/if/996362698;/" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" width="120" height="60">
	<!--webbot bot="HTMLMarkup" endspan --> 
	<a target="_top" HREF="http://www.burstnet.com/ads/bt4556a-map.cgi/996362698"><img SRC="http://www.burstnet.com/cgi-bin/ads/bt4556a.cgi/996362698" border="0" width="120" height="60" alt="Please support our sponsor."></a>
	<!--webbot            bot="HTMLMarkup" startspan --></IFRAME>
	<!--webbot bot="HTMLMarkup" endspan -->
	<!-- END BURST! CODE -->
';

}

function Burst_125x125() {

echo '  <!-- BEGIN BURST! CODE -->
	<IFRAME SRC="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/RETURN-CODE/if/996362698/" scrolling="no" marginwidth="0"
	marginheight="0" frameborder="0" vspace="0" hspace="0" width="125" height="125">
	<a target="_top" HREF="http://www.burstnet.com/ads/cb4556a-map.cgi/996362698">
	<img SRC="http://www.burstnet.com/cgi-bin/ads/cb4556a.cgi/996362698" border="0" width="125" height="125" alt="Please support our sponsor."></a></IFRAME>
	<!-- END BURST! CODE -->
';

}

function Burst_468x60_Below() {
	if (rand(1, 100) <= 50) {
echo '	<P ALIGN="center">
	<!-- BEGIN RICH-MEDIA BURST! CODE --> 
	<SCRIPT TYPE="text/javascript">
	<!--
	ShowBurstAd(\'ba4556a\',\'468\',\'60\');
	// --></script>
	<noscript><a href="http://www.burstnet.com/ads/ba4556a-map.cgi/ns" target="_top"><img src="http://www.burstnet.com/cgi-bin/ads/ba4556a.cgi/ns" width="468" height="60" border="0" alt="Click Here"></a>
	</noscript>
	<!-- END BURST CODE -->
	</P>
';
	} else {
		echo '<a href="http://magazine.daemonnews.org/" target="_top"><img src="/ads/daemonnews.gif" width="468" height="60"
		border="0" alt="Daemon News - Bringing BSD Together"></a>';
	}
}


?>