#!/usr/bin/perl -w
#
# $Id: process_cvs_mail.pl,v 1.2 2006-12-17 12:06:23 dan Exp $
#
# Copyright (c) 2001-2003  DVL Software
#
# Process incoming mail from cvs-all mailing list at freebsd.org
# and convert it to XML output according to the FreshPorts DTD.
#

push (@INC, '~/scripts');

use lib "$ENV{HOME}/scripts";


use strict;
use XML::Writer;
use constants;

&main;
exit;

#####
# Main Processing Routine
#####
sub main {
	# Get the message
	my ($message) = &GetMessage;

	# Get the data
	my ($Data_ref) = &GetData($message);

	# Create the XML
	&WriteXML($Data_ref);

	# Done!  Woo woo!
	exit;
}

#####
# GetMessage - Get the actual email from STDIN
#####
sub GetMessage {
	my ($message);

	while (<>) {
		$message .= $_;
	}

	return $message;
}

#####
# GetData - Generate and return the data structure
#####
sub GetData {
	my ($message) = shift;
	my (@Data);

	my $Message_Subject;
	my $Log;
	my $EncodingLosses = 'false';

	#
	# look for non-printable characters.
	# this shows you them: perl -le 'print map chr,0x20..0x7e'
	#
	if ($message =~ tr/\x0a\x09\x20-\x7E/?/c) {
		# we have messy characters in there
		$EncodingLosses = 'true';
	}

#I'd also consider going one step further and doing:
#
#        $EncodingLosses = $message =~ tr/\x0a\x09\x20-\x7E/?/c;
#
#which stores the number of replaced characters into $EncodingLosses.
#This might or might not be useful though, depending on the rest of the
#environment.
#
# Piet Delport <pjd@303.za.net> - Sat, 1 Jun 2002 01:44:13 +0200
#


	$Message_Subject	= &GetMessage_Subject($message);

	$Log					= &GetLog($message);

	if ($Log eq '') {
		$Log = $Message_Subject;
	}

	@Data =	[	'UPDATES', [ { Version => '1.3.2.1' },
				'UPDATE', [ {},
					'DATE', [ &GetDate($message)
					],
					'TIME', [ &GetTime($message)
					],
					'OS', [ {
						Id	=> &GetOS_Id,
						Branch	=> &GetOS_Branch($message) }
					],
					'LOG', [ {},
						0,
						$Log
					],
					'PEOPLE', [ {},
						&GetPeople($message)
					],
					'MESSAGE', [ {
						Id  => &GetMessage_Id($message),
						Subject => $Message_Subject,
						EncodingLosses => $EncodingLosses },
						'DATE', [ &GetMessage_Date($message)
						],
						'TIME', [ &GetMessage_Time($message)
						],
						&GetMessage_To($message)
					],
					'FILES', [ {},
						&GetFiles($message)
					]
				]
			 ]
		];

	my ($PR) = &GetPR($message);
	if ($PR) {
		push @{$Data[0]->[1][2]}, 'PR', [ { Id => $PR } ];
	}

	return @Data;
}

#####
# WriteXML - Convert the data into XML and print
#####
sub WriteXML {
	my ($data_ref) = shift;

	# Use XML::Writer to create the XML
	my ($writer) = new XML::Writer( DATA_INDENT => 4,
					DATA_MODE => 1 );

	# use the right encoding so strings like Lyngbøl will work for XML::Parser when
	# it comes time to read this stuff back in...
	# the default is: UTF-8.  We want ISO-8859-1.

	# Add the main XML tag
	$writer->xmlDecl("UTF-8");

	# Add the XML Document Type
	$writer->doctype('UPDATES','-//FreshPorts//DTD FreshPorts 2.0//EN', 'http://www.freshports.org/docs/fp-updates.dtd');

	# Convert the data into XML
	&DataToXML($writer, $data_ref); 

	# No more XML
	$writer->end;
}

#####
# DataToXML - Convert the data into XML; tends to call itself
#####
sub DataToXML {
	my ($writer) = shift;
	my ($data_ref) = shift;

	my ($count) = $#{$data_ref};
	for (my ($i) = 0; $i < $count; $i += 2) {
		my ($element_name)		= shift @{$data_ref};
		my ($element_content)		= shift @{$data_ref};

		if ($element_name eq '0') {
			$writer->characters($element_content);
		} else {
			my ($element_attributes)	= shift @{$element_content};

			$writer->startTag($element_name, %$element_attributes);
			&DataToXML($writer, $element_content);
			$writer->endTag($element_name);
		}
	}
}
####################################################################
##### Functions to actually retrieve the data from the message #####
####################################################################

sub GetPR {
        my ($message) = @_;
        my ($PR);

        my (@lines) = split("\n", $message);
        
        for (@lines) {          
                my ($line) = $_;
       
                if ($line =~ /^  PR:/) {
                        $PR = (split(" ", $line, 2))[1];
                        last;
                }
        }

        return $PR;
}

sub GetPeople {
	my ($message) = shift;
	my (@people);

	push @people, 'UPDATER', [ { Handle => &GetUpdater_Handle($message) } ];

	my ($submitter) = &GetSubmitter($message);
	if ($submitter) {
		push @people, 'SUBMITTER', [ {}, 0, $submitter ];
	}

	my ($reviewer) = &GetReviewer($message);
	if ($reviewer) {
		push @people, 'REVIEWER', [ {}, 0, $reviewer ];
	}

	my ($approver) = &GetApprover($message);
	if ($approver) {
		push @people, 'APPROVER', [ {}, 0, $approver ];
	}

	my ($obtainedfrom) = &GetObtainedFrom($message);
	if ($obtainedfrom) {
		push @people, 'OBTAINEDFROM', [ {}, 0, $obtainedfrom ];
	}

	return @people;
}

sub GetObtainedFrom {
        my ($message) = @_;
        my ($ObtainedFrom);

        my (@lines) = split("\n", $message);
        
        for (@lines) {          
                my ($line) = $_;
       
                if ($line =~ /^  Obtained from:/) {
                        $ObtainedFrom = (split(" ", $line, 3))[2];
                        last;
                }
        }

        return $ObtainedFrom;
}

sub GetApprover {
        my ($message) = @_;
        my ($Approver);

        my (@lines) = split("\n", $message);
        
        for (@lines) {          
                my ($line) = $_;
       
                if ($line =~ /^  Approved by:/) {
                        $Approver = (split(" ", $line, 3))[2];
                        last;
                }
        }

        return $Approver;
}

sub GetReviewer { 
        my ($message) = @_;
        my ($Reviewer);

        my (@lines) = split("\n", $message);

        for (@lines) {
                my ($line) = $_;
        
                if ($line =~ /^  Reviewed by:/) {
                        $Reviewer = (split(" ", $line, 3))[2];
                        last;
                }
        }

        return $Reviewer;
}

sub GetSubmitter {  
        my ($message) = @_;
        my ($Submitter);
        
        my (@lines) = split("\n", $message); 
                 
        for (@lines) {
                my ($line) = $_;
        
                if ($line =~ /^  Submitted by:/) {
                        $Submitter = (split(" ", $line, 3))[2];
                        last;
                }
        }
         
	return $Submitter;
}

sub IsDirectoryProvided($) {
#
# see whether or not we have a directory on this incoming $line.
# we assume that if the first 20 characters are all blanks,
# there is no directory.


	my ($line) = shift;

	my ($dir) = substr($line, 0, 20);
	$dir =~ s/\s*$//;                                       

	#
	# if non-zero, there's a directory.  otherwise, none.
	#
	return length($dir)
}
 
sub GetFiles {
	my ($message) = shift;
	my (@files);
	my (@lines) = split("\n", $message);

	my $EndOfFiles = '_____';

	# Modified Files
	my ($found) = 0;
	for (@lines) {
		my ($line) = $_;

#		print "file :" . $line . "\n";

		#
		# see also GetLog for use of Revision.
		#
		if ($line =~ /^  Revision .*Changes .*Path$/) { $found = 1; next; }
		next unless $found == 1;

		last if (length($line) == 0 || substr($line, 0, length($EndOfFiles)) eq $EndOfFiles);

		my ($revision, $changes1, $changes2, $path, $action) = split(" ", $line);

		if (!defined($action)) {
			$action = $FreshPorts::Constants::MODIFY;
		} else {
			if ($action eq '(dead)') {
				$action = $FreshPorts::Constants::REMOVE;
			} else {
				if ($action eq '(new)') {
					$action = $FreshPorts::Constants::ADD;
				} else {
					$action = 'unknown action';
				}
			}
		}

		push @files, 'FILE', [ { Action => $action, Revision => $revision, Changes => "$changes1 $changes2", Path => $path } ]; 
	}

	return @files;
}

sub GetOS_Id {
	my ($message) = shift;

	return 'FreeBSD';
}

sub GetOS_Branch {
	my ($message) = @_;
	my ($branch);

	my (@lines) = split("\n", $message);

	for (@lines) {
		next unless ($_ =~ /X-FreeBSD-CVS-Branch/);
		$branch = $_;
		$branch =~ s/X-FreeBSD-CVS-Branch: //;
		last;
	}

	return $branch;
}

sub GetLog {
	my ($message)  = @_;
	my ($log)      = '';
	my ($log_done) = 0;

	#
	# List of phrases marking the end of the log
	# see also GetFiles for use of Revision
	#
	my (@log_endings) = (   '  Revision',
							'To Unsubscribe' );

	my (@lines) = split("\n", $message);

	my ($log_found) = 0;
	for (@lines) {
		my ($line) = $_;

		# remove trailing spaces.
#		$line =~ s/ +$//;
#		$line .= "\n";

		if ($line =~ /  Log:/) { $log_found = 1; next; }
		next unless ($log_found == 1);

		# Check to see if we've gone too far
		for (@log_endings) {
			if ($line =~ /^$_/) { $log_done = 1; };
		}
		last if ($log_done == 1);

		# here we remove the two spaces at the start of the log which are added
		# by the email composing script
		if (length($line) >= 2) {
			$log .= substr($line,2) . "\n";
		}
	}

	# and we remove any trailing space in the log message
	$log =~ s/\s+$//;

	return $log;
}

sub GetUpdater_Handle {
        my ($message) = @_;
        my ($handle);

        my (@lines) = split("\n", $message);

        my ($newline_found) = 0;
        for (@lines) {
                if (length == 0) { $newline_found = 1; next; }
                next unless ($newline_found == 1);
                ($handle) = split(" ", $_);
                last;
        }

        return $handle;
}

sub GetDate {
        my ($message) = @_;
        my ($date, $year, $month, $day);

        my (@lines) = split("\n", $message);

        my ($newline_found) = 0; 
        for (@lines) {
                if (length == 0) { $newline_found = 1; next; }                
                next unless ($newline_found == 1);
                $date = (split /\s+/, $_, 3)[1];
		($year, $month, $day) = split(/\//, $date);
                last;
        }

	$date = {	Year	=> $year,
			Month	=> int($month),
			Day	=> int($day) };

	return $date;
}
 
sub GetTime {
        my ($message) = @_;                     
        my ($time, $hour, $minute, $second, $timezone);
                                                 
        my (@lines) = split("\n", $message);
        
        my ($newline_found) = 0;
        for (@lines) {
                if (length == 0) { $newline_found = 1; next; }
                next unless ($newline_found == 1);
                ($time, $timezone) = (split /\s+/, $_, 4)[2,3];
                ($hour, $minute, $second) = split(/:/, $time);
                last;
        }
                                        
        $time = {	Hour		=> int($hour),
                        Minute		=> int($minute),
                        Second		=> int($second),
			Timezone	=> $timezone };

	return $time;
}

sub GetMessage_Date {
        my ($message) = @_;
        my ($date, $year, $month, $day);
	my (%months) = ( 'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12 ); 

	my (@lines) = split("\n", $message);

        for (@lines) {
                my ($line) = $_;

                if ($line =~ /^Date: /) {
        	        ($day, $month, $year) = (split(/\s+/, $line))[2..4];
                        last;
                }
        }

        $date = {       Year    => $year,
                        Month   => int($months{$month}),     
                        Day     => int($day) };

        return $date;
}
                                          
sub GetMessage_Time {
        my ($message) = @_;
        my ($time, $hour, $minute, $second, $timezone);

        my (@lines) = split("\n", $message);
                                                
        for (@lines) {
                my ($line) = $_;
                                          
                if ($line =~ /^Date: /) {
                        ($time, $timezone) = (split(/\s+/, $line))[5,7];
			($hour, $minute, $second) = split(/:/, $time);
			$timezone = substr($timezone, 1, 3);
			last;
                }
        }

        $time = {	Hour		=> int($hour),
			Minute		=> int($minute),
			Second		=> int($second),
			Timezone	=> $timezone };

        return $time;
}

sub GetMessage_Id {
        my ($message) = @_;
        my ($Id);

        my (@lines) = split("\n", $message);

        for (@lines) {
                my ($line) = $_;

                if ($line =~ /^Message-Id:/) {
                        $line =~ /\<(.*?)\>/g;
                        $Id = $1;
                        last;
                } 
        }

        return $Id;
}

sub GetMessage_To {
        my ($message) = @_;
        my ($data, $to);

        my (@lines) = split("\n", $message);

        for (@lines) {
                my ($line) = $_;

                if ($line =~ /^To: /) {
                        $data = (split/: /, $line, 2)[1];
                        last;
                }
        }

	my (@data) = split(/, /, $data);

	my (@to) = ();
	for (@data) {
		push @to, 'TO';
		push @to, [{ 'Email' => $_ } ];
	}

        return @to;
}

sub GetMessage_Subject {
#
# This obtains the subject from the raw email.
# It assumes the email has this format or similar:
# Subject: cvs commit: CVSROOT modules ports/math Makefile ports/math/py-mpz
#          Makefile distinfo pkg-comment pkg-descr pkg-plist
#          ports/math/py-mpz/files setup.py
#
# 123456789
# This assumes 9 spaces there...
#

        my ($message) = @_;
        my ($Subject);

		my ($FoundSubject) = 0;

        my (@lines) = split("\n", $message);

        for (@lines) {
                my ($line) = $_;

				if ($FoundSubject) {
					if ($line =~ /^         /) {
						$Subject .= ' ' . (split/         /, $line, 2)[1];
						next;
					} else {
						last;
					}
				} else {
	                if ($line =~ /^Subject:/) {
    	                    $Subject = (split/: /, $line, 2)[1];
							$FoundSubject = 1;
                	}
				}
        }

        return $Subject;
}
