<form action="<?php echo $PHP_SELF. "?origin=$origin" ?>" method="POST">
      <input type="hidden" name="custom_settings" value="1"><input type="hidden" name="LOGIN" value="1">
      <p>User ID:<br>
      <input SIZE="15" NAME="UserID" value="<?php echo $UserID ?>"></p>
      <p>Password:<br>
      <input TYPE="PASSWORD" NAME="Password" VALUE = "<?php echo $Password ?>" size="20"></p>
      <p><input TYPE="submit" VALUE="Login" name=submit> &nbsp;&nbsp;&nbsp;&nbsp; <input TYPE="reset" VALUE="reset form">
</form>
