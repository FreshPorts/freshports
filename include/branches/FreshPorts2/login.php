<?
	# $Id: login.php,v 1.1.2.1 2002-01-06 07:29:28 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited
?>
<form action="<?php echo $PHP_SELF. "?origin=$origin" ?>" method="POST"  name=f>
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<?php echo $UserID ?>"></p>
      <p>Password:<br>
      <input TYPE="PASSWORD" NAME="Password" VALUE = "<?php echo $Password ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="Login" name=submit> &nbsp;&nbsp;&nbsp;&nbsp; <input TYPE="reset" VALUE="reset form">
</form>
