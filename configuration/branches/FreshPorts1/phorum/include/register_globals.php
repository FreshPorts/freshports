<?php

// we use $PHP_SELF as the test since it would always be there.
$PHP_SELF=$HTTP_SERVER_VARS["PHP_SELF"];
$HTTP_HOST=$HTTP_SERVER_VARS["HTTP_HOST"];
$HTTP_USER_AGENT=$HTTP_SERVER_VARS["HTTP_USER_AGENT"];
$QUERY_STRING=$HTTP_SERVER_VARS["QUERY_STRING"];
$REMOTE_ADDR=$HTTP_SERVER_VARS["REMOTE_ADDR"];
if(isset($HTTP_GET_VARS)){
  while(list($var, $val)=each($HTTP_GET_VARS)){
    $$var=$val;
  }
}
if(isset($HTTP_POST_VARS)){
  while(list($var, $val)=each($HTTP_POST_VARS)){
    $$var=$val;
  }
}
if(isset($HTTP_COOKIE_VARS)){
  while(list($var, $val)=each($HTTP_COOKIE_VARS)){
    $$var=$val;
  }
}
?>
