#include "config.h"

#include <stdlib.h>
#include <signal.h>

#if HAVE_UNISTD_H
#include <unistd.h>
#endif

#include "vqp.h"
#include "log.h"
#include "external.h"

struct in_addr	bind_address; 
unsigned int	port_number = 1589;
char    	db_fname[256];

int parse_options(int argc, char **argv)
{
	char	opt;
	char	*options = "a:de:f:l:p:";

	opterr = 0;
	
	opt = getopt(argc, argv, options);
	while ( opt > 0 ) {

		switch (opt) {

			case 'a':
				if ( optarg == NULL ) return 0;
				if ( !inet_aton(optarg, &bind_address) ) return 0;	
				break;

			case 'd':
				debug = 1;
				break;

			case 'e':
				strncpy(external_prog, optarg, 255);
				external_prog[255] = '\0';
				external_logic = 1;
				break;
				
			case 'f':
				strncpy(db_fname, optarg, 255);
				db_fname[255] = '\0';
				break;
				
			case 'l':
				if ( sscanf(optarg,"%x",&log_level) != 1) return 0;
				break;

			case 'p':
				if ( sscanf(optarg,"%d",&port_number) != 1) return 0;
				break;

			default:
				return 0;
				break;
		}
		opt = getopt(argc, argv, options);
	}

	return 1;
}

void usage()
{
	printf("\n");
	printf("Options:\n");
	printf("\n");
	printf("\t-a ip      address to bind to (any)\n");
	printf("\t-d         do not detach, log to stderr also\n");
	printf("\t-e path    use external program for mac to vlan assignment\n");
	printf("\t           when/if used with -f, -f is disregarded\n");
	printf("\t-f file    read VMPS database from file (/etc/vmps.db)\n");
	printf("\t-l level   set logging level:\n");
	printf("\t                 0x0100 - fatal,\n");
	printf("\t                 0x0200 - info,\n");
	printf("\t                 0x0400 - warning,\n");
	printf("\t                 0x0800 - debug,\n");
	printf("\t                 0x0001 - system,\n");
	printf("\t                 0x0002 - parser,\n");
	printf("\t                 0x0004 - vqp\n");
	printf("\t-p port    port to listen on (1589)\n");
	printf("\n");
}

RETSIGTYPE handle_sighup() {

	if ( external_logic ) return;
	vmps_log(PARSER|INFO, "RECEIVED SIGHUP. Re-reading config file");
	drop_data();
	parse_db_file(db_fname);
}

int main(int argc, char **argv) 
{
	int			sock;
	struct sockaddr_in	serv_addr;

	VQP_REQUEST		r;

	bind_address.s_addr = INADDR_ANY;
	strncpy(db_fname,SYSCONFDIR,240);
	strcat(db_fname,"/vlan.db");

	if ( !parse_options(argc,argv) ) {
		usage();
		exit(1);
	}

	if ( !debug ) daemon_start(1); 

	if ( external_logic ) {
		spawn_external();
	} else {
		parse_db_file(db_fname);
	}
	
	if ( (sock = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ) {
		vmps_log(FATAL|SYSTEM, "Cannot create a socket.");
		exit(1);
	}

	bzero( (char *) &serv_addr, sizeof(serv_addr) );
	serv_addr.sin_family	 = AF_INET;
	serv_addr.sin_addr.s_addr = bind_address.s_addr;
	serv_addr.sin_port	 = htons(port_number);

	if ( bind(sock, (struct sockaddr *) &serv_addr, sizeof(serv_addr)) < 0 ) {
		vmps_log(FATAL|SYSTEM, "Cannot bind the socket.");
		exit(1);
	}

#ifdef HAVE_SIGACTION
	{
		struct sigaction action;
		action.sa_sigaction = handle_sighup;
		sigemptyset(&action.sa_mask);
		action.sa_flags = SA_SIGINFO;
		sigaction(SIGHUP, &action, NULL);
	}
#else
		signal(SIGHUP, handle_sighup);
#endif

	vmps_log(SYSTEM|INFO, "VMPSD STARTED. Waiting for requests");

	while (1) {
		
		if ( !get_request(sock, &r) ) {
			if ( (log_level & 0xFF00) >= DEBUG ) print_request(&r);
			
			if ( external_logic ) 	{ do_request_external(sock, &r); }
			else			{ do_request(sock, &r); }

		}
	}
}


