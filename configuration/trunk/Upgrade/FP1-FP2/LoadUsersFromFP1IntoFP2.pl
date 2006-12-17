#!/usr/bin/perl -w
#
# $Id: LoadUsersFromFP1IntoFP2.pl,v 1.2 2006-12-17 11:39:36 dan Exp $
#
# Copyright (c) 2001-2003 DVL Software
#

use strict;
use lib "$ENV{HOME}/scripts";

#use port;
use database;
use utilities;
use DBI;

#require config;

my %WatchNotice;

$WatchNotice{'Z'} = 1;
$WatchNotice{'D'} = 2;
$WatchNotice{'W'} = 3;
$WatchNotice{'F'} = 4;
$WatchNotice{'M'} = 5;

my $sth;
my $sql;
my $IndexLine;
my $IndexFile = 0;

for (my $i = 1; $i < ($#ARGV+1); $i++) {
	print "checking arg $i\n";
	if ($ARGV[$i] eq '-I') {
		print "debugging....\n";
		$IndexFile = 1;
	}
}

my $dbh = my $dbh_pg = DBI->connect('DBI:Pg:dbname=user_convert');

if ($dbh) {
	print "connected\n";

	print "reading from STDIN...\n";
	while (defined(my $IndexLine = <STDIN> ) ) {
		# remove the trailing CR/LF
		$IndexLine =~ s/\n//g;

		my ($id, $username, $password, $cookie, $firstlogin, $lastlogin,
			$email, $watchnotifyfrequency, $emailsitenotices_yn, 
			$emailbouncecount, $type) = split(/\t/, $IndexLine);

		print "$id, $username, $password, $cookie, $firstlogin, $lastlogin, " .
			"$email, $watchnotifyfrequency, $emailsitenotices_yn, " .
			"$emailbouncecount, $type\n";

		$sql = "insert into users (id, name, password, cookie, firstlogin, lastlogin, email, watch_notice_id,
				emailsitenotices_yn, emailbouncecount, type, status, ip_address) values
				($id, '$username', '$password', '$cookie', '$firstlogin', '$lastlogin', '$email', 
				$WatchNotice{$watchnotifyfrequency}, '$emailsitenotices_yn', $emailbouncecount, '$type', 'A', '0.0.0.0')";
		
		$sth = $dbh->prepare($sql);
		$sth->execute ||
            FreshPorts::Utilities::ReportError('warning', "Could not execute SQL $sql ... maybe invalid? " . $dbh->errstr, 1);
#		}

	}
	$sth->finish();
	$dbh->rollback();
	$dbh->disconnect();
} else {
	print "Cannot connect to Postgres server: $DBI::errstr\n";
	print " db connection failed\n";
}
