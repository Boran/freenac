<?php
/**
 * config.inc.template
 *
 * Long description for file:
 * Setting for the WebGUI
 *
 */


date_default_timezone_set('CET');

// Web page contents
   $entityname='Swisscom';                # name of your company
   $head_title="FreeNAC :: $entityname ::";
   $head_right1="<a href='http://freenac.net' title='Visit the FreeNAC Internet Site'>FreeNAC website</a>";
   $head_right2="<a href='./'>Main Menu</a>";
   $head_left1="<a href='./index.html' title='Main Menu'><img src='./images/logo_small.png' border='0' /></a>";


  $sess_name='freenac';
  #$sess_time=3600;       // 3600=60min=1h
  $sess_time=60;    // testing: 1 min

  // Which method should be used for WebGUI authentication?
  // only ad_auth and anon_auth work at this stage.
  $ad_auth=false;       # apache does the domain login for us
  $anon_auth=true;     # No auth all aal, rely on Apache IP access control, or Firewall!

  // SQL & drupal auth: alpha stuff
  $sql_auth=false;       # lookup users from mysql
  $drupal_auth=false;    # lookup user password & uid from drupal
  $drupal_db="drupal";


// Enable XLS export? (requires the PEAR module Spreadsheet_Excel_Writer)
   $xls_output=false; // To enable set it to: true

?>
