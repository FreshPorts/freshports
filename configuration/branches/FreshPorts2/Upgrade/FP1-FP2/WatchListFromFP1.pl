#!/usr/bin/perl -w
#
# $Id: WatchListFromFP1.pl,v 1.1.2.2 2003-05-16 01:09:10 dan Exp $
#
# Copyright (c) 2002-2003 DVL Software
#

use strict;
use DBI;

my @row;

my $dbh = DBI->connect('dbi:mysql:freshports_20020329', 'root', 'xyzzy');
if ($dbh) {
	my $sql = "	select id, name, owner_user_id from watch";

	my $sth = $dbh->prepare($sql);

	$sth->execute ||
		die "Could not execute SQL statement ... maybe invalid?";

	while (@row = $sth->fetchrow_array) {
		print $row[0] . "\t" . $row[1] . "\t" . $row[2] . "\n";
	}
	$sth->finish();
	$dbh->disconnect();
}
