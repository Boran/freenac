#include "config.h"

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include <ucd-snmp/ucd-snmp-includes.h>
 
#include "log.h"

int set_port_speed(	char *host, 
			char *community, 
			char *port, 
			long speed, 
			int  duplex
                  )
{
	struct snmp_session session, *s;
	struct snmp_pdu *pdu, *response = NULL;
	struct variable_list *vars;
	int status;
	int ret = 1;
	int m, p;

	/* portAdminSpeed and portDuplex from CISCO-STACK-MIB.my */

	oid	oidPortSpeed[15]  = {1,3,6,1,4,1,9,5,1,4,1,1,9,0,0};
	oid	oidPortDuplex[15] = {1,3,6,1,4,1,9,5,1,4,1,1,9,0,0};

	if ( sscanf(port, "%d/%d", &m, &p) != 2 ) {
		vmps_log(SNMP|FATAL, "Invalid port name: %s", port);
		return 1;
	}

	oidPortSpeed[13] = m;
	oidPortSpeed[14] = p;

	oidPortDuplex[13] = m;
	oidPortDuplex[14] = p;

	snmp_sess_init(&session);

	session.peername = host;
	session.version = SNMP_VERSION_2c;

	session.community = community;
	session.community_len = strlen(community);

	s = snmp_open(&session);	
 
	if ( s == NULL ) {
		vmps_log(SNMP|FATAL, "Cannot open SNMP session to %s.", host);
		return 1;
	}

	pdu = snmp_pdu_create(SNMP_MSG_SET);

	if ( speed != 0 )
	if ( snmp_add_var(pdu, oidPortSpeed, sizeof(oidPortSpeed), 
			  'I', (const char *) &speed) ) {
		vmps_log(SNMP|FATAL, "Cannot add var - speed.");
		return 1;
	}

	if ( duplex != 0 )
	if ( snmp_add_var(pdu, oidPortDuplex, sizeof(oidPortDuplex), 
			  'i', (const char *) &duplex ) ) {
		vmps_log(SNMP|FATAL, "Cannot add var - duplex.");
		return 1;
	}

	status = snmp_synch_response(s, pdu, &response);

	if (status == STAT_SUCCESS) {
		if (response->errstat != SNMP_ERR_NOERROR)
			vmps_log(SNMP|FATAL, "SNMP set failed host: %s port: %s  speed: %ld, duplex: %d", host, port, speed, duplex);
        	else {
			vmps_log(SNMP|DEBUG, "SNMP set host: %s port: %s  speed: %ld, duplex: %d", host, port, speed, duplex);
			ret = 0;
		}
	}
	else if (status == STAT_TIMEOUT) {
			vmps_log(SNMP|FATAL, "SNMP set timeout host: %s port: %s  speed: %ld, duplex: %d", host, port, speed, duplex);
	}
	else {    /* status == STAT_ERROR */
			vmps_log(SNMP|FATAL, "SNMP set error host: %s port: %s  speed: %ld, duplex: %d", host, port, speed, duplex);
	}

	snmp_free_pdu(pdu);
	if (response) snmp_free_pdu(response);
	snmp_close(s);

	return ret;
}

