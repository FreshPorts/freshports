<?php
	#
	# $Id: login.php,v 1.3 2010-09-17 14:44:38 dan Exp $
	#
	# Copyright (c) 1998-2026 Dan Langille
	#

?>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" name="l">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p><?php echo _('User ID:'); ?><br>
      <input SIZE="15" NAME="UserID" value="<?php if (IsSet($UserID)) echo htmlentities($UserID) ?>" autofocus=""></p>
      <p><?php echo _('Password:'); ?><br>
      <input TYPE="PASSWORD" NAME="Password" VALUE = "<?php if (IsSet($Password)) echo htmlentities($Password) ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="<?php echo _('Login'); ?>" name=submit>
      <br>
      <br>
      <a href="forgotten-password.php"><?php echo _('Forgotten your password?'); ?></a>
      <br><br>
</form>
