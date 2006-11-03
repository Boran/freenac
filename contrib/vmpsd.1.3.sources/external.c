#include <stdio.h>
#include <signal.h>
#include <sys/param.h>
#include <sys/wait.h>
#include <sys/types.h>
#include <errno.h>
#include <unistd.h>
#include <sys/stat.h>
#include <fcntl.h>

#include "config.h"
#include "log.h"
#include "vqp.h"
#include "external.h"

int 	external_logic = 0;
char	external_prog[256];
pid_t	external_pid = 0;

int	tocli[2];
int	fromcli[2];

RETSIGTYPE sig_term()
{

	vmps_log(SYSTEM|INFO, "Terminating external program (%d).", external_pid);
	if ( kill(external_pid, SIGTERM) < 0 ) {
		vmps_log(SYSTEM|FATAL, "Cannot send TERM signal to external program (%s).", strerror(errno));
		exit(1);
	}

	vmps_log(SYSTEM|INFO, "VMPSD TERMINATING.");
	exit(0);
}

RETSIGTYPE sig_child_e()
{
        int     pid;
        int     status;

        pid = wait3(&status, WNOHANG, (struct rusage *) 0);
	vmps_log(SYSTEM|INFO, "VMPSD EXITING (external program terminating prematurely)[%d].",pid);
	exit(1);
}

int spawn_external()
{

	pid_t	chpid;

	signal(SIGCHLD, sig_child_e);

	if ( pipe(tocli) < 0 ) {

		vmps_log(SYSTEM|FATAL, "Cannot create pipe (%s). Aborting.", strerror(errno));
		exit(1);

	}

	if ( pipe(fromcli) < 0 ) {

		vmps_log(SYSTEM|FATAL, "Cannot create pipe (%s). Aborting.", strerror(errno));
		exit(1);

	}

	chpid = fork();

	if ( chpid < 0 ) {

		vmps_log(SYSTEM|FATAL, "Cannot fork (%s). Aborting.", strerror(errno));
		exit(1);
	}

	if ( chpid == 0 ) {

		close(STDIN_FILENO);
		close(STDOUT_FILENO);
		close(STDERR_FILENO);

		if ( dup2(tocli[0],STDIN_FILENO) < 0 ) {

			vmps_log(SYSTEM|FATAL, "Cannot dup2 STDIN (%s). Aborting.", strerror(errno));
			exit(1);

		}

		if ( dup2(fromcli[1],STDOUT_FILENO) < 0 ) {

			vmps_log(SYSTEM|FATAL, "Cannot dup2 STDOUT (%s). Aborting.", strerror(errno));
			exit(1);

		}

		if ( execlp(external_prog,external_prog,NULL) < 0 ) {

			vmps_log(SYSTEM|FATAL, "Failed to execve '%s' (%s). Aborting.", external_prog, strerror(errno));
			exit(1);

		}

		/* not reached */
		exit(1);
	}

	external_pid = chpid;
	signal(SIGTERM, sig_term);

	if ( !debug ) {

		close(STDIN_FILENO);
		close(STDOUT_FILENO);
		close(STDERR_FILENO);

	}
}

int readline(int fd, char *buf, int size)
{

	char	c;
	int	r;
	char	*ptr;
	char	n;

	n = 0;
	ptr = buf;
	while ( n < size-1 ) {
	 	//vmps_log(VQP|DEBUG,">>>> n= %d", n);

		r = read(fd,&c,1);
	 	//vmps_log(VQP|DEBUG,">>>> r= %d", r);
		if ( r <= 0 ) break;
		if ( r == 1 ) { 
	 		//vmps_log(VQP|DEBUG,">>>> received %c", c);

			if ( c == '\n' ) break;
			*ptr++ = c; 
			n++; 
		}
	}

	*ptr = '\0';
	if ( n < 0 ) return(-1); else return(n); 
}

int get_vlan_external(VQP_REQUEST *r, char *vlan_name)
{

/*
external program input:

	<domain> <switch ip> <port> <mac address>

external program output

	ALLOW <vlan name>
	DENY
	SHUTDOWN
	DOMAIN

return

	0 - deny
	1 - allow
	2 - shutdown
	3 - domain

*/

	int	n;
	char	str[256];
	char	buf[256];
	char	retcode[256];
	char	vname[256];
	
	snprintf(str,255,"%s %s %s %s %02x%02x.%02x%02x.%02x%02x\n", 
			r->domain,
			inet_ntoa(r->client_ip),
			r->port,
			r->vlan,
			r->mac[0], r->mac[1], r->mac[2], r->mac[3], r->mac[4], r->mac[5]
	);

	vmps_log(VQP|INFO, ">>>> Sending: %s ", str);
	write(tocli[1], str, strlen(str));
	//vmps_log(VQP|DEBUG, ">>>> Sent: %s ", str);

	n = readline(fromcli[0], buf, 255); 
	//vmps_log(VQP|DEBUG,">>>> received %s", buf);

	sscanf(buf,"%s %s",retcode, vname);
	strncpy(vlan_name,vname,VLAN_NAME_MAX);

	if ( strcmp(retcode,"ALLOW") ) { strcpy(vlan_name,""); }

	vmps_log(VQP|DEBUG, "External prog says: %s %s", retcode, vlan_name);

	if ( !strcmp(retcode,"ALLOW") ) { return 1; }
	if ( !strcmp(retcode,"SHUTDOWN") ) { return 2; }
	if ( !strcmp(retcode,"DOMAIN") ) { return 3; }

	return 0;
}

void do_request_external(int sock, VQP_REQUEST *r )
{

	char	vlan_name[VLAN_NAME_MAX+1];

	switch ( get_vlan_external(r, vlan_name) ) {

		case	0:
			send_response(sock, VQP_RSP_DENY, r, NULL);
			print_action(r,"DENY",vlan_name);
			break;

		case	1:
			send_response(sock, VQP_RSP_NERR, r, vlan_name);
			print_action(r,"ALLOW",vlan_name);
			break;

		case	2:
			send_response(sock, VQP_RSP_SHUT, r, NULL);
			print_action(r,"SHUTDOWN",vlan_name);
			break;

		case	3:
			send_response(sock, VQP_RSP_DOMA, r, NULL);
			print_action(r,"DOMAIN MISMATCH",vlan_name);
			break;

	}
}


