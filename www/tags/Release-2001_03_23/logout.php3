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
