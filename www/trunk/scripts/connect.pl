#!/usr/bin/perl

use DBI;

$dbh = DBI->connect('dbi:mysql:freshports','freshports','marlboro');

if ($dbh) {

$sql = "select name from categories";

$sth = $dbh->prepare($sql);

$sth->execute || 
     die "Could not execute SQL statement ... maybe invalid?";

#output database results
while (@row=$sth->fetchrow_array)
   { print "@row\n" }

} else {
   print "connect failed\n";
}
