<?php
	#
	# The URL parser is now www/--/url-parser.php.
	#
	# This file remains only for nginx configurations which still send
	# requests here (location / in virtualhost-common.conf). Once every
	# site uses /--/url-parser.php, remove this file.
	#

	require(__DIR__ . '/url-parser.php');
