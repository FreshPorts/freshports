#!/usr/bin/perl

#
# Port Updater for FreshPorts
# takes output of LogMunger and updates the database
# written by Dan Langille
# copyright 2000 DVL Software
#

use DBI;

#
# These are the files which require a port be refreshed from the raw Makefile
#
$FilesWhichPromptRefresh = "Makefile|pkg/DESCR|pkg/COMMENT";

#sub GetPortCategory($category, $dbh) {
sub GetPortCategory($;$) {
   my $category = shift;
   my $dbh = shift;

   $sql = "select id from categories where lower(name) = lower('" . $category . "')";

   $sth = $dbh->prepare($sql);

   $sth->execute ||
        die "Could not execute SQL statement ... maybe invalid?";


   @row=$sth->fetchrow_array;

   print "\nGetPortCategory = $sql which gives ", @row[0], "\n";


   return @row[0];
}

#PortUpdate ($committer, $timestamp, $action, $description, $category, $port, $entry, $dbh) {
sub PortUpdate($;$;$;$;$;$;$;$) {
   my $committer   = shift;
   my $timestamp   = shift;
   my $action      = shift;
   my $description = shift;
   my $category    = shift;
   my $port        = shift;
   my $entry       = shift;
   my $dbh         = shift;

   my $sql = "";
   my $refresh_needed = "N";

   $categoryid = GetPortCategory($category, $dbh);

   # update the port, creating it if necessary

   $sql = "select id from ports where lower(name) = lower('" . $port . "') and primary_category_id = $categoryid";
   print $sql, "\n";
   $sth = $dbh->prepare($sql);
   
   $sth->execute ||
      die "Could not execute SQL statement ... maybe invalid?";

   @row=$sth->fetchrow_array;

   if (@row) {
      print "something found\n";
   } else {
      print "nothing found\n";
   }

   #
   # depending on what has changed, we need to take action accordingly
   #

   if ($entry =~ /$FilesWhichPromptRefresh/) {
      $refresh_needed = "Y";
   }

   print @row[0];

   if (!@row) {
      # no such port.  create it.
      $sql = "insert into ports (name, last_update, primary_category_id, " .
             "last_update_description, committer, date_added, needs_refresh, " .
             "status, package_exists, short_description) values (";
      # we assume above that the package does not exist until we are told otherwise.

      # we don't get a version when inserting, so we must fake it by supplying a name.
      $sql .= "'$port', '$timestamp', $categoryid, '$description', " . 
              "'$committer', current_timestamp, 'Y', 'N', 'N', '-- waiting for description --')";

      print "$sql\n";

      $sth = $dbh->prepare($sql);

      $sth->execute ||
         die "Could not execute SQL statement ... maybe invalid?";

      $sql = "insert into newports (name, primary_category_id) values ('$port', $categoryid)";

      $sth = $dbh->prepare($sql);

      $sth->execute ||
         die "Could not execute SQL statement ... maybe invalid?";

   } else {
      # update the time on the port
      $sql = "update ports set last_update = '$timestamp', committer = '$committer', " .
             "last_update_description = '$description' ";

      if ($refresh_needed eq "Y") {
         $sql .= ", needs_refresh = 'Y'";
      }

      $sql .= " where id = " . @row[0];

      print "$sql\n";

      $sth = $dbh->prepare($sql);

      $sth->execute ||
         die "Could not execute SQL statement ... maybe invalid?";
   }
}


$dbh = DBI->connect('dbi:mysql:freshports','updater','xyzzy');

$inputfile = 'data.txt';
$inputfile = 'sample.txt';
#$inputfile = 'abacus.txt';
$inputfile =  '/www/freshports.org/work/msgs-awk/20000421-13:30:45-NZST.39927.munged';
$ignoredirs = "Attic|distfiles|Mk|Tools|Templates";

#open (STDIN, $inputfile) || die "error opening";
@file=<STDIN>;
close(STDIN);
chomp(@file);

for($i=0; $i<=$#file; $i++) {
   $id = $i + 1;

   $line = $file[$i];

   ($committer, $timestamp, $action, $filename, $description, $extra)=split/\|/,$line;

#  these bits might have quotes.
   $committer   =~ s/\'/\\'/g;
   $description =~ s/\'/\\'/g;

   print "committer=", $committer, "\ntimestamp=", $timestamp, "\naction='",  $action, "'\nfilename=", $filename, "\ndescription=", $description, "\n";

   ($category, $port, $entry, $extra2) = split/\//,$filename;

  print "category=$category\nport=$port\nentry=$entry\n";
#  exit;

   #
   # this is where we can pick up on special things
   #

   if ($category eq "." and $port eq "INDEX") {
      #
      # ahh, the index has changed, do we want to know?
      #
      print "ahh, the index has changed, do we want to know?";
   } else {
      if ($entry || (!$entry && $action eq 'import')) {

         # we ignore certain categories and always ignore /usr/ports/<category>/Makefile.
         if (($category !~ /$ignoredirs/) && ($port ne 'Makefile')) {
            #print '  ***';
            # we have a file in this port which is actually being updated.  Let's update the port.
     
            PortUpdate ($committer, $timestamp, $action, $description, $category, $port, $entry, $dbh)
         } else {
            print "ignoring $category/$port\n";
         }
      } else {
        print 'not processing this update as it does not meet the criteria';
      }
   }
 
  print "\n\n ===================================\n\n";

#exit;
}

`touch /usr/local/etc/freshports/msgs/lastupdate`;
