This directory contains example scripts used when migration a site from V2.1 to V2.2

The v2.1 DB schema was not very normalised, so some data is really difficult to import cleanly, for example the office/location/building fields. These have been centralsied into one lookup table in V2.2.

However the key information in the systems table is easy enough to transfer.

V2.2 uses the db 'opennac' by default, whereas v2.1 uses 'inventory'. So they can co-exist on the same mysql instance.


*TBD*: describe each script briefly.