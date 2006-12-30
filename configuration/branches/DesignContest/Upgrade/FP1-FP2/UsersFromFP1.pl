#!/usr/bin/perl -w
#
# $Id: UsersFromFP1.pl,v 1.2 2006-12-17 11:39:36 dan Exp $
#
# Copyright (c) 2003 DVL Software
#

use strict;
use DBI;

my @row;

my $dbh = DBI->connect('dbi:mysql:freshports_20020329', 'root', 'xyzzy');
if ($dbh) {
	my $sql = "select id, username, password, cookie, firstlogin,
			lastlogin, email, watchnotifyfrequency, emailsitenotices_yn,
			emailbouncecount, type from users ORDER by id";

	my $sth = $dbh->prepare($sql);

	$sth->execute ||
		die "Could not execute SQL statement ... maybe invalid?";

	while (@row = $sth->fetchrow_array) {
		print $row[0] . "\t" . $row[1] . "\t" . $row[2] . "\t" . $row[3] . 
                        "\t" . $row[4] . "\t" . $row[5] . "\t" . $row[6] . 
						"\t" . $row[7] . "\t" . $row[8] . "\t" . $row[9] . 
						"\t" . $row[10] . "\n";
	}
	$sth->finish();
	$dbh->disconnect();
}