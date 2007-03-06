<?php

chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');

include_once('print.inc');

db_connect($readuser,$readpass);

$single_host = mysql_real_escape_string($_GET["single_host"]);

echo print_host($single_host);

vmps_footer();


?>
