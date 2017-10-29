#
# $Id: README.txt,v 1.3 2007-10-16 18:29:39 dan Exp $
#
# Copyright (c) 1998-2006 DVL Software Limited
#

Much of what this documents is now handled by the freshports-www port.

This file documents the directory structure that needs to be present
for a FreshPorts website and scripts.

You need to do this:

+-----------------------+
|file system permissions|
+-----------------------+

touch     /var/db/freshports/cache/searchlog.txt
chgrp www /var/db/freshports/cache/searchlog.txt
chmod g+w /var/db/freshports/cache/searchlog.txt

The following settings are necessary for a phorum:

chgrp www phorum-3.3.2a
chmod g+w phorum-3.3.2a


+---------------------+
|Website configuration|
+---------------------+

mkdir -p /var/db/freshports/cache/html
mkdir -p /var/db/freshports/cache/news
mkdir -p /var/db/freshports/cache/ports
mkdir -p /var/db/freshports/cache/spooling
mkdir -p /var/db/freshports/tmp

chown -R www:www /var/db/freshports/cache
chmod -R g+w     /var/db/freshports/cache

mkdir scripts-log

cd www/include
cp common.php.sample common.php

cd configuration
cp database.php.sample database.php

modify the login:

$database=pg_connect("dbname=FreshPorts2 user=dan")


+-------+
|CVSROOT|
+-------+

CVSROOT=/usr/repositories/freshports2


+-------------------------------------+
|Various ports which must be installed|
+-------------------------------------+

*** Don't forget to maintain the FreshPorts install meta port ***

just some random notes on configuration:

File-PathConvert
http://search.cpan.org/search?dist=File-PathConvert
http://www.cpan.org/authors/id/R/RB/RBS/File-PathConvert-0.85.tar.gz

