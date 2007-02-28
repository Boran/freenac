#include "config.h"

#include "log.h"

#include <stdio.h>
#include <signal.h>
#include <sys/param.h>
#include <sys/wait.h>
#include <errno.h>

#ifdef	SETPGRP_VOID
#define SYSV
#else
#define	VMPS_CHECK_BSD
#endif

extern int	errno;

#ifdef	VMPS_CHECK_BSD
#include <sys/file.h>
#include <sys/ioctl.h>
#endif

#ifdef	HAVE_SYS_WAIT_H
#include	<sys/wait.h>
#endif

RETSIGTYPE sig_child()
{

#ifdef	VMPS_CHECK_BSD

	int	pid;
	int	status;
	while ( (pid = wait3(&status, WNOHANG, (struct rusage *) 0)) > 0 ) ;

#endif

}

daemon_start(ignsigcld)

	int	ignsigcld;	

{
	register int	childpid;

#ifdef	SIGTTOU
	signal(SIGTTOU, SIG_IGN);
#endif
#ifdef	SIGTTIN
	signal(SIGTTIN, SIG_IGN);
#endif
#ifdef	SIGTSTP
	signal(SIGTSTP, SIG_IGN);
#endif

	childpid = fork();
	if ( childpid < 0 ) {
		vmps_log(SYSTEM|FATAL, "can't fork");
		exit(1);
	} 

	if ( childpid > 0 ) exit(0); 

#ifdef 	VMPS_CHECK_BSD

	if ( setpgrp(0, getpid()) == -1 ) {
		vmps_log(SYSTEM|FATAL, "can't change process group");
		exit(1);
	}

	if ( (fd = open("/dev/tty", O_RDWR)) >= 0 ) {
		ioctl(fd, TIOCNOTTY, (char *)NULL);
		close(fd);
	}

#else

	if ( setpgrp() == -1 ) {
		vmps_log(SYSTEM|FATAL, "can't change process group");
		exit(1);
	}

	childpid = fork();
	if ( childpid < 0 ) {
		vmps_log(SYSTEM|FATAL, "can't fork");
		exit(1);
	}

	if ( childpid > 0 ) exit(0);
		
#endif

	errno = 0;
	umask(0);

	if ( ignsigcld ) {

#ifdef	VMPS_CHECK_BSD
		RETSIGTYPE sig_child();

		signal(SIGCHLD, sig_child);
#else
		signal(SIGCLD,SIG_IGN);
#endif

	}
}

