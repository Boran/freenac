#include "config.h"

#include <stdlib.h>
#include <search.h>
#include <stdlib.h>

#include "data.h"
#include "log.h"

void 	*macs = NULL;
void 	*vlans = NULL;
void	*ports = NULL;
void 	*vlan_groups = NULL;
void 	*port_groups = NULL;

char	vmps_domain[VLAN_NAME_MAX+1];
int	vmps_mode_open;
char	vmps_fallback[VLAN_NAME_MAX+1];
int	vmps_no_domain_req;

#ifdef	HAVE_SNMP
char	community[255];
#endif

/* --------------------------------------------------------------------------- */

void *xmalloc(unsigned n) {
	void *p;
	p = malloc(n);
	if(p != NULL) {
		vmps_log(DEBUG|SYSTEM, "ALLOCATE: %x : %d bytes",p,n);
		return p;
	}
	vmps_log(FATAL|SYSTEM, "Insufficient memory.");
	exit(1);
}

void *xfree(void *p) {

	if (p == NULL) return;
	vmps_log(DEBUG|SYSTEM, "FREE: %x",p);
	free(p);
}

/* --------------------------------------------------------------------------- */

int compare_mac(const void *pa, const void *pb) {
	return memcmp( ((MAC_ENTRY *)pa)->mac, ((MAC_ENTRY *)pb)->mac, 6 ); 
}

void print_mac(const void *nodep, const VISIT which, const int depth) 
{
	MAC_ENTRY 	*m;
	u_char		x[ETH_ALEN];
	void 		*val;

	switch(which)
	{
		case postorder:
		case leaf:
			m = *(MAC_ENTRY **)nodep;
			memcpy(x, m->mac, 6);
#ifdef	HAVE_SNMP
			vmps_log(PARSER|DEBUG, "MAC: %02x%02x%02x%02x%02x%02x VLAN: %s SPEED: %ld DUPLEX: %d", 
				x[0], x[1], x[2], x[3], x[4], x[5], m->vlan, m->speed, m->duplex);
#else
			vmps_log(PARSER|DEBUG, "MAC: %02x%02x%02x%02x%02x%02x VLAN: %s", 
				x[0], x[1], x[2], x[3], x[4], x[5], m->vlan);
#endif

			break;
		case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

#ifdef	HAVE_SNMP
int insert_mac(u_char *x, const char *vlan, const long speed, const int duplex)
#else
int insert_mac(u_char *x, const char *vlan)
#endif
{
	MAC_ENTRY	*ptr;
	MAC_ENTRY	**val;

	ptr=(MAC_ENTRY *)xmalloc(sizeof(MAC_ENTRY));
	memcpy(ptr->mac, x, ETH_ALEN);
#ifdef	HAVE_SNMP
	ptr->speed = speed;
	ptr->duplex = duplex;
#endif
	ptr->vlan = (char *)xmalloc(strlen(vlan)+1);
	strcpy(ptr->vlan, vlan);

	if ( strcasecmp(vlan,"--NONE--") ) 
		insert_vlan(vlan); 
 
	val = (MAC_ENTRY **)tsearch((void *)ptr, &macs, compare_mac); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"MAC insert failed.");
		exit(1);
	}

	if ( *val != ptr ) free_mac(ptr);

	return 0;
}

MAC_ENTRY *find_mac(u_char mac[ETH_ALEN])
{
	MAC_ENTRY	**mp;
	MAC_ENTRY	m;

	memcpy(m.mac, mac, ETH_ALEN);
	mp=tfind(&m,&macs,compare_mac);

	if ( mp ) return (*mp); else return NULL;

}

void discard_macs1() 
{
	MAC_ENTRY 	*m,*m1;
	u_char		x[ETH_ALEN];

	while ( macs != NULL ) {
		m = *(MAC_ENTRY **)macs;
		memcpy(x, m->mac, 6);
		vmps_log(PARSER|DEBUG, "DELETING MAC: %02x%02x%02x%02x%02x%02x VLAN: %s", 
			x[0], x[1], x[2], x[3], x[4], x[5], m->vlan);
		m1 = *(MAC_ENTRY **)tdelete(m, &macs, compare_mac);
		xfree(m->vlan);
		xfree(m);
	}
}

void free_mac(MAC_ENTRY *mac)
{
	if (mac == NULL) return;

	xfree(mac->vlan);
	xfree(mac);
}

void drop_macs(void **macs) 
{
	MAC_ENTRY 	*m;
	u_char		x[ETH_ALEN];

	if ( macs == NULL ) return;

	while ( *macs != NULL ) {
		m = **(MAC_ENTRY ***) macs;
		memcpy(x, m->mac, 6);
		vmps_log(PARSER|DEBUG, "DELETING MAC: %02x%02x%02x%02x%02x%02x VLAN: %s", 
			x[0], x[1], x[2], x[3], x[4], x[5], m->vlan);
		tdelete(m, macs, compare_mac);
		free_mac(m);
	}
}

/* --------------------------------------------------------------------------- */

int compare_vlan(const void *pa, const void *pb) {
	return strcmp( ((VLAN_ENTRY *)pa)->name, ((VLAN_ENTRY *)pb)->name );
}

void print_vlan(const void *nodep, const VISIT which, const int depth) 
{
	VLAN_ENTRY 	*vl;

	switch(which)
	{
               case postorder:
               case leaf:
                 vl = *(VLAN_ENTRY **)nodep;
		 vmps_log(PARSER|DEBUG, "VLAN: %s, RESTRICTED: %d", vl->name, vl->restricted);
		 twalk(vl->ports, print_port);
                 break;
               case preorder:
                 break;
               case endorder:
                 break;
	}
	return;
}

VLAN_ENTRY *new_vlan(const char *vlan_name)
{
	VLAN_ENTRY	*ptr;

	ptr=(VLAN_ENTRY *)xmalloc(sizeof(VLAN_ENTRY));
	ptr->restricted	= 0;
	ptr->name = (char *)xmalloc(strlen(vlan_name)+1);
	strcpy(ptr->name, vlan_name);
	ptr->ports = NULL;

	return ptr;
}

int insert_vlan(const char *vlan_name)
{
	VLAN_ENTRY	*ptr;
	VLAN_ENTRY	**val;

	ptr=(VLAN_ENTRY *)xmalloc(sizeof(VLAN_ENTRY));
	ptr->restricted	= 0;
	ptr->name = (char *)xmalloc(strlen(vlan_name)+1);
	strcpy(ptr->name, vlan_name);
	ptr->ports = NULL;

	val = (VLAN_ENTRY **)tsearch((void *)ptr, &vlans, compare_vlan); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"VLAN insert failed.");
		exit(1);
	}

	if ( *val != ptr ) free_vlan(ptr);

	return 0;
}

VLAN_ENTRY *find_vlan(char *vlan)
{
	VLAN_ENTRY	**vp;
	VLAN_ENTRY	v;

	v.name=vlan;
	vp=tfind(&v,&vlans,compare_vlan);

	if ( vp ) return (*vp); else return NULL;

}

void free_vlan(VLAN_ENTRY *vlan)
{
	if (vlan == NULL) return;

	xfree(vlan->name);
	if (vlan->ports != NULL)
		if ( (*(PORT_ENTRY **)vlan->ports) != NULL ) 
			drop_ports(&(vlan->ports));
	xfree(vlan);
}

void drop_vlans(void **vlans) 
{
	VLAN_ENTRY 	*v;

	if ( vlans == NULL ) return;

	while ( *vlans != NULL ) {
		v = **(VLAN_ENTRY ***) vlans;
		vmps_log(PARSER|DEBUG, "DELETING VLAN: %s, RESTRICTED: %d", v->name, v->restricted);
		tdelete(v, vlans, compare_vlan);
		free_vlan(v);
	}
}

/* --------------------------------------------------------------------------- */

int compare_str(const void *pa, const void *pb) {
	return strcmp( (char *)pa, (char *)pb );
}

void print_str(const void *nodep, const VISIT which, const int depth) 
{
	char 	*str;
	void 	*val;

	switch(which) {

		case postorder:
		case leaf:
			str = *(char **)nodep;
			vmps_log(PARSER|DEBUG, "  MEMBER: %s", str);
			break;
			case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

int insert_string(void **root, const char *str)
{
	char	*ptr;
	void	**val;

	ptr=(char *)xmalloc(strlen(str)+1);
	strcpy(ptr, str);

	val = tsearch((void *)ptr, root, compare_str); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"STRING insert failed.");
		exit(1);
	}

	if ( *val != ptr ) xfree(ptr);

	return 0;
}

/* --------------------------------------------------------------------------- */

int compare_vlan_group_member(const void *pa, const void *pb) {
	return strcmp( (char *)pa, (char *)pb );
}

/* --------------------------------------------------------------------------- */

int compare_vlan_group(const void *pa, const void *pb) {
	return strcmp( 	((VLAN_GROUP_ENTRY *)pa)->name, 
			((VLAN_GROUP_ENTRY *)pb)->name 
	);
}

void print_vlan_group(const void *nodep, const VISIT which, const int depth) 
{
	VLAN_GROUP_ENTRY 	*vg;
	void 			*val;

	switch(which) {
		case postorder:
		case leaf:
			vg = *(VLAN_GROUP_ENTRY **)nodep;
			vmps_log(PARSER|DEBUG, "VLAN GROUP: %s", vg->name);
			twalk(vg->members, print_str);
			break;
		case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

int insert_vlan_group(const char *vlan_group, char *member)
{
	VLAN_GROUP_ENTRY	*ptr;
	VLAN_GROUP_ENTRY	**val;

	ptr=(VLAN_GROUP_ENTRY *)xmalloc(sizeof(VLAN_GROUP_ENTRY));
	ptr->name = (char *)xmalloc(strlen(vlan_group)+1);
	strcpy(ptr->name, vlan_group);
	ptr->members = NULL;

	val = tsearch((void *)ptr, &vlan_groups, compare_vlan_group); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"VLAN GROUP insert failed.");
		exit(1);
	}

	if ( *val != ptr ) free_vlan_group(ptr);

	if ( member ) 
		insert_string( &((*val)->members), member); 

	return 0;
}

VLAN_GROUP_ENTRY *find_vlan_group(char *vg_name)
{

	VLAN_GROUP_ENTRY	**vg;
	VLAN_GROUP_ENTRY	g;

	g.name=vg_name;
	vg=tfind(&g,&vlan_groups,compare_vlan_group);

	if ( vg ) return (*vg); else return NULL;

}

void free_vlan_group(VLAN_GROUP_ENTRY *vg)
{
	char *m;

	if (vg == NULL) return;

	xfree(vg->name);
	while ( vg->members != NULL ) {
		m = *(char **) (vg->members);
		vmps_log(PARSER|DEBUG, "  DELETING MEMBER: %s", m);
		tdelete(m, &(vg->members), compare_str);
		xfree(m);
	}
	xfree(vg);
}

void drop_vlan_groups(void **vlan_groups) 
{
	VLAN_GROUP_ENTRY	*vg;

	if ( vlan_groups == NULL ) return;

	while ( *vlan_groups != NULL ) {
		vg = **(VLAN_GROUP_ENTRY ***) vlan_groups;
		vmps_log(PARSER|DEBUG, "DELETING VLAN GROUP: %s", vg->name);
		tdelete(vg, vlan_groups, compare_vlan_group);
		free_vlan_group(vg);
	}
}

/* --------------------------------------------------------------------------- */

int compare_port_group(const void *pa, const void *pb) {
	return strcmp( 	((PORT_GROUP_ENTRY *)pa)->name, 
			((PORT_GROUP_ENTRY *)pb)->name 
	);
}

void print_port_group(const void *nodep, const VISIT which, const int depth) 
{
	PORT_GROUP_ENTRY 	*pg;
	void 			*val;

	switch(which) {
		case postorder:
		case leaf:
			pg = *(PORT_GROUP_ENTRY **)nodep;
			vmps_log(PARSER|DEBUG, "PORT GROUP: %s", pg->name);
			if ( pg->defaultvlan != NULL)
				vmps_log(PARSER|DEBUG, " defaultvlan: %s",
				pg->defaultvlan);
			if ( pg->fallback != NULL)
				vmps_log(PARSER|DEBUG, " fallback: %s",
				pg->fallback);
			twalk(pg->members, print_port);
			break;
		case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

PORT_GROUP_ENTRY *new_port_group(const char *port_group)
{
	PORT_GROUP_ENTRY	*ptr;

	ptr=(PORT_GROUP_ENTRY *)xmalloc(sizeof(PORT_GROUP_ENTRY));
	ptr->name = (char *)xmalloc(strlen(port_group)+1);
	strcpy(ptr->name, port_group);
	ptr->members = NULL;
	ptr->defaultvlan = NULL;
	ptr->fallback = NULL;

	vmps_log(SYSTEM|DEBUG,"PORT GROUP alloc %s.",ptr->name);

	return ptr;
}

PORT_GROUP_ENTRY *get_port_group(const char *port_group)
{
	PORT_GROUP_ENTRY	*ptr;
	PORT_GROUP_ENTRY	**val;

	ptr = new_port_group(port_group);

	val = tsearch((void *)ptr, &port_groups, compare_port_group); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"PORT GROUP insert failed.");
		exit(1);
	}

	if ( *val != ptr ) {
		vmps_log(SYSTEM|DEBUG,"PORT GROUP free %s.",ptr->name);
		xfree(ptr->name);
		xfree(ptr);
	}

	return *val;
}

int insert_port_group(const char *port_group, char *device, char *port)
{
	PORT_GROUP_ENTRY	*ptr;

	ptr=get_port_group(port_group);

	if ( device ) {
		PORT_ENTRY *p, *gp;
		p = new_port(device, port);
		insert_port( &(ptr->members), p); 
		gp = copy_port(p);
		gp->parent=ptr;
		insert_port( &ports, gp);
	}

	return 0;
}

int insert_port_group_defaultvlan(const char *port_group, char *defaultvlan)
{
	PORT_GROUP_ENTRY	*ptr;

	ptr=get_port_group(port_group);
	ptr->defaultvlan=(char *)xmalloc(strlen(defaultvlan)+1);
	strcpy(ptr->defaultvlan, defaultvlan);
	insert_vlan(defaultvlan);
}

int insert_port_group_fallback(const char *port_group, char *fallback)
{
	PORT_GROUP_ENTRY	*ptr;

	ptr=get_port_group(port_group);
	ptr->fallback=(char *)xmalloc(strlen(fallback)+1);
	strcpy(ptr->fallback, fallback);
	insert_vlan(fallback);
}

PORT_GROUP_ENTRY *find_port_group(char *port_group)
{

	PORT_GROUP_ENTRY	**pg;
	PORT_GROUP_ENTRY	g;

	g.name=port_group;
	pg=tfind(&g,&port_groups,compare_port_group);

	if ( pg ) return (*pg); else return NULL;

}

void free_port_group(PORT_GROUP_ENTRY *pg)
{

	if (pg == NULL) return;

	vmps_log(SYSTEM|DEBUG, "FREE PORT GROUP: %s", pg->name);
	xfree(pg->name);
	if (pg->members != NULL)
		if ( (*(PORT_ENTRY **)pg->members) != NULL ) 
			drop_ports(&(pg->members));
	if (pg->defaultvlan != NULL) xfree(pg->defaultvlan);
	if (pg->fallback != NULL) xfree(pg->fallback);
	xfree(pg);
}

void drop_port_groups(void **port_groups) 
{
	PORT_GROUP_ENTRY	*pg;

	if ( port_groups == NULL ) return;

	while ( *port_groups != NULL ) {
		pg = **(PORT_GROUP_ENTRY ***) port_groups;
		vmps_log(PARSER|DEBUG, "DELETING PORT GROUP: %s", pg->name);
		tdelete(pg, port_groups, compare_port_group);
		free_port_group(pg);
	}
	drop_ports(&ports);
}

/* --------------------------------------------------------------------------- */

int compare_port(const void *pa, const void *pb) {

	int     ret;
	ret = memcmp( 	(void *) &((PORT_ENTRY *)pa)->device,
			(void *) &((PORT_ENTRY *)pb)->device,
			sizeof(struct in_addr)
	);

	if ( !ret ) {
		ret = strcmp(	((PORT_ENTRY *)pa)->name, 
				((PORT_ENTRY *)pb)->name 
		);
	}
	return ret;
}

void print_port(const void *nodep, const VISIT which, const int depth) 
{
	PORT_ENTRY 	*p;
	void 		*val;

	switch(which)
	{
		case postorder:
		case leaf:
			p = *(PORT_ENTRY **)nodep;
			vmps_log(PARSER|DEBUG, "  DEVICE: %s PORT: %s", inet_ntoa(p->device), p->name);
			if (p->parent != NULL)
				vmps_log(PARSER|DEBUG, "   parent: %s",
					p->parent->name);
			break;
		case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

PORT_ENTRY *new_port(char *device, const char *port)
{
	PORT_ENTRY	*ptr;
	
	ptr=(PORT_ENTRY *)xmalloc(sizeof(PORT_ENTRY));
	ptr->parent = NULL;

	if ( !inet_aton(device, &(ptr->device)) ) {
		parse_error(device);
		exit(1);
	}

	ptr->name = (char *)xmalloc(strlen(port)+1);
	strcpy(ptr->name, port);

	return ptr;
}

PORT_ENTRY *copy_port(PORT_ENTRY *port)
{
	PORT_ENTRY	*ptr;

	if ( port == NULL ) return NULL;
	
	ptr=(PORT_ENTRY *)xmalloc(sizeof(PORT_ENTRY));

	memcpy((void *)ptr,(void *)port,sizeof(*port));
	ptr->name = (char *)xmalloc(strlen(port->name)+1);
	strcpy(ptr->name, port->name);
	ptr->parent = NULL;

	return ptr;
}

int insert_port(void **root, PORT_ENTRY  *port)
{
	PORT_ENTRY	*ptr;
	PORT_ENTRY	**val;
	
	val = (PORT_ENTRY **) tsearch( (void *)port, root, compare_port); 
	if ( val == NULL ) {
		vmps_log(SYSTEM|FATAL,"PORT insert failed.");
		exit(1);
	}

	if ( *val != port ) free_port(port);

	return 0;
}

PORT_ENTRY *find_port(PORT_ENTRY **root, struct in_addr device, char *port_name)
{
	PORT_ENTRY	**pp;
	PORT_ENTRY	p;

	if ( root == NULL ) root = (PORT_ENTRY **)&ports;

	p.name = port_name;
	memcpy((void *)&p.device, (void *)&device, sizeof(device));

	pp=tfind(&p,(void **)root,compare_port);
	if ( pp ) return (*pp); 

	p.name = "--ALL--";
	pp=tfind(&p,(void **)root,compare_port);
	if ( pp ) return ( *pp); else return NULL;

}

void free_port(PORT_ENTRY *port)
{
	if (port == NULL) return;

	xfree(port->name);
	xfree(port);
}

void drop_ports(void **ports) 
{
	PORT_ENTRY 	*p;

	if ( ports == NULL ) return;

	while ( *ports != NULL ) {
		p = **(PORT_ENTRY ***) ports;
		vmps_log(PARSER|DEBUG, "  DELETING PORT: DEVICE: %s PORT: %s", inet_ntoa(p->device), p->name);
		tdelete(p, ports, compare_port);
		free_port(p);
	}
}

/* --------------------------------------------------------------------------- */
void drop_data()
{

        drop_macs(&macs);
        drop_vlans(&vlans);
        drop_vlan_groups(&vlan_groups);
        drop_port_groups(&port_groups);

}

