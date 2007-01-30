__________ Free NAC Windows GUI  ___________

Description:
  This Windows Executable can be used to connect to the NAC Database and manage it.
  We cannot provide source code at the moment as its proprietary, but you can use it for free.
  This is issue will probably be solved by replacing it with a WebGUI, depending on the Team 
  resources (contributions are welcome).

  Copyright (C) 2006 Swisscom, FreeNAC Core Team, Sean Boran, http://www.FreeNAC.net
  

Installation:
  1. Copy vmps.exe and vmps.xml to a folder on your Windows PC or a Network Share, e.g. c:\nac.
  2. Configure vmps.xml with a text edit such as 'wordpad'
    - Change the IP address of the server
    - Create a key containing an SQL username/password, with rights to access the NAC database.
      If you use an SQL username/password other than the demo one, if will need to be changed 
      on the mysql side, and on the windows side.
      You generate a new key by:
    
      a) Starting the GUI vmps.exe
      b) Admin -> Encrypt User
      c) Fill in the Username and Password, and click on Generate
      d) Copy the value of the 'generated key' filed to the 'auth' field in vmps.xml
      e) Restart the GUI, and press "Connect"


Using this GUI on the Online FreeNAC database
---------------------------------------------
  1. Download vmps.exe from http://svn.sourceforge.net/viewvc/opennac/branches/2.1/WindowsGUI/
  2. Download the demo config file vmps.xml from
     http://svn.sourceforge.net/viewvc/opennac/branches/2.1/WindowsGUI/demo1
  3. Then save these in a directory, and start it. 
  
  This will try to connected via the Internet to the FreeNAC demo database, which 
  is re-initiailised automatically every hour. 
  Note: this will not run behind a corporate proxying firewall, 
        port 3306/mysql needs to be open outgoing.


CHANGE HISTORY:  
--------------
v1.2.0.100: fix 'scannow' button, remove error message when writing patchtable.
v1.2.0.101/30.10.06/SB: Deleted systems were not correctly logged, Delection of changes to vlans in Edit Tab improved. For DEMO company allow Admin access for all users.
v1.2.0.101/30.10.06/SB: Handling of locates with '/' and not '.' for date seperators.
v1.2.0.102/10.11.06/SB: fix history log timestamps for some timezones, and add the demodb/vmps.xml
v1.2.0.103/24.11.06/SB: 
	Allow the port default vlan to be changed. 
	Add Queries for expired user, expired systems. Add button to export entire table to excel.
	Sources: delete old components taPorts, taSystems.
v1.2.0.103/4.12.06/SB: 	
  History field was accidentally read-only. Edit tab: make user lookups much faster via a dedicated query.
  Query tab: add Anti-Virus out of date
  Query tab: add the actual query SQL to the bottom of each query.  
  Allow vlans, switchs, ports, lookups rows to be deleted by an admin.
v1.2.0.104/18.12.06/SB: 	
  Remove references to old 'oper' table. Remove 'user' column from patch cables tab.  
  Add a PatchCable column to the Overview tab, added an 'unmanaged' status to the Edit/Overvew tabs
v1.2.0.105/21.12.06/SB: 	  
  IP address visible in the Edit tab again. Record port restarts
  in the Change History. Remove some unneeded warnings.
v1.2.0.108/24.01.07/SB: 	  
  Add PatchCableEnabled to vmps.xml, which hides or shows relevant Tabs/fields.
  Performance: optimise queries, start reduced from 30 to 3 secs. To view Users on an office
   on the Ports Tab, doubleclick.
  Install DeveloperExpress tools for delphi and start using their improved components
  - Change grids in Change and Server log
  - Improved edit-User ComboBox to show key zuser details
  - Add Filter by Switch to the Ports page  


	