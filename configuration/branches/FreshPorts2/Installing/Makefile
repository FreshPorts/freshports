# New ports collection makefile for: newbox-checklist
# Date created:	13 January 2002
# Whom:			Dan Langille
#
# $Id: Makefile,v 1.1.2.1 2002-05-25 22:53:23 dan Exp $
#

PORTNAME=	newbox-checklist
PORTVERSION=	1.0
CATEGORIES=	misc

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=	ports@freebsddiary.org

RUN_DEPENDS=	bash:${PORTSDIR}/shells/bash2 \
		joe:${PORTSDIR}/editors/joe \
		screen:${PORTSDIR}/misc/screen \
		sudo:${PORTSDIR}/security/sudo \
		xtail:${PORTSDIR}/misc/xtail \
		${PREFIX}/distributed.net/dnetc:${PORTSDIR}/misc/dnetc \
		${PREFIX}/etc/logcheck.sh:${PORTSDIR}/security/logcheck \

NO_WRKSUBDIR=	YES
NO_BUILD=	YES

do-install: # empty

.include <bsd.port.mk>




