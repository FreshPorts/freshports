<?php
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
if(isset($HTTP_SERVER_VARS)){
  while(list($var, $val)=each($HTTP_SERVER_VARS)){
    $$var=$val;
  }
}
?>