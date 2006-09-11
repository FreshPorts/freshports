#
# $Id: Makefile,v 1.1.2.13 2006-09-11 02:07:58 dan Exp $
#
# Install the ports needed by FreshPorts
#
# Copyright (c) 2002-2006 DVL Software Limited
#

PORTNAME=	FreshPorts-ports
PORTVERSION=	1.0
CATEGORIES=	misc
COMMENT=	For use by Dan Langille only

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=	ports@freebsddiary.org

BUILD_DEPENDS=	${LOCALBASE}/bin/psql:${PORTSDIR}/databases/postgresql81-client \
		${LOCALBASE}/bin/postmaster:${PORTSDIR}/databases/postgresql81-server \
		${PREFIX}/lib/postgresql/pgcrypto.so:${PORTSDIR}/databases/postgresql-contrib 

RUN_DEPENDS=	${LOCALBASE}/bin/svstat:${PORTSDIR}/sysutils/daemontools \
		${SITE_PERL}/XML/Writer.pm:${PORTSDIR}/textproc/p5-XML-Writer \
		${SITE_PERL}/XML/Node.pm:${PORTSDIR}/textproc/p5-XML-Node \
		${SITE_PERL}/Mail/Sender.pm:${PORTSDIR}/mail/p5-Mail-Sender \
		${SITE_PERL}/mach/Date/Calc.pm:${PORTSDIR}/devel/p5-Date-Calc \
		${SITE_PERL}/mach/Bundle/DBI.pm:${PORTSDIR}/databases/p5-DBI \
		${SITE_PERL}/mach/DBD/Pg.pm:${PORTSDIR}/databases/p5-DBD-Pg \
		${LOCALBASE}/bin/procmail:${PORTSDIR}/mail/procmail \
		${LOCALBASE}/sbin/stunnel:${PORTSDIR}/security/stunnel \
		${PREFIX}/sbin/httpd:${PORTSDIR}/www/apache13-modssl \
		${PREFIX}/bin/php:${PORTSDIR}/lang/php4 \
		${LOCALBASE}/sbin/portsdb:${PORTSDIR}/sysutils/portupgrade \
		${LOCALBASE}/sbin/bacula-fd:${PORTSDIR}/sysutils/bacula-client \
		${LOCALBASE}/bin/mysqladmin:${PORTSDIR}/databases/mysql41-client \
		${LOCALBASE}/bin/mysql_install_db:${PORTSDIR}/databases/mysql41-server \
		${PREFIX}/share/pear/HTML/Common.php:${PORTSDIR}/devel/pear-HTML_Common \
		${PREFIX}/share/pear/HTML/Page2.php:${PORTSDIR}/devel/pear-HTML_Page2 \
		${SITE_PERL}/Class/Observable.pm:${PORTSDIR}/devel/p5-Class-Observable \
		${SITE_PERL}/LWP/Simple.pm:${PORTSDIR}/www/p5-libwww \
		${SITE_PERL}/mach/Text/CSV_XS.pm:${PORTSDIR}/textproc/p5-Text-CSV_XS \
		${LOCALBASE}/bin/php:${PORTSDIR}/lang/php4-extensions \
		${LOCALBASE}/lib/python2.4/site-packages/psycopgmodule.so:${PORTSDIR}/databases/py-psycopg
				
NO_WRKSUBDIR=	YES
NO_BUILD=		YES

do-install: 	# empty

.include <bsd.port.mk>
