From http://news.freshports.org/2012/11/08/getting-more-accurate-results/

mkdir -p /usr/local/FreshPorts/ports-jail/usr/ports    \
         /usr/local/FreshPorts/ports-jail/usr/share/mk \
         /usr/local/FreshPorts/ports-jail/usr/sbin     \
         /usr/local/FreshPorts/ports-jail/usr/bin      \
         /usr/local/FreshPorts/ports-jail/libexec      \
         /usr/local/FreshPorts/ports-jail/usr/lib      \
         /usr/local/FreshPorts/ports-jail/sbin         \
         /usr/local/FreshPorts/ports-jail/lib          \
         /usr/local/FreshPorts/ports-jail/bin          \
         /usr/local/FreshPorts/ports-jail/dev

# for FreshPorts
/home/dan/PORTS-SVN             /usr/local/FreshPorts/ports-jail/usr/ports        nullfs  ro,nosuid,noexec        0       0
/usr/share/mk                   /usr/local/FreshPorts/ports-jail/usr/share/mk     nullfs  ro,nosuid,noexec        0       0
/usr/sbin                       /usr/local/FreshPorts/ports-jail/usr/sbin         nullfs  ro,nosuid               0       0
/usr/bin                        /usr/local/FreshPorts/ports-jail/usr/bin          nullfs  ro,nosuid               0       0
/libexec                        /usr/local/FreshPorts/ports-jail/libexec          nullfs  ro,nosuid               0       0
/usr/lib                        /usr/local/FreshPorts/ports-jail/usr/lib          nullfs  ro,nosuid               0       0
/sbin                           /usr/local/FreshPorts/ports-jail/sbin             nullfs  ro,nosuid               0       0
/lib                            /usr/local/FreshPorts/ports-jail/lib              nullfs  ro,nosuid               0       0
/bin                            /usr/local/FreshPorts/ports-jail/bin              nullfs  ro,nosuid               0       0
none                            /usr/local/FreshPorts/ports-jail/dev              devfs   rw                      0       0


at  /usr/local/FreshPorts/ports-jail/ we place this file: make.sh

#!/bin/sh
PORTDIR=$1
cd ${PORTDIR}
/usr/bin/make -V PORTNAME -V PKGNAME -V IGNORE  -V -f /usr/ports/${PORTDIR}/Makefile  LOCALBASE=/nonexistentlocal


Then we can issue this command:

$ sudo /usr/sbin/chroot -u dan /usr/local/FreshPorts/ports-jail /make.sh sysutils/msktutil

dan      ALL=(ALL) NOPASSWD:/usr/sbin/chroot -u dan /usr/FreshPorts/ports-jail /make-port.sh *
dan      ALL=(ALL) NOPASSWD:/usr/sbin/chroot -u dan /usr/FreshPorts/ports-jail /make-category-comment.sh *
dan      ALL=(ALL) NOPASSWD:/usr/sbin/chroot -u dan /usr/FreshPorts/ports-jail /make-master-port-test.sh *
dan      ALL=(ALL) NOPASSWD:/usr/sbin/chroot -u dan /usr/FreshPorts/ports-jail /make-master-sites-all.sh *
dan      ALL=(ALL) NOPASSWD:/usr/sbin/chroot -u dan /usr/FreshPorts/ports-jail /make-showconfig.sh *

