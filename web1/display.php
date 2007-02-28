<?php

include_once('config.inc');
include_once('functions.inc');
include_once('print.inc');

db_connect();

$single_host = mysql_real_escape_string($_GET["single_host"]);

echo print_host($single_host);

vmps_footer();


?>
