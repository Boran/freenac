Future ideas: 
•	One issue that I is the vmps_lastseen service tailing the messages log all the time.  It causes slow logins and responsiveness suffers some.  One solution is to pass the information from vmpsd_external directly to a separate process so monitoring the messages log is not needed.  The NACRequest class was developed so it could be passed to the resolve() method to determine how to reply to vmpsd, send the reply, and then be passed to the vmps_lastseen process via shared memory, file, etc.   
•	I have over 20 routers in my environment.  Running the router_mac_ip script against 20+ routers doesn't make a lot of sense for a handful of new devices each week.  I think that the scanning could be more targeted to the routers that are in the same building as the switch that has the new device.  Just a thought.  Also, the value length of 255 in the config table is too small to hold all my routers.  Any array my be the short term work around.

Let me know what you think.  I'm currently looking at process fork class that would allow spawning off vmps_lastseen thread(s) within vmpsd_oo script.  This would give you one single service to manage in the start up scripts.  It would also allow for multiple "lastseen" threads to run. 
One last thing.  If you run vmpsd_oo with vmpsd, the first request will fail.  The php script produces a "\n" as the first thing which causes the first request to fail.  All subsequent requests should respond appropriately.  I haven't had a time to chase it down yet. 

Cheers, 


------------------------
Scott LeFevre