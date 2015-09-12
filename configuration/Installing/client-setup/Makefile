#
# $Id: Makefile,v 1.4 2011-06-14 17:32:41 dan Exp $
#
# Install the ports needed by FreshPorts
#
# Copyright (c) 2002-2006 DVL Software Limited
#

PORTNAME=	FreshPorts-client
PORTVERSION=	1.0
CATEGORIES=	misc
COMMENT=	For use by Dan Langille only

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=	ports@freebsddiary.org

BUILD_DEPENDS=	${LOCALBASE}/bin/psql:${PORTSDIR}/databases/postgresql92-client \
		${LOCALBASE}/lib/python2.4/site-packages/psycopgmodule.so:${PORTSDIR}/databases/py-psycopg

RUN_DEPENDS=	${SITE_PERL}/XML/Writer.pm:${PORTSDIR}/textproc/p5-XML-Writer \
		${SITE_PERL}/XML/Node.pm:${PORTSDIR}/textproc/p5-XML-Node \
		${SITE_PERL}/Text/Unidecode.pm:${PORTSDIR}/converters/p5-Text-Unidecode \
		${SITE_PERL}/Mail/Sender.pm:${PORTSDIR}/mail/p5-Mail-Sender \
		${SITE_PERL}/Date/Calc.pm:${PORTSDIR}/devel/p5-Date-Calc \
		${SITE_PERL}/mach/Bundle/DBI.pm:${PORTSDIR}/databases/p5-DBI \
		${SITE_PERL}/mach/List/MoreUtils.pm:${PORTSDIR}/lang/p5-List-MoreUtils \
		${SITE_PERL}/mach/DBD/Pg.pm:${PORTSDIR}/databases/p5-DBD-Pg \
		${LOCALBASE}/bin/procmail:${PORTSDIR}/mail/procmail \
		${SITE_PERL}/Class/Observable.pm:${PORTSDIR}/devel/p5-Class-Observable \
		${SITE_PERL}/LWP/Simple.pm:${PORTSDIR}/www/p5-libwww 
				
NO_WRKSUBDIR=	YES
NO_BUILD=		YES

do-install: 	# empty

.include <bsd.port.mk>
