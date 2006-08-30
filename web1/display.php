<?php

include_once('config.inc');
include_once('functions.inc');
include_once('print.inc');

db_connect();
import_request_variables("gp");

echo print_host($single_host);


?>
