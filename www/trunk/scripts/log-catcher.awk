BEGIN {
 OUTDIR="/usr/local/etc/freshports/msgs-awk/" ;
 MUNGER="/usr/bin/awk -f usr/local/etc/freshports/log-munger.awk";
 UPDATER="updater.pl ";
 getline pid<"/dev/pid"
 file=OUTDIR strftime("%Y%m%d-%T-%Z.")  pid;
 inheader=1;wasport=0;
 }

{
if(inheader==0) {
 if($1=="To" && $2=="Unsubscribe:" && $NF=="majordomo@FreeBSD.org") exit;
 print $0>file;
 next;
 }
if($1=="In-Reply-To:") exit;
if($1=="Subject:" && ($2!="cvs" || $3!="commit:" || substr($4,1,6)!="ports/")) exit;
if(NF==0) {
 inheader=0;getline;
 if(NF!=4 || length($4)!=3 || length($2)!=10 || length($3)!=8) exit;
 print $0>file;wasport=1;
 }

}

END {
if(wasport) {
 cmd=MUNGER " < " file " > " file ".munged";
 /* cmd2=MUNGER " <" file "|" UPDATER; */
 system(cmd);
 /*system(cmd2);*/
 print cmd;
/* print cmd2; */
}
}

