<?php check_security(); ?>
<?php
  $DefaultDisplay=$new_DefaultDisplay;
  $DefaultEmail=$new_DefaultEmail;
  $PhorumMailCode=$new_PhorumMailCode;
  $UseCookies=$new_UseCookies;
  $SortForums=$new_SortForums;
  $default_lang=$new_default_lang;
  $TimezoneOffset=$new_default_timezone_offset;
  writefile();
  QueMessage("The Global properties have been updated.");
?>