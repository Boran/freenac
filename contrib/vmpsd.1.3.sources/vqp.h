#ifndef __VQP__

#define	__VQP__	

#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h> 

#include "data.h"

#define	SERVER_UDP_PORT	22222	
#define	MAX_PACKET_SIZE	2048

#define VQP_CLI_ADDR	0x00000c01
#define VQP_PORT_NAME	0x00000c02
#define VQP_VLAN_NAME	0x00000c03
#define VQP_DOMAIN_NAME	0x00000c04
#define VQP_ETH_PACKET	0x00000c05
#define VQP_MAC_NULL	0x00000c06
#define VQP_UNKNOWN	0x00000c07
#define VQP_MAC_ADDR	0x00000c08

#define VQP_REQ_JOIN	0x01
#define VQP_REQ_RECONF	0x03

#define	VQP_RSP_NERR	0x00
#define	VQP_RSP_DENY	0x03
#define	VQP_RSP_SHUT	0x04
#define	VQP_RSP_DOMA	0x05

typedef	struct {
	u_char	unkn1;	/* const 0x01 ?? */
	u_char	req_type;
	u_char	response;
	u_char	nitems;
	unsigned int 	seq_no;
} VQP_HEADER;

typedef struct {
	VQP_HEADER	head;
	struct in_addr	client_ip;
	char		port[PORT_NAME_MAX+1];
	char		vlan[VLAN_NAME_MAX+1];
	char		domain[DOMAIN_NAME_MAX+1];
	u_char		mac[ETH_ALEN];
	struct sockaddr_in 	cli;
} VQP_REQUEST;


int get_request(int sock, VQP_REQUEST *r);
void print_request(VQP_REQUEST *r);
void do_request(int sock, VQP_REQUEST *r );

#endif
