#include "config.h"

#include <stdio.h>
#include <syslog.h>
#include <stdarg.h>

#include "log.h"

int 	debug = 0;
int	log_level = SYSTEM|PARSER|VQP|INFO;

static	int log_opened = 0;

void vmps_log(const int level, const char *fmt, ...)
{

	char	str[256];
	va_list ap;

	if ( ((log_level & 0xFF00) >= (level & 0xFF00)) &&
	     ((level & log_level & 0x00FF) > 0) ) {

		va_start(ap, fmt);

		if ( !log_opened ) {
			openlog("vmpsd", LOG_CONS, LOG_LOCAL6);
			log_opened = 1;
		}
		vsnprintf(str, 256, fmt, ap);
		syslog(LOG_INFO, "%s", str);

		if ( debug ) { 
			fprintf(stderr,"%s\n", str);
		}
			 
		va_end(ap);

	}
}

