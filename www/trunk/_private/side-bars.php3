  <table WIDTH="152" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">
        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">Login</font></td>
        </tr>
        <tr>
         <td><script language="php">

  if ($UserName) {
   echo '<font SIZE="-1">Logged in as ', $UserName, "</font><br>";
   echo '<font SIZE="-1"><a href="customize.php3">Customize</a></font><br>';
   echo '<font SIZE="-1"><a href="logout.php3">Logout</a></font><br>';  
   echo '<font SIZE="-1"><a href="category-watch.php3">watch list - Categories</a></font><br>';
   echo '<font SIZE="-1"><a href="port-watch.php3">watch list - Ports</a></font>';
  } else {
   echo '<font SIZE="-1"><a href="login.php3">User Login</a></font><br>';
   echo '<font SIZE="-1"><a href="new-user.php3">Create account</a></font><br>';
  }
        </script>
   </td>
   </tr>
   </table>
<br>
 <table WIDTH="152" BORDER="1" BORDER="1" CELLSPACING="0" CELLPADDING="5"
            bordercolor="#a2a2a2" bordercolordark="#a2a2a2" bordercolorlight="#a2a2a2">        <tr>
         <td bgcolor="#AD0040" height="30"><font color="#FFFFFF" SIZE="+1">This site</font></td>
        </tr>
        <tr>
    <td valign="top"><font SIZE="-1"><a href="about.html">What is freshports?</a></font><br>
                <font SIZE="-1"><a href="authors.html">About the Authors</a></font>
   </td>
   </tr>
   </table>
