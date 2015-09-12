#!/usr/bin/perl -w
#
# $Id: LoadWatchListFromFP1IntoFP2.pl,v 1.2 2006-12-17 11:39:36 dan Exp $
#
# Copyright (c) 2001-2003 DVL Software
#

use strict;
use lib "$ENV{HOME}/scripts";

#use port;
use database;
#use utilities;
use DBI;

#require config;

my $sth;
my $sql;
my $IndexLine;
my $IndexFile = 0;

my %Ports;

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
#		print $IndexLine . "\n"; 

		my ($id, $name, $user_id) = split(/\t/, $IndexLine);

		print "$id, $name, $user_id\n";

		$sql = "insert into watch_list (id, user_id, name) values ($id, $user_id, '$name')";
		
		$sth = $dbh->prepare($sql);
		$sth->execute ||
            FreshPorts::Utilities::ReportError('warning', "Could not execute SQL $sql ... maybe invalid? " . $dbh->errstr, 1);
	

	}
#	$sth->finish();
	$dbh->disconnect();
} else {
	print "Cannot connect to Postgres server: $DBI::errstr\n";
	print " db connection failed\n";
}
