<?
   # $Id: logout.php3,v 1.4 2001-09-28 00:05:38 dan Exp $
   #
   # Copyright (c) 1998-2001 DVL Software Limited

   require("./include/common.php");
   require("./include/freshports.php");
   require("./include/databaselogin.php");


   freshports_Start("title",
               "freshports - new ports, applications",
               "FreeBSD, index, applications, ports");

?>
<?
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
