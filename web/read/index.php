<?php

/**
 * index.php
 *
 * Long description for file:
 * Simple FreeNAC browser.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package             FreeNAC
 * @author              Patrick Bizeau
 * @copyright   2006 FreeNAC
 * @license             http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version             SVN: $Id$
 * @link                http://www.freenac.net
 *
 */


///////////////////////////////////////////
//     DO NOT EDIT BELOW THIS LINE       //
///////////////////////////////////////////
chdir(dirname(__FILE__));
set_include_path("./:../");

// include configuration
require_once('../config.inc');
// include functions
require_once('../funcs.inc');

echo print_header($entityname, $xls_output);

?>
