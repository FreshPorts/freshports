<?
	# $Id: login.php,v 1.1.2.3 2002-05-18 08:25:09 dan Exp $
	#
	# Copyright (c) 1998-2001 DVL Software Limited

	$origin = $_GET["origin"];
?>
<form action="<?php echo $_SERVER["PHP_SELF"] . "?origin=$origin" ?>" method="POST"  name=f>
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<?php echo $UserID ?>"></p>
      <p>Password:<br>
      <input TYPE="PASSWORD" NAME="Password" VALUE = "<?php echo $Password ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="Login" name=submit> &nbsp;&nbsp;&nbsp;&nbsp; <input TYPE="reset" VALUE="reset form">
</form>
