#!/usr/bin/perl -w

# log catcher
# written by Olaf
# takes the data from cvs-all and creates a file
# copyright 2000 DVL Software

$Outfile  = "/usr/local/etc/freshports/msgs/" . time . ".$$.txt";

$Nextfile = "/usr/local/etc/freshports/msgs/" . time . ".$$.txt.munged";

while ( defined(my $l = <STDIN> ) ) {
     last if $l =~ /^$/;
     $Is_reply = 1 if ( $l =~ /^in-reply-to:/i );
     $Is_commit = 1 if ( $l =~  m!^subject: cvs commit: ports/!i );
}

if ( (not $Is_reply ) and $Is_commit ) {
    # Improvement?: Does sendmail listen to the return code of MDAs??
    open SESAME, ">$Outfile"
	 or die "Can't open $Outfile for writing, commit lost, stopped, error = $! ";
    # Copy the body.
    while ( defined(my $l = <STDIN> ) ) {
	 print SESAME $l;
    }
    close SESAME;

    $Command = "/usr/bin/awk -f /usr/local/etc/freshports/log-munger.awk < $Outfile > $Nextfile";
#    print $Command;
    `$Command`;

    $Command = "/bin/cat $Nextfile | /usr/bin/perl /usr/local/etc/freshports/updates/updates.pl > $Nextfile.out";
    `$Command`;
} else {
     while ( defined(<STDIN>) ) {
	  ;
    }
}

exit 0;



