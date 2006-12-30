#!/usr/bin/perl -w
#
# $Id: LoadWatchListPortsFromFP1IntoFP2.pl,v 1.2 2006-12-17 11:39:36 dan Exp $
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
my @row;

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

	$sql = "select name, category, element_id from ports_active order by category, name";
	$sth = $dbh->prepare($sql);
	$sth->execute ||
		FreshPorts::Utilities::ReportError('warning', "Could not execute SQL $sql ... maybe invalid? " . $dbh->errstr, 1);

	print "reading all ports...\n";
	while (@row=$sth->fetchrow_array) {
		$Ports{"$row[1]/$row[0]"} = $row[2];
	}



	

	print "reading from STDIN...\n";
	while (defined(my $IndexLine = <STDIN> ) ) {
		# remove the trailing CR/LF
		$IndexLine =~ s/\n//g;
#		print $IndexLine . "\n"; 

		my ($watch_id, $category, $port) = split(/\t/, $IndexLine);

		print "$watch_id, $category, $port\n";

		if (defined($Ports{"$category/$port"})) {

			$sql = "insert into watch_list_element (watch_list_id, element_id) 
						values ($watch_id, " . $Ports{"$category/$port"} . ")";

			$sth = $dbh->prepare($sql);
			$sth->execute ||
    	        FreshPorts::Utilities::ReportError('warning', "Could not execute SQL $sql ... maybe invalid? " . $dbh->errstr, 1);
		} else {
			print "$category, $port not found!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";
		}
	}
#	$sth->finish();
	$dbh->disconnect();
} else {
	print "Cannot connect to Postgres server: $DBI::errstr\n";
	print " db connection failed\n";
}
