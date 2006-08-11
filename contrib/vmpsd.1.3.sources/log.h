#ifndef __LOG__

#define	__LOG__

/* Logging levels */

#define FATAL	0x0100
#define	INFO	0x0200
#define WARNING	0x0400
#define DEBUG 	0x0800

/* Logging type */

#define	SYSTEM	0x0001
#define	PARSER	0x0002
#define	VQP	0x0004

#ifdef   HAVE_SNMP
#define	SNMP	0x0008
#endif

extern int 	debug;
extern int	log_level;

extern void vmps_log(const int level, const char *fmt, ...);

#endif
