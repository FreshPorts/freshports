<body bgcolor="#ffffff" link="#0000cc">
<?
# get the minutes
$Minutes = date("i");
if ($Minutes >= 0 and $Minutes < 20) {
   $Image = "bsdcon-banner1.gif";
} else {
   if ($Minutes >= 20 and $Minutes < 40) {
      $Image = "bsdcon-banner2.gif";
   } else {
      $Image = "bsdcon-banner3.gif";
   }
}
?>

<center>
<a href="http://www.freesoftware.com/cgi-bin/ad_redirect.pl?URL=http://www.bsdcon.com&IMAGE=<? echo $Image?>"><img src="/ads/<? echo $Image?>" width="470" height="62" alt="BSD Con 2000" border="0"></a>
</center>
<a href="http://<? echo $HTTP_HOST?>/">
<img src="http://<? echo $HTTP_HOST?>/images/freshports.jpg" 
alt="freshports.org - the place for ports" width="512" height="110" border="0"></a>
