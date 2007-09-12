This directory contains example scripts used when migration a site from V2.1 to V2.2

The v2.1 DB schema was not very normalised, so some data is really difficult to import cleanly, for example the office/location/building fields. These have been centralsied into one lookup table in V2.2.

However the key information in the systems table is easy enough to transfer.
Some scripts were created to migrate one existing installation - you can try to use them, but without any warranty as they are specific to this installation.

These scripts are based on the fact that  V2.2 uses the db 'opennac' by default, whereas v2.1 uses 'inventory'. This means that both database can co-exist on the same mysql instance.

If you want, you can try to migrate the data - this is non-destructive. 
This is however a good moment to note that, as FreeNAC is a sensible software, the main database should be backed up regularly.

To prepare the migration :
- create a new database (default is 'opennac')
- grant proper rights, according to freenac documentation
- add the content of new_db_schema_2.2.sql and new_db_values_2.2.sql to the new database
- if you didn't call the new database 'opennac', edit the 'move_data.php' script and modify the value of $new_db

To do the migration :
- run the 'move_data.php' script

After the migration :
- the 'post_move_example1.php' was used to restore the consitency of the data and make sure proper values were selected. you may want to adapt it to your data.



