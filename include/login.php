<?php
	#
	# $Id: login.php,v 1.3 2010-09-17 14:44:38 dan Exp $
	#
	# Copyright (c) 1998-2003 DVL Software Limited
	#

?>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" name="l">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<?php if (IsSet($UserID)) echo htmlentities($UserID) ?>" autofocus=""></p>
      <p>Password:<br>
      <input TYPE="PASSWORD" NAME="Password" VALUE = "<?php if (IsSet($Password)) echo htmlentities($Password) ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="Login" name=submit>
      <br>
      <br>
      <a href="forgotten-password.php">Forgotten your password?</a>
      <br><br>
</form>
