<script language="php">
if ($submit) {

// process form

//  while (list($name, $value) = each($HTTP_POST_VARS)) {
//    echo "$name = $value<br>\n";
//  }

  $OK = 1;

  $errors = "";

  require( "_private/commonlogin.php3");

  // test for existance of user id
  $Cookie = UserToCookie($UserID);
  $sql = "select * from users where lower(login) = lower('$Cookie')".
         " and password = '$Password'";
  $result = mysql_query($sql, $db) or die('query failed');


  // create user id if not found
  if(!mysql_numrows($result)) {
     $LoginFailed = 1;
  } else {
    SetCookie("visitor", $Cookie, time() + 60*60*24*120, '/');
    header("Location: ../../");  /* Redirect browser to PHP web site */
    exit;  /* Make sure that code below does not get executed when we redirect. */  }
}
</script>

<html>

<head>
<title>The FreeBSD Diary -- Login</title>
<meta name="description" content="Login">
<meta name="keywords" content="FreeBSD">
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
</head>

<body bgcolor="#ffffff" link="#0000cc">

<table width="100%">
  <tr>
    <td><p align="center"><!-- BEGIN RICH-MEDIA BURST! CODE --> <script language="JavaScript">
<!-- /* © 1997-1999 BURST! Media, LLC. All Rights Reserved.*/
var TheAdcode = 'ad4556a';
var bN = navigator.appName;
var bV = parseInt(navigator.appVersion);
var base='http://www.burstnet.com/';
var Tv='';
var agt=navigator.userAgent.toLowerCase();
if (bV>=4)
  {ts=window.location.pathname+window.location.search;
   i=0; Tv=0; while (i< ts.length)
      { Tv=Tv+ts.charCodeAt(i); i=i+1; } Tv="/"+Tv;}
  else   {Tv=escape(window.location.pathname);
   if( Tv.charAt(0)!='/' ) Tv="/"+Tv;
          else if (Tv.charAt(1)=="/")
Tv="";
  if( Tv.charAt(Tv.length-1) == "/")
    Tv = Tv + "_";}
if (bN=='Netscape'){
     if ((bV>=4)&&(agt.indexOf("mac")==-1))
{  document.write('<s'+'cript src="'+
      base+'cgi-bin/ads/'+TheAdcode+'.cgi/RETURN-CODE/JS'
      +Tv+'">');
     document.write('</'+'script>');
}
      else if (bV>=3) {document.write('<'+'a href="'+base+'ads/'+
        TheAdcode+'-map.cgi'+Tv+'"target=_top>');
        document.write('<img src="' + base + 'cgi-bin/ads/' +
        TheAdcode + '.cgi' + Tv + '" ismap width="468" height="60"' +
        ' border="0" alt="Click Here"></a>');}
 }
if (bN=='Microsoft Internet Explorer')
 document.write('<ifr'+'ame id="BURST" src="'+base+'cgi-bin/ads/'
+
  TheAdcode + '.cgi' + Tv + '/RETURN-CODE" width="468" height="60"' +
  'marginwidth="0" marginheight="0" hspace="0" vspace="0" ' +
  'frameborder="0" scrolling="no"></ifr'+'ame>');
// -->
</script> <noscript><a
    href="http://www.burstnet.com/ads/ad4556a-map.cgi" target="_top"> <img
    src="http://www.burstnet.com/cgi-bin/ads/ad4556a.cgi" width="468" height="60" border="0"
    alt="Click Here"></a> </noscript> <!-- END BURST CODE --> <br>
    <small>Your ad here.&nbsp; Please <a
    href="mailto:ads@freebsddiary.org?subject=your ad here">contact us</a> for details.</small>
    </p>
    <p align="center"><a href="http://www.freebsddiary.org/"><img
    src="http://www.freebsddiary.org/images/diary.gif" alt="The FreeBSD Diary" border="0"
    width="420" height="105"></a> </p>
    <p align="center">[ <a href="">Home</a> | <a href="topics.php3">Topics</a> | <a
    href="chronological.php3">Index</a> | <a href="help.html">Web Resources</a> | <a
    href="booksmags.html">Books/Mags</a> | <a href="topology.html">Topology</a> | <a
    href="search.html">Search</a> | <a href="feedback.html">Feedback</a> | <a href="faq.html">FAQ</a>
    | <a href="phorum/list.php3?num=1">Forum</a> ]</td>
  </tr>
  <tr>
    <td><script language="php">
  
if ($LoginFailed) {   
echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>                                                                                                          
<td>
<table width=100% border=0 cellpadding=1>
<tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>Login Failed!</font></b></td>
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=3 cellspacing=0 border=0>
  <tr valign=top>
   <td><img src="images/warning.gif"></td>
   <td width=100%>
  <p>The User ID and password you supplied could not be used to login.  This could be for one of the following reasons:</p>
 <ul>
 <li>The login id is incorrect
 <li>The password is incorrect
 <li>Both of the above
 </ul>
 <p>If you need help, please ask in the forum. </p>
 </td>
 </tr>
 </table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>';
}


echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>';


echo '<table width=100% border=0 cellpadding=1 bgcolor="#AD0040">';

echo '<tr bgcolor="#AD0040"><td bgcolor="#AD0040"><font color="#ffffff"><big><big>Login Details</big></big></font></td></tr>';

echo '<tr><td bgcolor="#ffffff">';
include ("_private/login.inc.php3");

echo "</td>
</tr>
</table>
</td>
</tr>
</table>";

</script></td>
  </tr>
  <tr>
    <td><p align="center">[ <a href="">Home</a> | <a href="topics.php3">Topics</a> |
    <a href="chronological.php3">Index</a> | <a href="help.html">Web Resources</a> | <a
    href="booksmags.html">Books/Mags</a> | <a href="topology.html">Topology</a> | <a
    href="search.html">Search</a> | <a href="feedback.html">Feedback</a> | <a href="faq.html">FAQ</a>
    | <a href="phorum/list.php3?num=1">Forum</a> ] </p>
    <table border="0" width="100%">
      <tr>
        <td valign="top" align="right"><p align="right"><font size="1">This page last updated:
        Wednesday, 23 February 2000<br>
        <a href="legal.html" target="_top">Copyright</a> 1997, 1998, 1999, 2000 <a
        href="http://www.dvl-software.com/">DVL Software Limited</a>.&nbsp; All rights reserved.</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
