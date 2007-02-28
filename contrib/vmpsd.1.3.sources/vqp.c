#include "config.h"

#include "log.h"
#include "data.h"
#include "vqp.h"
#include "external.h"

int get_request(int sock, VQP_REQUEST *r)
{
	int	n;
	int	addr_len;
	u_char 	buf[MAX_PACKET_SIZE];
	u_char	*p;

	struct sockaddr_in 	cli;
	unsigned long int	type;
	unsigned short int	size;

	int	i;

	unsigned long int	auxli;
	unsigned short int	auxsi;

	addr_len = sizeof(cli);
	n = recvfrom(sock, buf, MAX_PACKET_SIZE, 0, (struct sockaddr *)&cli, &addr_len);

	if ( n < 0 ) {
		vmps_log(WARNING|VQP, "Error in recvfrom.");
		return 1;
	}

	if ( n < sizeof(VQP_HEADER) ) {
		vmps_log(WARNING|VQP, "VQP packet size error (header).");
		return 1;
	}

	p = buf;

	r->head.unkn1 		= *p++;
	r->head.req_type	= *p++;
	r->head.response	= *p++;
	r->head.nitems		= *p++;
	memcpy( (void *) &auxli, (void *) p, 4 );
	r->head.seq_no		= ntohl( auxli );
	p 			+= 4; 
	n			-= sizeof(VQP_HEADER);

	strcpy(r->port, "");
	strcpy(r->vlan, "");
	strcpy(r->domain, "");
	bzero((void *)r->mac, ETH_ALEN);
	bzero((void *)&(r->client_ip), sizeof(r->client_ip));

	memcpy((void *)&(r->cli), (void *)&cli, sizeof(cli));

	for (i=r->head.nitems; i>0; i--) {

		memcpy( (void *) &auxli, (void *) p, 4 );	
		type 	= ntohl( auxli );
		p 	+= 4;
		memcpy( (void *) &auxsi, (void *) p, 2 );
		size	= ntohs( auxsi );
		p	+= 2;

		if ( n < size ) {
			vmps_log(WARNING|VQP, "VQP packet size error (data).");
			return 1;
		}
 	
		switch (type) {
			case VQP_CLI_ADDR:
				memcpy( (void *) &auxli, (void *) p, 4 );
				r->client_ip.s_addr = auxli;
				p += size;
				break;

			case VQP_PORT_NAME:
				memcpy( (void *) r->port, (void *) p, size );
				r->port[size] = '\0';
				p += size;
				break;

			case VQP_VLAN_NAME:
				memcpy( (void *) r->vlan, (void *) p, size );
				r->vlan[size] = '\0';
				p += size;
				break;

			case VQP_DOMAIN_NAME:
				memcpy( (void *) r->domain, (void *) p, size );
				r->domain[size] = '\0';
				p += size;
				break;

			case VQP_ETH_PACKET:
				p += ETH_ALEN;
				memcpy( (void *) r->mac, (void *) p, ETH_ALEN); 
				p += ETH_ALEN;
				break;

			case VQP_MAC_NULL:
				memcpy( (void *) r->mac, (void *) p, ETH_ALEN); 
				p += ETH_ALEN;
				break;

			case VQP_UNKNOWN:
				p += size;
				break;

			case VQP_MAC_ADDR:
				memcpy( (void *) r->mac, (void *) p, ETH_ALEN); 
				p += ETH_ALEN;
				break;

			default:	
				vmps_log(WARNING|VQP,"Unknown data item %08x.", type);
				p += size;
				break;
		}
	}

	return 0;
}

int send_response(int sock, u_char action, VQP_REQUEST *r, char *vlan_name)
{
	VQP_HEADER	h;
	u_char		buf[MAX_PACKET_SIZE];
	u_char		*p;
	unsigned long int	data_type;
	unsigned short int	data_len;
	unsigned int		n = 0;

	h.unkn1		= 0x01;
	h.req_type	= r->head.req_type + 1;
	h.response	= action;

	if ( action == VQP_RSP_NERR ) h.nitems = 2;
	else h.nitems = 0;

	h.seq_no	= htonl(r->head.seq_no);

	p = buf;
	memcpy((void *)p, (void *)&h, sizeof(h));
	p += sizeof(h);
	n += sizeof(h);

	if ( action == VQP_RSP_NERR ) {
		data_type = htonl(VQP_VLAN_NAME);
		memcpy((void *)p, (void *)&data_type, sizeof(data_type));
		p += sizeof(data_type);
		n += sizeof(data_type);

		data_len = htons(strlen(vlan_name));		
		memcpy((void *)p, (void *)&data_len, sizeof(data_len));
		p += sizeof(data_len);
		n += sizeof(data_len);

		memcpy((void *)p, (void *)vlan_name, strlen(vlan_name));
		p += strlen(vlan_name);
		n += strlen(vlan_name);

		data_type = htonl(VQP_MAC_ADDR);
		memcpy((void *)p, (void *)&data_type, sizeof(data_type));
		p += sizeof(data_type);
		n += sizeof(data_type);

		data_len = htons(ETH_ALEN);		
		memcpy((void *)p, (void *)&data_len, sizeof(data_len));
		p += sizeof(data_len);
		n += sizeof(data_len);

		memcpy((void *)p, (void *)r->mac, ETH_ALEN);
		p += ETH_ALEN;
		n += ETH_ALEN;
	}

	if ( n != sendto(sock, buf, n, 0,(struct sockaddr *) &(r->cli), sizeof(r->cli)) ) {
		vmps_log(VQP|WARNING, "sento failed.");
	}
}

void print_action(VQP_REQUEST *r, char *str, char *vlan_name)
{

	vmps_log(VQP|INFO, "%s: %02x%02x%02x%02x%02x%02x -> %s, switch %s port %s",
			str,
			r->mac[0], r->mac[1], r->mac[2], r->mac[3], r->mac[4], r->mac[5],
			vlan_name,
			inet_ntoa(r->client_ip),
			r->port
	);

}

void print_request(VQP_REQUEST *r)
{
	vmps_log(VQP|DEBUG, "==================================");
	vmps_log(VQP|DEBUG, "VQP Request");
	vmps_log(VQP|DEBUG, "Unknown: %d",r->head.unkn1);
	vmps_log(VQP|DEBUG, "Request Type: %d",r->head.req_type);
	vmps_log(VQP|DEBUG, "Response: %d",r->head.response);
	vmps_log(VQP|DEBUG, "No. Data Items: %d",r->head.nitems);
	vmps_log(VQP|DEBUG, "Sequence No.: %ld",r->head.seq_no);

	vmps_log(VQP|DEBUG, "Client IP address: %s",inet_ntoa(r->client_ip));
	vmps_log(VQP|DEBUG, "Port name: %s",r->port);
	vmps_log(VQP|DEBUG, "Vlan name: %s",r->vlan);
	vmps_log(VQP|DEBUG, "Domain name: %s",r->domain);
	vmps_log(VQP|DEBUG, "MAC address: %02x%02x%02x%02x%02x%02x",
			r->mac[0], r->mac[1], r->mac[2], r->mac[3], r->mac[4], r->mac[5]);
}

int check_domain(char *domain)
{

	if ( strcmp(domain,"") && vmps_no_domain_req ) return 1;
	else if ( !strcasecmp(vmps_domain, domain) ) return 1;

	return 0;
}

int get_vlan(VQP_REQUEST *r, char *vlan_name)
{
	MAC_ENTRY	*pm;
	VLAN_ENTRY	*pv;
	PORT_ENTRY	*pe;

	pe = find_port(NULL, r->client_ip, r->port);
	pm = find_mac(r->mac);
	if ( pm == NULL ) {
		if ( pe != NULL && pe->parent->fallback != NULL ) {
			strcpy(vlan_name, pe->parent->fallback);
		}
		else {
			if ( !strcmp(vmps_fallback,"") ) return 0;
			else strcpy(vlan_name, vmps_fallback);
		}
	}
	else {
		if ( !strcasecmp(pm->vlan,"--DEFAULT--") ) {
			if ( pe != NULL && pe->parent->defaultvlan != NULL)
				strcpy(vlan_name, pe->parent->defaultvlan);
			else {
				if ( pe != NULL && pe->parent->fallback !=NULL )
					strcpy(vlan_name, pe->parent->fallback);
				else {
					if ( !strcmp(vmps_fallback,"") ) return 0;
					else strcpy(vlan_name, vmps_fallback);
				}
			}
		}
		else strcpy(vlan_name,pm->vlan);
	}

	if ( !strcasecmp(vlan_name,"--NONE--") ) return 0; 
	if ( !strcmp(vlan_name,"") ) return 0; 

	pv = find_vlan(vlan_name);
	if ( pv == NULL ) {
		if ( !strcasecmp(vlan_name,vmps_fallback) ) return 1;
		vmps_log(VQP|DEBUG, "Undefined vlan: %s.", vlan_name);
		return 0;
	}

	if ( pv->restricted ) return ( find_port((PORT_ENTRY **)&(pv->ports), r->client_ip, r->port) != NULL );

	if ( 
		strcasecmp("",r->vlan) && 
		strcasecmp("--NONE--",r->vlan) &&
		strcasecmp(vlan_name,r->vlan) 

	   ) return 0;

	return 1;
}

void do_request(int sock, VQP_REQUEST *r )
{

	char	vlan_name[VLAN_NAME_MAX+1];

	if ( 	r->head.req_type == VQP_REQ_JOIN ||
		r->head.req_type == VQP_REQ_RECONF ) {
	
		if ( !check_domain(r->domain) ) {
			send_response(sock, VQP_RSP_DOMA, r, NULL);
			print_action(r,"DOMAIN MISMATCH",NULL);
			return;
		}

		if ( !get_vlan(r, vlan_name) ) {

			if ( vmps_mode_open ) {
				send_response(sock, VQP_RSP_DENY, r, NULL);
				print_action(r,"DENY",NULL);
			} else {
				send_response(sock, VQP_RSP_SHUT, r, NULL);
				print_action(r,"SHUTDOWN",NULL);
			}

		}
		else { 
			send_response(sock, VQP_RSP_NERR, r, vlan_name);

			print_action(r,"ALLOW",vlan_name);

#ifdef HAVE_SNMP	
			{
				MAC_ENTRY *m;

				m = find_mac(r->mac);
				if ( (m->speed != 0) || (m->duplex != 0) ) 
					set_port_speed( inet_ntoa(r->client_ip),
							community,
							r->port,
							m->speed,
							m->duplex
					);
			}
#endif
		}
	}
	else
		vmps_log(VQP|WARNING, "Unexpected request: %d", r->head.req_type);

}

