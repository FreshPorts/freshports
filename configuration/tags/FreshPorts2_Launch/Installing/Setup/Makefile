#
# $Id: Makefile,v 1.1.2.1 2002-05-25 23:57:04 dan Exp $
#
# Install the ports needed by FreshPorts
#
# Copyright (c) 2002 DVL Software Limited

PORTNAME=		FreshPorts-ports
PORTVERSION=	1.0
CATEGORIES=		misc

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=		ports@freebsddiary.org

RUN_DEPENDS=	svstat:${PORTSDIR}/sysutils/daemontools \
				/usr/local/lib/perl5/site_perl/5.005/XML/Writer.pm:${PORTSDIR}/textproc/p5-XML-Writer \
				/usr/local/lib/perl5/site_perl/5.005/i386-freebsd/auto/XML/Node:${PORTSDIR}/textproc/p5-XML-Node \
				DBD-Pg:${PORTSDIR}/databases/p5-DBD-Pg \
				DBI:${PORTSDIR}/databases/p5-DBI \
				procmail:${PORTSDIR}/mail/procmail \
				stunnel:${PORTSDIR}/security/stunnel \
				apache+mod_ssl:${PORTSDIR}/www/apache13-modssl \
				mod_php4:${PORTSDIR}/www/mod_php4 \
				portupgrade:${PORTSDIR}/sysutils/portupgrade \
				psql:${PORTSDIR}/databases/postgresql7


NO_WRKSUBDIR=	YES
NO_BUILD=		YES

do-install: 	# empty

.include <bsd.port.mk>


