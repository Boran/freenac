__________ Free NAC Windows GUI  ___________

Description:
  This Windows Executable can be used to connect to the NAC Database and manage it.
  
  Copyright (C) 2007 Swisscom, FreeNAC Core Team, Sean Boran, http://www.FreeNAC.net

License  
  We cannot provide source code at the moment as its proprietary, but you can use it for free.
  You may not charge other for this tool, and must always preserve this file and the CHANGELOG if
  copying.
  
  The OpenSource issue will probably be solved by replacing it with a WebGUI, depending on the Team 
  resources (contributions are welcome).


  
  
  
Using this GUI on the Online FreeNAC database
---------------------------------------------
  1. Download vmps.exe from http://svn.sourceforge.net/viewvc/opennac/branches/2.2/WindowsGUI/
  2. Download the demo config file vmps.xml from
     http://svn.sourceforge.net/viewvc/opennac/branches/2.2/WindowsGUI/demo1
  3. Then save these in a directory, e.g. c:\nac., and start it. 
  
  This will try to connected via the Internet to the FreeNAC demo database, which 
  is re-initiailised automatically every hour. 
  Note: this will not run behind a corporate proxying firewall, 
        port 3306/mysql needs to be open outgoing.
        
        
Using this GUI on the FreeNAC Virtual Machine
---------------------------------------------
  1. Download vmps.exe and vmps.xml from 
     http://svn.sourceforge.net/viewvc/opennac/branches/2.2/WindowsGUI/     
  2. Then save these in a directory, e.g. c:\nac..
  3. Configure vmps.xml with a text edit such as 'wordpad'
     - Change the IP address of the server to corresponding to the FreeNAC VM 
  4. Start the NAC GUI by double clicking on the vmps.exe.

 
Full  Installation:
-------------------
  1. Copy vmps.exe and vmps.xml to a folder on your Windows PC or a Network Share, e.g. c:\nac.
  
  2. Configure vmps.xml with a text edit such as 'wordpad'
    - Change the IP address of the server
    
  3.  Configure the SQL username/password key, to access the NAC database. This
      will need to be changed on the mysql side, and on the windows side.
      On the mysql server grant the rights to a user as in /opt/nac/doc/master_server_install.txt.
      
      Next, create a key containing an SQL username/password.
      You generate a new key by:
    
      a) Starting the GUI vmps.exe
      b) Admin -> Encrypt User
      c) Fill in the Username and Password, and click on Generate
      d) Copy the value of the 'generated key' filed to the 'auth' field in vmps.xml
      e) Restart the GUI, and press "Connect"

   
   4. Ensure the settings in the 'config' DB table are correct.
      To enable all features, set value=1:
        update config set value='1' WHERE name='StaticInvEnabled';
        update config set value='1' WHERE name='NmapEnabled';
        update config set value='1' WHERE name='AntiVirusEnabled';
        update config set value='1' WHERE name='PatchCableEnabled';  
        
      To enable the DEMO company setting in vmps.xml to work:                  
        update config set value='1' WHERE name='DemoMode';  
      normally this is 0, meaning that a company called DEMO will not work. 
      If it is=1, the DEMO company is allowed in vmps.xml and the user is given administrator access!        


See also the CHANGELOG.txt


	