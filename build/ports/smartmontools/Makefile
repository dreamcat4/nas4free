PORTNAME=		smartmontools
PORTVERSION=	6.2
CATEGORIES=		sysutils
COMMENT=		S.M.A.R.T. disk monitoring tools
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		SF

CONFLICTS=		smartmontools-devel-[0-9]*

USE_GMAKE=		yes
NO_STAGE=       yes

GNU_CONFIGURE=	yes
CONFIGURE_ARGS=	--with-docdir=${DOCSDIR} --disable-sample \
			--disable-dependency-tracking --disable-drivedb


CFLAGS:=		${CFLAGS:S/-O2/-O/} -Wno-write-strings


do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartctl ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${CP} -pv ${WRKSRC}/smartd_warning.sh ${NAS4FREE_ROOTFS}/${PREFIX}/etc

.include <bsd.port.mk>
