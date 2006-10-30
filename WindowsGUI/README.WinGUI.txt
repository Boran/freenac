__________ Free NAC  Windows GUI Version 1.2.099 ___________

[This is a primitive, prelimiary doc, more should follow]

This Windows Executable can be used to connect to the NAC Database and manage it.

We cannot provide source code at the moment as its proprietary, but you can use it for free.
This is issue will probably be solved by replacing it with a WebGUI, depending on the Team resources (contributions are welcome).

No installation is needed, just make sure that vmps.xml and vmps.exe are in the same directory.

Configuration in vmps.xml:
- Change the IP address of the server
- if you use an SQL username/password other than the demo one, if will need to be changed on the mysql side, and on the windows side you generate a new key by:
    a) Starting the GUI
    b) Admin -> Encrypt User
    c) Fill in the Username and Password, and click on Generate
    d) Copy the value of the 'generated key' filed to the 'auth' field in vmps.xml
    e) Restart the GUI, and press "Connect"


CHANGES:
v1.2.0.100: fix 'scannow' button, remove error message when writing patchtable.
v1.2.0.101/30.10.06/SB: Deleted systems were not correctly logged, Delection of changes to vlans in Edit Tab improved. For DEMO company allow Admin access for all users.
v1.2.0.101/30.10.06/SB: Handling of locates with '/' and not '.' for date seperators.