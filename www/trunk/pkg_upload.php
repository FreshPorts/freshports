<HTML>
	<BODY>
		<?
		// make sure the POST vars are ok. 
		// check for funny stuff
		if ($dbg) {
			define("DEBUG",1);
		}
		if($file && trim($pkg_info) != '') {
			require_once "pkg_utils.inc";
			$retid = -1;
			if (IsLoginValid($user,$pw,$ret_id)) {
				$filename = "/tmp/tmp_pkg_output.$user";
				if (! copy($pkg_info,$filename)) {
					?> <pre> Error writing file on server </pre> <?
					exit();
				}
				require_once "pkg_process.inc";
				$result = ProcessPackages($filename,$ret_id);

				if ($result && DEBUG) {
					?> <pre> <?= "$user " ?> Your Ports Are: <? 
						print_r($result['FOUND']);
						echo("<PRE>We were unable to be 100% certain about the following ports.");
			  			echo("It is most likely that you will want them, but you may with to review.</PRE>");
						print_r($result['GUESS']);
					?> </pre> <?  
				}
			} else { ?>
				<pre>
					Invalid Username and/or Password
		 		</pre> 
		<?	}
		} else {
		?>
		<FORM action='pkg_upload.php?file=1' method='post' enctype='multipart/form-data'>
			<TABLE>
				<TR><TD>Enter Your Username</TD></TR>
				<TR><TD><INPUT type="text" name="user" value"" size=20></TD></TR>
				<!-- <TR><TD>&nbsp;</TD></TR> -->
				<TR><TD>Upload Your File</TD></TR>
				<TR><TD><INPUT type="file" name="pkg_info" size=20></TD></TR>
				<TR><TD><INPUT type="submit" name="upload" value="Upload" size=20></TD></TR>
			</TABLE>
		</FORM>
		<? } ?>
	</BODY>
</HTML>
