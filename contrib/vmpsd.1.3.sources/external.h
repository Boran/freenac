#ifndef __EXTERNAL_GET_VLAN__

#define	__EXTERNAL_GET_VLAN__

extern int 	external_logic;
extern char 	external_prog[256];
extern pid_t	external_pid;

int get_vlan_external(VQP_REQUEST *r, char *vlan_name);
void do_request_external(int sock, VQP_REQUEST *r );

#endif
