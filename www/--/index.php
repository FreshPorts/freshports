<?php
	#
	# The URL parser is www/--/new-url-parsing.php.
	#
	# This file remains only for nginx configurations which still send
	# requests here, such as the /css/ and image fallbacks in
	# virtualhost-common.conf. Once nothing refers to /--/index.php,
	# remove this file.
	#

	require(__DIR__ . '/new-url-parsing.php');
