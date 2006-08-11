#include "config.h"

#include <stdio.h>
#include <ctype.h>
#include <string.h>
#include <stdarg.h>
 
#include "log.h"
#include "data.h"

#define	LINESZ		255

FILE	*f;
char	line[LINESZ] = ""; 
char	*lp = line;
int	lno = 0;

char	str[LINESZ];
char	str1[LINESZ];
char	str2[LINESZ];
char	str3[LINESZ];

void parse_error(const char *token) {

	vmps_log(PARSER|FATAL, "Syntax error in line %d near \"%s\".", lno, token);

	exit(1);

}

void open_db_file(const char *fname) {

	f = fopen(fname, "r");
	if ( f == NULL ) {
		vmps_log(FATAL|SYSTEM, "Cannot open db file.");
		exit(1);
	} 
}

void close_db_file() {

	fclose(f);		
}

char get_char() {

	if ( *lp == '\0' ) {
		if ( fgets(line, LINESZ, f) == NULL ) return '\0';
		lp = line;
		lno++;
	}

	while ( *lp == '!' ) {
		if ( fgets(line, LINESZ, f) == NULL ) return '\0';
		lp = line;
		lno++;
	}

	return *lp++;
}

int get_string(char *sp) {

	char	c;

	strcpy(sp, "");

	while ( (c = get_char()) && (isspace(c)) ) ;

	if ( !c ) return 0;

	if ( c == '"' ) {

		while ( c = get_char() ) {
			if ( c == '"' ) { *sp++ = '\0'; return 1; }
			if ( isprint(c) ) *sp++ = c; else return -1;
		}
		
		return -1;
	} else {

		*sp++ = c;
		while ( c = get_char() ) {
			if ( isgraph(c) ) *sp++ = c; 
			else {
				*sp++ = '\0';
				return 1;
			}
		}
		
		*sp++ = '\0';
		return 1;
	}
}

void *ports_to_add_to;

void add_port(const void *nodep, const VISIT which, const int depth) 
{
	PORT_ENTRY 	*p,*p1,**p2;

	switch(which)
	{
               case postorder:
               case leaf:
                 p = *(PORT_ENTRY **)nodep;
		 vmps_log(PARSER|DEBUG, "COPY PORT: %s", p->name);
		 p1 = copy_port(p);
		 p2 = tsearch(p1, ports_to_add_to, compare_port); 
		 if ( p1 != *p2 ) free_port(p1);
                 break;
               case preorder:
                 break;
               case endorder:
                 break;
	}
	return;
}

void insert_pol_vlan_pg(VLAN_ENTRY *vlan, PORT_GROUP_ENTRY *pg )
{
	ports_to_add_to = &(vlan->ports); 
	twalk( pg->members, add_port );
	vlan->restricted = 1;
}

PORT_GROUP_ENTRY *pg_to_add;

void add_port_group(const void *nodep, const VISIT which, const int depth) 
{
	char 		*v_name;
	VLAN_ENTRY	*v;

	switch(which)
	{
		case postorder:
		case leaf:
			v_name = *(char **)nodep;
			v = find_vlan(v_name);
			if ( v == NULL ) 
	  			vmps_log(PARSER|WARNING, "No ports in vlan %s. A typo?", v_name);
			else
				insert_pol_vlan_pg(v, pg_to_add); 
			break;
		case preorder:
			break;
		case endorder:
			break;
	}
	return;
}

int insert_pol_vg_pg(char *vg_name, char *pg_name)
{
	VLAN_GROUP_ENTRY	*vg;
	PORT_GROUP_ENTRY	*pg;

	vg = find_vlan_group(vg_name);
	if ( ! vg ) {
	  vmps_log(PARSER|FATAL, "No VLAN GROUP %s. A typo?", vg_name);
	  exit(1); 
	}

	pg = find_port_group(pg_name);
	   
	if ( ! pg ) {	
	  vmps_log(PARSER|FATAL, "No PORT GROUP  %s. A typo?", pg_name);
	  exit(1); 
	}

	pg_to_add = pg;
	twalk(vg->members, add_port_group); 

	return 0;		
}

int insert_pol_vg_port(char *vg_name, char *device, char *port)
{
	VLAN_GROUP_ENTRY	*vg;
	PORT_GROUP_ENTRY	*pg;
	PORT_ENTRY *p, *gp;

	vg = find_vlan_group(vg_name);
	if ( ! vg ) {
	  vmps_log(PARSER|FATAL, "No VLAN GROUP %s. A typo?", vg_name);
	  exit(1); 
	}

	pg = new_port_group("_temporary_");

	p = new_port(device, port);
	insert_port( &(pg->members), p);

	pg_to_add = pg;
	twalk(vg->members, add_port_group); 

	free_port_group(pg);
	
	return 0;		
}

#ifdef	HAVE_SNMP
void parse_snmp_param()
{

	if ( ! get_string(str1) ) parse_error(str1); 
	if ( ! get_string(str2) ) parse_error(str2); 
		if ( !strcasecmp("community", str1) ) { 
			strncpy(community, str2, 255); 
			community[254] = '\0';
		}
	else parse_error(str1); 

	get_string(str);

}
#endif

void parse_vmps_param()
{

	if ( ! get_string(str1) ) parse_error(str1); 
	if ( ! get_string(str2) ) parse_error(str2); 
		if ( !strcasecmp("domain", str1) ) { 
			str2[VLAN_NAME_MAX] = '\0';
			strcpy(vmps_domain, str2); 
		}
		else if ( !strcasecmp("mode", str1) ) {
			if ( !strcasecmp("open", str2) ) { vmps_mode_open = 1; }
			else if ( !strcasecmp("secure", str2) ) { vmps_mode_open = 0; }
			else { parse_error(str2); } 
		}
		else if ( !strcasecmp("fallback", str1) ) {
			str2[VLAN_NAME_MAX] = '\0';
			strcpy(vmps_fallback, str2); 
		}
		else if ( !strcasecmp("no-domain-req", str1) ) {
			if ( !strcasecmp("allow", str2) ) { vmps_no_domain_req = 1; }
			else if ( !strcasecmp("deny", str2) ) { vmps_no_domain_req = 0; }
			else { parse_error(str2); } 
		}
	else parse_error(str1); 

	get_string(str);

}

void parse_macs()
{
	u_char		x[ETH_ALEN];
	unsigned int	x0, x1, x2, x3, x4, x5;

#ifdef	HAVE_SNMP
	long		speed;
	int		duplex;
#endif
	
	if ( !get_string(str) ) parse_error(str);

	while ( !strcasecmp("address", str) ) {

		if ( !get_string(str1) ) parse_error(str);
		if ( !get_string(str2) ) parse_error(str1);
		if ( !get_string(str3) ) parse_error(str2);
		if ( strcasecmp("vlan-name", str2) ) parse_error(str2);

		if (  sscanf(str1, "%02x%02x.%02x%02x.%02x%02x",
				&x0, &x1, &x2, &x3, &x4, &x5 ) != ETH_ALEN ) 
			parse_error(str1);

		x[0] = x0;
		x[1] = x1;
		x[2] = x2;
		x[3] = x3;
		x[4] = x4;
		x[5] = x5;
		
		get_string(str);

#ifdef	HAVE_SNMP
		speed = 0; duplex = 0;
		if ( !strcasecmp("speed", str) ) {  

			if ( !get_string(str1) ) parse_error(str);
			if ( sscanf(str1,"%ld",&speed) != 1 )
				if ( !strcasecmp("auto",str1) ) speed = 1;
				else parse_error(str1);

			get_string(str);
			if ( !strcasecmp("duplex", str) ) {  

				if ( !get_string(str1) ) parse_error(str);
				if ( !strcasecmp("half",str1) ) duplex = 1;
				else if ( !strcasecmp("full",str1) ) duplex = 2;
				else parse_error(str1);
				get_string(str);
			}
		}

		insert_mac(x, str3, speed, duplex); 
#else
		insert_mac(x, str3); 
#endif

	}
}

void parse_port_groups()
{
	char	pg[LINESZ];

	if ( !get_string(str) ) parse_error(str);

	strcpy(pg, str);
	while ( get_string(str) ) {
		if ( !strcasecmp("device", str) ) {
			if ( !get_string(str1) ) parse_error(str);
			if ( !get_string(str2) ) parse_error(str1);
			if ( !strcasecmp("port", str2) ) {

				if ( !get_string(str3) ) parse_error(str2);
				insert_port_group(pg, str1, str3); 

			}
			else if ( !strcasecmp("all-ports", str2) ) {

				insert_port_group(pg, str1, "--ALL--"); 

			}
			else parse_error(str2);
		}
		else if ( !strcasecmp("default-vlan", str) ) {
			if ( !get_string(str1) ) parse_error(str);
			insert_port_group_defaultvlan(pg, str1);
		}
		else if ( !strcasecmp("fallback-vlan", str) ) {
			if ( !get_string(str1) ) parse_error(str);
			insert_port_group_fallback(pg, str1);
		}
		else break;
	}
}

void parse_vlan_groups()
{
	char	vg[LINESZ];

	if ( ! get_string(str) ) parse_error(str);   	

	strcpy(vg, str);
	while ( get_string(str) && !strcasecmp("vlan-name", str) ) {

		if ( !get_string(str1) ) parse_error(str);
		insert_vlan_group(vg,str1); 

	}
}

void parse_policies()
{

	char	v_name[LINESZ];
	char	vg_name[LINESZ];
	char	pg_name[LINESZ];

	PORT_GROUP_ENTRY	*pg;

	strcpy(v_name,"");
	strcpy(vg_name,"");
	    
	if ( !get_string(str) ) parse_error(str);   	
	if ( !strcasecmp("vlan-name", str) ) {
		if ( !get_string(str1) ) parse_error(str);
		strcpy(v_name, str1);
	}
	else if ( !strcasecmp("vlan-group", str) ) {
		if ( !get_string(str1) ) parse_error(str);
		strcpy(vg_name, str1);
	}
	else parse_error(str);

	while ( get_string(str) ) {
		if ( !strcasecmp("port-group", str) ) {
			if ( !get_string(str1) ) parse_error(str);
			strcpy(pg_name, str1);
		
			if ( strcmp(v_name, "") ) {

				VLAN_ENTRY	 *v;
				PORT_GROUP_ENTRY *g;

				v=find_vlan(v_name);
				if ( v == NULL ) {
					vmps_log(PARSER|WARNING, "No MACs assigned to VLAN %s. A typo?", 
							v_name);
					break;
				}

				pg=find_port_group(pg_name);
				if ( ! pg ) {
					vmps_log(PARSER|FATAL, "No PORT GROUP  %s. A typo?", pg_name);
					exit(1); 
				}
				insert_pol_vlan_pg(v, pg);  
			}
			else 
				insert_pol_vg_pg(vg_name,str1);   
		}
		else if ( !strcasecmp("device", str) ) {

			char		p_name[LINESZ];
			VLAN_ENTRY	*v;
			PORT_ENTRY	*p;

			if ( !get_string(str1) ) parse_error(str);
			if ( !get_string(str2) ) parse_error(str);
			if ( !strcasecmp("port", str2) ) {
				if ( !get_string(str3) ) parse_error(str2);
				strcpy(p_name, str3);
			}
	        	else if ( !strcasecmp("all-ports", str2) ) {
				strcpy(p_name, "--ALL--");
			}
			else parse_error(str2);

			if ( strcmp(v_name, "") ) {

				v=find_vlan(v_name);
				if ( v ) {

					p = new_port(str1, p_name);
					insert_port( &(v->ports), p); 
					v->restricted = 1;
				} 
				else 
					vmps_log(PARSER|WARNING, "No MACs assigned to VLAN %s. A typo?", v_name);
			}
			else 
				insert_pol_vg_port(vg_name,str1,p_name);
		}
		else break;
	}
}

void dump_data()
{
#ifdef HAVE_SNMP
	vmps_log(PARSER|DEBUG, "SNMP COMMUNITY: %s", community);
#endif
	vmps_log(PARSER|DEBUG, "MACS --------------");
	twalk(macs, print_mac);
	vmps_log(PARSER|DEBUG, "VLANS -------------");
	twalk(vlans, print_vlan);
	vmps_log(PARSER|DEBUG, "VLAN GROUPS -------");
	twalk(vlan_groups, print_vlan_group);
	vmps_log(PARSER|DEBUG, "PORT GROUPS -------");
	twalk(port_groups, print_port_group); 
	vmps_log(PARSER|DEBUG, "PORTS -------------");
	twalk(ports, print_port);
}

void parse_db_file(const char *fname) {

	open_db_file(fname);

	strcpy(vmps_domain, "");
	strcpy(vmps_fallback,"");
	vmps_mode_open = 1;
	vmps_no_domain_req = 0;

	get_string(str);
	while ( strcmp(str,"") ) {
		if 	( ! strcasecmp("vmps", str) ) 			{ parse_vmps_param(); } 
		else if	( ! strcasecmp("vmps-mac-addrs", str) ) 	{ parse_macs(); }
		else if ( ! strcasecmp("vmps-port-group", str) )	{ parse_port_groups(); }
		else if ( ! strcasecmp("vmps-vlan-group", str) )	{ parse_vlan_groups(); }
		else if ( ! strcasecmp("vmps-port-policies", str) ) 	{ parse_policies(); } 
#ifdef	HAVE_SNMP
		else if ( ! strcasecmp("snmp", str) ) 			{ parse_snmp_param(); } 
#endif
		else parse_error(str);
	}

	close_db_file();
	if ( debug ) dump_data(); 
}

