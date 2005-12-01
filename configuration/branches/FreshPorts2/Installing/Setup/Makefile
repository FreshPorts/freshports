#
# $Id: Makefile,v 1.1.2.9 2005-12-01 04:45:59 dan Exp $
#
# Install the ports needed by FreshPorts
#
# Copyright (c) 2002-2005 DVL Software Limited
#

PORTNAME=		FreshPorts-ports
PORTVERSION=	1.0
CATEGORIES=		misc

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=		ports@freebsddiary.org

RUN_DEPENDS=	svstat:${PORTSDIR}/sysutils/daemontools \
				${SITE_PERL}/XML/Writer.pm:${PORTSDIR}/textproc/p5-XML-Writer \
				${SITE_PERL}/XML/Node.pm:${PORTSDIR}/textproc/p5-XML-Node \
				${SITE_PERL}/Mail/Sender.pm:${PORTSDIR}/mail/p5-Mail-Sender \
				${SITE_PERL}/mach/Date/Calc.pm:${PORTSDIR}/devel/p5-Date-Calc \
				${SITE_PERL}/mach/DBD/Pg.pm:${PORTSDIR}/databases/p5-DBD-Pg \
				${SITE_PERL}/mach/Bundle/DBI.pm:${PORTSDIR}/databases/p5-DBI \
				procmail:${PORTSDIR}/mail/procmail \
				stunnel:${PORTSDIR}/security/stunnel \
				${PREFIX}/sbin/httpd:${PORTSDIR}/www/apache13-modssl \
				${PREFIX}/bin/php:${PORTSDIR}/lang/php4 \
				${LOCALBASE}/sbin/portsdb:${PORTSDIR}/sysutils/portupgrade \
				psql:${PORTSDIR}/databases/postgresql74-client \
				postmaster:${PORTSDIR}/databases/postgresql74-server \
				bacula-fd:${PORTSDIR}/sysutils/bacula-client \
				svc:${PORTSDIR}/sysutils/daemontools \
				mysqladmin:${PORTSDIR}/databases/mysql41-client \
				mysql_install_db:${PORTSDIR}/databases/mysql41-server \
				${PREFIX}/share/pear/HTML/Common.php:${PORTSDIR}/devel/pear-HTML_Common \
				${PREFIX}/share/pear/HTML/Page2.php:${PORTSDIR}/devel/pear-HTML_Page2 \
				${PREFIX}/lib/postgresql/pgcrypto.so:${PORTSDIR}/databases/postgresql-contrib \
				${SITE_PERL}/Class/Observable.pm:${PORTSDIR}/devel/p5-Class-Observable
				
NO_WRKSUBDIR=	YES
NO_BUILD=		YES

do-install: 	# empty

.include <bsd.port.mk>
