<?
   # $Id: logout.php3,v 1.5 2001-10-20 21:50:39 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");


   SetCookie("visitor", '', 0, '/');  // clear the cookie

   if ($origin == "/index.php3") {                   
      $origin = "/";                                 
   }
   header("Location: $origin");  /* Redirect browser to PHP web site */
   exit;  /* Make sure that code below does not get executed when we redirect. */


?>

<html>

<head>
<title></title>
</head

<body>
</body>
</html>
