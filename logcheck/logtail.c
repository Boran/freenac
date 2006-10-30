/* ------------------------------------------------------------------*/
/* logtail.c -- ASCII file tail program that remembers last position.*/
/* 								     */
/* Author:							     */
/* Craig H. Rowland <crowland@psionic.com> 15-JAN-96		     */
/*		    <crowland@vni.net>				     */
/*								     */
/* Please send me any hacks/bug fixes you make to the code. All      */
/* comments are welcome!					     */
/*								     */
/* Idea for program based upon the retail utility featured in the    */
/* Gauntlet(tm) firewall protection package published by Trusted     */
/* Information Systems Inc. <info@tis.com>			     */
/*								     */
/* This program will read in a standard text file and create an      */
/* offset marker when it reads the end. The offset marker is read    */
/* the next time logtail is run and the text file pointer is moved   */
/* to the offset location. This allows logtail to read in the next   */
/* lines of data following the marker. This is good for marking log  */
/* files for automatic log file checkers to monitor system events.   */
/*								     */
/* This program covered by the GNU License. This program is free to  */
/* use as long as the above copyright notices are left intact. This  */
/* program has no warranty of any kind.				     */
/*								     */
/* VERSION 1.1: Initial release					     */
/*								     */
/*         1.11: Minor typo fix. Fixed NULL comparison.		     */
/* ------------------------------------------------------------------*/


#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sysexits.h>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
#include "bondlog.h"


#define MAX 1024		/* buffer */
#define MAX_PATH 255		/* increase this size if you need a longer path */
#define VERSION "1.11"




int
check_log (char *logname, char *offset_filename, FILE * out)
{

  FILE *input,			/* Value user supplies for input file */
   *offset_output;		/* name of the offset output file */

  struct stat file_stat;

  char inode_buffer[MAX],	/* Inode temp storage */
    offset_buffer[MAX],		/* Offset temp storage */
    buffer[1024];		/* I/O Buffer */

  long offset_position;		/* position in the file to offset */

  /* Check if the file exists in specified directory */
  /* Open as a binary in case the user reads in non-text files */
  if ((input = fopen (logname, "rb")) == NULL)
    {
      printf ("File %s cannot be read.\n", logname);
      return (2);
    }

  /* see if we can open an existing offset file and read in the inode */
  /* and offset */
  if ((offset_output = fopen (offset_filename, "rb")) != NULL)
    {				/* read in the saved inode number */
      if ((fgets (buffer, MAX, offset_output)) != NULL)	/* nested if()...yuch */
	strlcpy (inode_buffer, buffer, sizeof (inode_buffer));	/* copy in inode */

      /* read in the saved decimal offset */
      if ((fgets (buffer, MAX, offset_output)) != NULL)	/* nested if()...yuch */
	strlcpy (offset_buffer, buffer, sizeof (offset_buffer));	/* copy in offset */

      fclose (offset_output);	/* We're done, clean up */
    }
  else				/* can't read the file? then assume no offset file exists */
    {
      strcpy (inode_buffer, "0");	/* this inode will be set later */
      offset_position = 0L;	/* if the file doesn't exist, assume */
      /* offset of 0 because we've never */
      /* tailed it before */
    }


  if ((stat (logname, &file_stat)) != 0)	/* load struct */
    {
      printf ("Cannot get %s file size.\n", logname);
      return (3);
    }

  /* if the current file inode is the same, but the file size has */
  /* grown SMALLER than the last time we checked, then something  */
  /* suspicous has happened (log file edited) and we'll report it */
  if (((atol (inode_buffer)) == (file_stat.st_ino))
      && (atol (offset_buffer) > (file_stat.st_size)))
    {
      offset_position = 0L;	/* reset offset and report everything */
      printf ("***************\n");
      printf
	("*** WARNING ***: Log file %s is smaller than last time checked!\n",
	 logname);
      printf ("***************	 This could indicate tampering.\n");
    }

  /* if the current file inode or size is different than that in the */
  /* offset file then assume it has been rotated and set offset to zero */
  if (((atol (inode_buffer)) != (file_stat.st_ino))
      || (atol (offset_buffer) > (file_stat.st_size)))
    offset_position = 0L;
  else				/* If the file inode is the same as old inode set the new offset */
    offset_position = atol (offset_buffer);	/*get value and convert */

#ifdef DEBUG
  printf ("inodebuf: %s offsetbuf: %s offsetpos: %ld\n", inode_buffer,
	  offset_buffer, offset_position);
#endif

  if ((fseek (input, offset_position, 0)) == -1)
    {
      fprintf (stderr, "Cannot seek to %ld position", offset_position);
    }
  /* set the input file stream to */
  /* the offset position */
  /* Print the file */
  while ((fgets (buffer, 1024, input)) != NULL)
    {
      if ((fprintf (out, "%s", buffer)) == 0)
	{
	  fprintf (stderr, "Cannot write to tempfile!");
	}
    }
  /* after we are done we need to write the new offset */
  if ((offset_output = fopen (offset_filename, "w")) == NULL)
    {
      printf ("File %s cannot be created. Check your permissions.\n",
	      offset_filename);
      fclose (input);
      fclose (offset_output);
      return (4);
    }
  else
    {
      if ((chmod (offset_filename, 00600)) != 0)	/* Don't let anyone read offset */
	{
	  printf ("Cannot set permissions on file %s\n", offset_filename);
	  return (3);
	}
      else
	{
	  offset_position = ftell (input);	/* set new offset */
	  fprintf (offset_output, "%ld\n%ld", (long) file_stat.st_ino,
		   offset_position);
	  /* write it */
	}
    }

  fclose (input);		/* clean up */
  fclose (offset_output);

  return 0;			/* everything A-OK */
}
