#!/usr/bin/perl -w
#
# $Id: WatchListPortsFromFP1.pl,v 1.2 2006-12-17 11:39:36 dan Exp $
#
# Copyright (c) 2002-2003 DVL Software
#

use strict;
use DBI;

my @row;

my $dbh = DBI->connect('dbi:mysql:freshports_20020329', 'root', 'xyzzy');
if ($dbh) {
	my $sql = "	select watch.id, categories.name, ports.name 
				  from users, watch_port, watch, categories, ports
				 where users.id                  = watch.owner_user_id
				   and watch.id                  = watch_port.watch_id
				   and watch_port.port_id        = ports.id
				   and ports.primary_category_id = categories.id
				   and ports.status              = 'A'
			  order by 1, 2, 3";

	my $sth = $dbh->prepare($sql);

	$sth->execute ||
		die "Could not execute SQL statement ... maybe invalid?";

	while (@row = $sth->fetchrow_array) {
		print $row[0] . "\t" . $row[1] . "\t" . $row[2] . "\n";
	}
	$sth->finish();
	$dbh->disconnect();
}
