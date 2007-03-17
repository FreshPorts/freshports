This directory contains the code to convert a FreeBSD
cvs-all message into xml which can be processed by
FreshPorts/FreshSource.

This directory contains:

README.txt          - the file you are reading
constants.pm        - perl module needed by process_cvs_mail.pl
email.txt           - raw email to be used as sample input
email.xml           - xml output by process_cvs_mail.pl
process_cvs_mail.pl - the perl code used to convert email to xml

This will convert a FreshPorts cvs all email message to xml:

    perl process_cvs_mail.pl < email.txt > email.xml

