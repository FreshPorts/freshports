#
# $Id: Makefile,v 1.4 2011-06-14 17:32:41 dan Exp $
#
# Install the ports needed by FreshPorts
#
# Copyright (c) 2002-2006 DVL Software Limited
#

PORTNAME=	FreshPorts-server
PORTVERSION=	1.0
CATEGORIES=	misc
COMMENT=	For use by Dan Langille only

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=	ports@freebsddiary.org

BUILD_DEPENDS=	${LOCALBASE}/bin/postmaster:${PORTSDIR}/databases/postgresql81-server \
		${PREFIX}/lib/postgresql/pgcrypto.so:${PORTSDIR}/databases/postgresql-contrib

RUN_DEPENDS=	${LOCALBASE}/bin/svstat:${PORTSDIR}/sysutils/daemontools \
		${PREFIX}/sbin/httpd:${PORTSDIR}/www/apache22 \
		${PREFIX}/bin/php:${PORTSDIR}/lang/php5 \
		${PREFIX}/share/pear/HTML/Common.php:${PORTSDIR}/devel/pear-HTML_Common \
		${PREFIX}/share/pear/HTML/Page2.php:${PORTSDIR}/devel/pear-HTML_Page2 \
		${PREFIX}/share/pear/Pager/Pager.php:${PORTSDIR}/devel/pear-Pager \
		${SITE_PERL}/Class/Observable.pm:${PORTSDIR}/devel/p5-Class-Observable \
		${LOCALBASE}/bin/php:${PORTSDIR}/lang/php5-extensions
				
NO_WRKSUBDIR=	YES
NO_BUILD=		YES

do-install: 	# empty

.include <bsd.port.mk>
