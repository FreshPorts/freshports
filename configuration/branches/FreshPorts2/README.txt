#
# $Id: README.txt,v 1.1.2.5 2003-05-16 01:09:06 dan Exp $
#
# Copyright (c) 1998-2003 DVL Software Limited
#

You need to do this:

+-----------------------+
|file system permissions|
+-----------------------+

touch searchlog.txt
chgrp www searchlog.txt
chmod g+w searchlog.txt

The following settings are necessary for a phorum:

chgrp www phorum-3.3.2a
chmod g+w phorum-3.3.2a


+---------------------+
|Website configuration|
+---------------------+

 - cd www/include
 - cp common.php.sample common.php

 - cd configuration
 - cp database.php.sample database.php

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

