<script language="php">

require( "/www/freshports.org/_private/commonlogin.php3");
//require( "_private/getvalues.php3");

// if we don't know who they are, we'll make sure they login first
if (!$visitor) {
	header("Location: login.php3?origin=" . $PHP_SELF);  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
}

if ($submit) {

// process form

//  while (list($name, $value) = each($HTTP_POST_VARS)) {
//    echo "$name = $value<br>\n";
//  }

  $OK = 1;

  $errors = "";

  if ($Password1 != $Password2) {
    $errors .= "The password was not confirmed.  It must be entered twice.<BR>";
    $OK = 0;
  }

  $AccountModified = 0;
  if ($OK) {


     if ($emailsitenotices_yn == "ON") {
         $emailsitenotices_yn_value = "Y";
     } else {
        $emailsitenotices_yn_value = "N";
     }

     $sql = "update users set ";
     $sql .= "email                = '$email', ";
     $sql .= "emailsitenotices_yn  = '$emailsitenotices_yn_value',";
     $sql .= "watchnotifyfrequency = '$watchnotifyfrequency'";

     if ($Password1 != '') {
       $sql .= ", password = '$Password1'";
     }

     $sql .= " where cookie = '$visitor'";

     $result = mysql_query($sql);
     if ($result) {
	if (mysql_affected_rows() == 1) {
	   $AccountModified = 1;
	}
     }

     if (!$AccountModified) {
	$errors .= 'Something went terribly wrong there.<br>';
	$errors .= 'UserID	= '.$UserID	  . '<br>';
	$errors .= 'Password	= '.$Password1	  . '<br>';
	$errors .= 'DaysToShow	= '.$DaysToShow   . '<br>';
	$errors .= 'MaxArticles = '.$MaxArticles  . '<br>';
	$errors .= 'DaysNew	= '.$DaysNew	  . '<br>';
	$errors .= $sql;
     } else {
	header("Location: ../../");  /* Redirect browser to PHP web site */
	exit;  /* Make sure that code below does not get executed when we redirect. */
     }
   }
}
</script>

<html>

<head>
<title>freshports -- Customize User Account</title>
<meta name="description" content="freshports - new ports, applications">
<meta name="keywords" content="FreeBSD, index, applications, ports">  
<!--// DVL Software is a New Zealand company specializing in database applications. //-->
</head>

<body bgcolor="#ffffff" link="#0000cc">

 <? include("/www/freshports.org/_private/header.inc") ?>

<table width="100%" border="0">
  <tr><td valign="top" width="100%">
<table width="100%" border="0">
    <td bgcolor="#AD0040"><font color="#FFFFFF" size="+2">Customize User Account</font></td>
  </tr>
  <tr>
    <td height="20"><script language="php">

if (!$submit) {
 require( "/www/freshports.org/_private/getvalues.php3");
}

if ($errors) {
echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>
<table width=100% border=0 cellpadding=1>
<tr bgcolor="#AD0040"><td><b><font color="#ffffff" size=+0>Access Code Failed!</font></b></td>
</tr>
<tr bgcolor="#ffffff">
<td>
  <table width=100% cellpadding=3 cellspacing=0 border=0>
  <tr valign=top>
   <td><img src="/warning.gif"></td>
   <td width=100%>
  <p>Some errors have occurred which must be corrected before your login can be created.</p>';

echo $errors;

echo '<p>If you need help, please post a message on the forum. </p>
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
if ($AccountModified) {
   echo "Your account details were successfully updated.";
} else {
  // provide default values for an empy form.
//  $daystoshow = 20;
//  $maxarticles = 40;
//  $daysnew = 20;
//   require( "_private/commonphp3.inc");
//	echo $DaysToShow,  '= days to show';

echo '<table cellpadding=1 cellspacing=0 border=0 bgcolor="#AD0040" width=100%>
<tr>
<td>
<table width=100% border=0 cellpadding=1>

<tr bgcolor="#AD0040"><td><font color="#ffffff">Use this form to customzie your account.</font></td>
</tr>
<tr bgcolor="#ffffff">
<td>';

echo 'If you wish to change your password, supply your new password twice.  Otherwise, leave it blank.<br>';
include("/www/freshports.org/_private/getvalues.php3");

$Customize=1;
include("/www/freshports.org/_private/new-user.inc");

echo "</td>
</tr>
</table>
</form>
</td>
</tr>
</table>";
}

</script></td>
</table>
</td>
  <td valign="top" width="*">
   <? include("/www/freshports.org/_private/side-bars.php3") ?>
 </td>
</tr>
  </tr>
</table>
</body>
<? include("/www/freshports.org/_private/footer.inc") ?>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
