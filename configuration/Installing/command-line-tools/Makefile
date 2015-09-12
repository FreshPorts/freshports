#
# $Id: Makefile,v 1.2 2006-12-17 11:39:36 dan Exp $
#
# New ports collection makefile for: newbox-checklist
#
# Copyright (c) 2002-2005 DVL Software Limited
#

PORTNAME=	newbox-checklist
PORTVERSION=	1.0
CATEGORIES=	misc

MASTER_SITES=   # none
DISTFILES=      # none

MAINTAINER=	ports@freebsddiary.org
COMMENT=	For use by Dan Langille only

RUN_DEPENDS=	bash:${PORTSDIR}/shells/bash2 \
		joe:${PORTSDIR}/editors/joe \
		screen:${PORTSDIR}/sysutils/screen \
		sudo:${PORTSDIR}/security/sudo \
		xtail:${PORTSDIR}/misc/xtail \
		${PREFIX}/etc/logcheck.sh:${PORTSDIR}/security/logcheck \

NO_WRKSUBDIR=	YES
NO_BUILD=	YES

do-install: # empty

.include <bsd.port.mk>




