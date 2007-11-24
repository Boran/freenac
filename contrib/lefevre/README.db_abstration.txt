
Sean et al, 

I forgot this piece.  Add the following lines to you config.inc  It won't work without them. 

// For OO Testing 
$database['server']     = $dbhost; 
$database['username']   = $dbuser; 
$database['password']   = $dbpass; 
$database['database']   = $dbname; 



----- Forwarded by Scott LeFevreon 11/01/2007 02:44 PM ----- 
Sean, 

I've been trying to keep up to date on the enhancements that you've been making to FreeNAC.  I noticed the 2.2RC3 came out and started to review it.  I originally started looking at how I could make my coding changes more maintainable and quick to integrate the latest release of FreeNAC.  In your notes, it was mentioned that your looking at moving to a more object-oriented model.  This peaked my interested and started to do some digging.  (I also have a degree in programming that I rarely get to exercise.)  I've gone a bit further than I original intended but the results are interesting.  Here's what I've done. 

I've developed a completely OO based version of vmpsd_external.  I've used a set of classes that allows for quick setup and instantiation of  database rows into objects.  The classes come from http://fugitivethought.com/projects/php-oodb/index.php.  They were missing a few refinements so I've added a some of what's missing.  The classes are simple but allow for very quick and elegant/intuitive access to related objects either in a single dimension (1-to-1) or multidimensional (1-to-n).  The other advantage was that it didn't require a complex data modeling setup.  All that was needed was the class files, knowledge of the tables, the relationships, and you can get moving. 

Attached (vmpsd_oo.tar.gz) is what I've developed.  I've developed this against the 2.2RC3 release.  You should be able to untar this into your /opt/nac/bin directory and give it a try.  Here's the files included: 
•	OOdb-0.6.1.php - OO database access classes.  I made changes from what you can get from the website sited above so I changed the mod-level of the file.   
•	dbAdapter/ - directory with the MySQL class for OOdb. 
•	vmpsd_oo - this is the adaptation of vmpsd_external to use OO.   
•	classes.php - The classes I've developed for FreeNAC.  I haven't done a lot of modeling but more relied on what was needed to get the job done.  It could use some clean up!  A few classes you might want to look at are NACResolver, VMPSEngineV1, and VMPSEngineV2.  NACResolver is the container object for most of vmpsd.  VMPSEngineV1 and VMPSEngineV2 are based on NACResolver.  The primary difference between the two are the Resolve() methods.  In V1, I tried to approximate the logic you are using in your decide() function for the Resolve() method.  In V2, I adapted the decide() function that I developed for the Resolve() method.  This solution came to me as a great way to maintain compatibility between the current release and the changes that I had made.  I haven't tried to implement the get_port_status() function so checking for hubs doesn't work.

Cheers, 


------------------------
Scott LeFevre
