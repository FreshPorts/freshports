  <table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">Login</font></td>
        </tr>
        <tr>
         <td><script language="php">

  if ($UserName) {
   echo '<font SIZE="-1">Logged in as ', $UserName, "</font><br>";
   echo '<font SIZE="-1"><a href="http://freshports.org/customize.php3">Customize</a></font><br>';
   echo '<font SIZE="-1"><a href="http://freshports.org/logout.php3?origin=' . $PHP_SELF . ' ">Logout</a></font><br>';  
   echo '<font SIZE="-1"><a href="http://freshports.org/watch-categories.php3">watch list - Categories</a></font><br>';
   echo '<font SIZE="-1"><a href="http://freshports.org/watch.php3">your watched ports</a></font><br>';
  } else {
   echo '<font SIZE="-1"><a href="http://freshports.org/login.php3?origin=' . $PHP_SELF . ' ">User Login</a></font><br>';
   echo '<font SIZE="-1"><a href="http://freshports.org/new-user.php3">Create account</a></font><br>';
  }
        </script>
   </td>
   </tr>
   </table>
<br>
<table WIDTH="152" BORDER="1" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">Ports</font></td>
        </tr>
        <tr>
    <td valign="top">
       <font SIZE="-1"><a href="http://freshports.org/">home</a></font><br>
       <font SIZE="-1"><a href="http://freshports.org/ports-new.php3">Brand new ports</a></font><br>
       <font SIZE="-1"><a href="http://freshports.org/ports.php3">Updated ports</a></font><br>
       <font SIZE="-1"><a href="http://freshports.org/categories.php3">Categories</a></font><br>
   </td>
   </tr>
   </table>
<br>
 <table WIDTH="152" BORDER="1" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">This site</font></td>
        </tr>
        <tr>
    <td valign="top">
        <font SIZE="-1"><a href="http://freshports.org/about.php3">What is freshports?</a></font><br>
        <font SIZE="-1"><a href="http://freshports.org/authors.php3">About the Authors</a></font><br>
        <font SIZE="-1"><a href="http://freshports.org/phorum/list.php?f=3">Feedback Phorum</a></font><br>
        <font SIZE="-1"><a href="http://freshports.org/inthenews.php3">in the news</a></font><br>
        <font SIZE="-1"><a href="http://freshports.org/changes.php3">changes</a></font><br>
    </td>
   </tr>
   </table>
