#
# $Id: README.txt,v 1.3 2007-10-16 18:29:39 dan Exp $
#
# Copyright (c) 1998-2006 DVL Software Limited
#

This file documents the directory structure that needs to be present
for a FreshPorts website and scripts.

You need to do this:

+-----------------------+
|file system permissions|
+-----------------------+

touch dynamic/searchlog.txt
chgrp www dynamic/searchlog.txt
chmod g+w dynamic/searchlog.txt

The following settings are necessary for a phorum:

chgrp www phorum-3.3.2a
chmod g+w phorum-3.3.2a


+---------------------+
|Website configuration|
+---------------------+

mkdir -p dynamic/caching/cache
mkdir -p dynamic/caching/categories
mkdir -p dynamic/caching/news
mkdir -p dynamic/caching/ports
mkdir -p dynamic/caching/spool
mkdir -p dynamic/caching/tmp

chown -R www:www dynamic
chmod -R g+w     dynamic

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

