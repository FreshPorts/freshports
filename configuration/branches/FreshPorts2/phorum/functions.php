<?php
	#
	# $Id: functions.php,v 1.1.2.1 2004-01-10 15:32:34 dan Exp $
	#
	# Copyright (c) 1998-2004 DVL Software Limited
	#

function custom_SearchPathForum() {
	return 'set search_path = phorum, www, public, pg_catalog';
}

?>