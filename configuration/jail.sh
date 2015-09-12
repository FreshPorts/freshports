
mkdir -p /usr/FreshPorts/ports-jail/usr/ports    \
         /usr/FreshPorts/ports-jail/usr/share/mk \
         /usr/FreshPorts/ports-jail/usr/sbin     \
         /usr/FreshPorts/ports-jail/usr/bin      \
         /usr/FreshPorts/ports-jail/libexec      \
         /usr/FreshPorts/ports-jail/usr/lib      \
         /usr/FreshPorts/ports-jail/sbin         \
         /usr/FreshPorts/ports-jail/lib          \
         /usr/FreshPorts/ports-jail/bin          \
         /usr/FreshPorts/ports-jail/dev

# for FreshPorts
/home/dan/PORTS-SVN             /usr/FreshPorts/ports-jail/usr/ports        nullfs  ro,nosuid,noexec        0       0
/usr/share/mk                   /usr/FreshPorts/ports-jail/usr/share/mk     nullfs  ro,nosuid,noexec        0       0
/usr/sbin                       /usr/FreshPorts/ports-jail/usr/sbin         nullfs  ro,nosuid               0       0
/usr/bin                        /usr/FreshPorts/ports-jail/usr/bin          nullfs  ro,nosuid               0       0
/libexec                        /usr/FreshPorts/ports-jail/libexec          nullfs  ro,nosuid               0       0
/usr/lib                        /usr/FreshPorts/ports-jail/usr/lib          nullfs  ro,nosuid               0       0
/sbin                           /usr/FreshPorts/ports-jail/sbin             nullfs  ro,nosuid               0       0
/lib                            /usr/FreshPorts/ports-jail/lib              nullfs  ro,nosuid               0       0
/bin                            /usr/FreshPorts/ports-jail/bin              nullfs  ro,nosuid               0       0
none                            /usr/FreshPorts/ports-jail/dev              devfs   rw                      0       0


at  /usr/local/FreshPorts/ports-jail/ we place this file: make.sh

#!/bin/sh
PORTDIR=$1
cd ${PORTDIR}
/usr/bin/make -V PORTNAME -V PKGNAME -V IGNORE  -V -f /usr/ports/${PORTDIR}/Makefile  LOCALBASE=/nonexistentlocal


Then we can issue this command:

$ sudo /usr/sbin/chroot -u dan /usr/local/FreshPorts/ports-jail /make.sh /usr/ports/sysutils/msktutil