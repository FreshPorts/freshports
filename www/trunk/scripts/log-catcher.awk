{

if($1=="Log:") {
loga="";
j=0;
do {
 getline;
 if(!j) gsub("^ +","",$0);j=1;
 loga=loga $0 " ";
 } while(NF>0);
for(i=0;i<nmodified;i++)
 printf("%s|%s|modify|%s|%s\n",commiter,cdate,modfiles[i],loga);
for(i=0;i<nremoved;i++)
 printf("%s|%s|remove|%s|%s\n",commiter,cdate,rmfiles[i],loga);
for(i=0;i<nadded;i++)
 printf("%s|%s|add|%s|%s\n",commiter,cdate,addfiles[i],loga);
nadded=0;nmodified=0;nremoved=0;

next;
}

if($1=="Log" && $2=="Message:" && imported) {
loga="";
j=0;
do {
 getline;
 if(!j) gsub("^ +","",$0);j=1;
 loga=loga $0 " ";
 } while(NF>0);
printf("%s|%s|import|%s|%s\n",commiter,cdate,importedsrc,loga);
imported=0;
}


idx=index($0,":");
if(action==1) {
 if(!idx) {
  for(i=2;i<=NF;i++) modfiles[nmodified++]=$1 "/" $i;
  next;
  }
}

if(action==2) {
 if(!idx) {
  for(i=2;i<=NF;i++) rmfiles[nremoved++]=$1 "/" $i;
  next;
  }
}

if(action==3) {
 if(!idx) {
  for(i=2;i<=NF;i++) addfiles[nadded++]=$1 "/" $i;
  next;
  }
}

if(NF==4 && length($4)==3 && length($2)==10 && length($3)==8) {
 if(imported)
 printf("%s|%s|import|%s|%s\n",commiter,cdate,importedsrc,"");
 commiter=$1;cdate=$2 " " $3 " " $4;
 nremoved=0;nmodified=0;nadded=0;imported=0;
} else
if($1=="Modified" && $2=="files:" && NF==2) {
action=1;next;
 } else
if($1=="Removed" && $2=="files:" && NF==2) {
 action=2;next;
 } else
if($1=="Added" && $2=="files:" && NF==2) {
 action=3;next;
 } else
if($3=="Imported" && $4=="sources" && NF==4 ) {
 importedsrc=$1;
 gsub("^ports/","",importedsrc);$action=4;
 imported=1;
 /*printf("%s|%s|import|%s|\n",commiter,cdate,$1);*/
 next;
 } else action =0;


}

END {
 if(imported)
 printf("%s|%s|import|%s|%s\n",commiter,cdate,importedsrc,"");
}


