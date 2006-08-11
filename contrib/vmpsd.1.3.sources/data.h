#ifndef __DATA__

#define	__DATA__

#include <search.h>
#include <unistd.h>

#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#define PORT_NAME_MAX   12
#define VLAN_NAME_MAX   32
#define DOMAIN_NAME_MAX 32

#define ETH_ALEN	6

typedef struct {
  u_char        mac[ETH_ALEN];
  char          *vlan;
#ifdef	HAVE_SNMP
  long		speed;
  int		duplex;
#endif
} MAC_ENTRY;

typedef struct {
  int           restricted;
  char          *name;
  void          *ports;
} VLAN_ENTRY;

typedef struct {
  char  *name;
  void  *members;
} VLAN_GROUP_ENTRY;

typedef struct {
  char  *name;
  char  *defaultvlan;
  char  *fallback;
  void  *members;
} PORT_GROUP_ENTRY;

typedef struct {
  struct in_addr        device;
  char                  *name;
  PORT_GROUP_ENTRY	*parent;
} PORT_ENTRY;

extern void 	*macs;
extern void 	*vlans;
extern void 	*ports;
extern void 	*vlan_groups;
extern void 	*port_groups;

extern char	vmps_domain[VLAN_NAME_MAX+1];
extern int	vmps_mode_open;
extern char	vmps_fallback[VLAN_NAME_MAX+1];
extern int	vmps_no_domain_req;

#ifdef	HAVE_SNMP
extern char	community[255];
#endif

void *xmalloc(unsigned n);
int compare_mac(const void *pa, const void *pb);
void print_mac(const void *nodep, const VISIT which, const int depth); 
#ifdef	HAVE_SNMP
int insert_mac(u_char *x, const char *vlan, const long speed, const int duplex);
#else
int insert_mac(u_char *x, const char *vlan);
#endif
MAC_ENTRY *find_mac(u_char m[ETH_ALEN]);
void free_mac(MAC_ENTRY *mac);
void drop_macs(void **macs);

int compare_vlan(const void *pa, const void *pb);
void print_vlan(const void *nodep, const VISIT which, const int depth); 
VLAN_ENTRY *new_vlan(const char *vlan_name);
int insert_vlan(const char *vlan_name);
VLAN_ENTRY *find_vlan(char *vlan);
void free_vlan(VLAN_ENTRY *vlan);
void drop_vlans(void **vlans);

int compare_str(const void *pa, const void *pb);
void print_str(const void *nodep, const VISIT which, const int depth);
int insert_string(void **root, const char *str);

int compare_vlan_group_member(const void *pa, const void *pb);
int compare_vlan_group(const void *pa, const void *pb);
void print_vlan_group(const void *nodep, const VISIT which, const int depth);
int insert_vlan_group(const char *vlan_group, char *member);
VLAN_GROUP_ENTRY *find_vlan_group(char *vg_name);
void free_vlan_group(VLAN_GROUP_ENTRY *g);
void drop_vlan_groups(void **vlan_groups);

int compare_port_group(const void *pa, const void *pb);
void print_port_group(const void *nodep, const VISIT which, const int depth);
PORT_GROUP_ENTRY *new_port_group(const char *port_group);
PORT_GROUP_ENTRY *get_port_group(const char *port_group);
int insert_port_group(const char *port_group, char *device, char *port);
int insert_port_group_defaultvlan(const char *port_group, char *defaultvlan);
int insert_port_group_fallback(const char *port_group, char *fallback);
PORT_GROUP_ENTRY *find_port_group(char *port_group);
void free_port_group(PORT_GROUP_ENTRY *g);
void drop_port_groups(void **port_groups);

int compare_port(const void *pa, const void *pb);
void print_port(const void *nodep, const VISIT which, const int depth);
PORT_ENTRY *new_port(char *device, const char *port);
PORT_ENTRY *copy_port(PORT_ENTRY *port);
int insert_port(void **root, PORT_ENTRY  *port);
PORT_ENTRY *find_port(PORT_ENTRY **root, struct in_addr device, char *port_name);
void free_port(PORT_ENTRY *port);
void drop_ports(void **ports);

void drop_data();

#endif
