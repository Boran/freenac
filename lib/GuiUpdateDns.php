<?php
/**
 * GuiUpdateDns.php
 *
 * Long description for file:
 * Using ip.address and systems.name from the FreeNAC 'ip' DB, generate 
 * a list of dynamic DNS updates.
 * The DNS update commands are written to $tmp_file, once the file has been 
 * written, the dns_update flag is reset for each field.
 * TODO : aliases/CNAMES, then HINFO LOC SVR (separate table)
 *
 * Inputs: ip.address, systems.name
 *   $conf->: ddns_server,  dns_domain, ddns_ttl
 * Options:  
 *   $ddns_level: 0 = all hosts, 1 = hosts with dns_update bit 1 is set
 *  Status field
 *    0 = free
 *    1 = used: updated by Dyn dns [THIS is the only value we check]
 *    -1 =  special address (network address, broadcast)
 *    2 =  fixed
 *    3 = reserved
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran/Thomas Dagonnier (FreeNAC Core Team)
 * @copyright			2008 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 *
 */

class GuiUpdateDns extends WebCommon
{
  private $id, $action;      // See also WebCommon and Common

  function __construct($debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("__construct id=$id, debug=$debug_level, action=$action", 2);

    echo "<hr><h3 text-align='centre'>Updating DNS </h3>";
  }

  protected function sanitize_name($name) {
        // make sure there are only DNS-ok characters
        // currently ugly/basic - need to be improved
        $name =  ltrim(rtrim($name,' '),' ');
        $name = str_replace(' ', '-', $name);
        return($name);
  }

  public function UpdateDns()
  {
    if ($_SESSION['nac_rights'] < 2)    // TBD: change to 99 for production
      throw new InsufficientRightsException('UpdateDns() ' .$_SESSION['nac_rights']);


    $conn=$this->getConnection();     //  make sure we have a DB connection

    // settings TBD: query from $conf later
    $nsupdate="nsupdate -v -t 10 ";    // -d = debug, 10 sec timeout, use tcp
    $ddns_update_all=TRUE; /* True= all hosts, false= hosts with lastchange>lastupdate */

    /** A Records **/
    $query = "SELECT ip.id as id, INET_NTOA(ip.address) as ip, systems.name as name FROM ip LEFT JOIN systems ON ip.system = systems.id WHERE ip.status=1 AND ip.system != 0 ";
      if ($ddns_update_all===FALSE) {
           $query.=" AND (ip.lastupdate < ip.lastchange)";
      }
      $this->debug($query, 3);
      $res = $conn->query($query);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

     $dns_ina='';      // collect the update commands
     while (($host = $res->fetch_assoc()) !== NULL) {

         if (($host['name'] == 'unknown') || ($host['ip'] == '') || ($host['name'] == '')) { 
            echo "Skipping invalid host={$host['name']}, ip={$host['ip']} <br>";
            next;
         }
         echo "Analysing host={$host['name']}, ip={$host['ip']} <br>";

	 $dns_name = $this->sanitize_name($host['name']) ."." .$this->conf->dns_domain .".";
	    #$dns_ip = $host['ip'];
            $dns_ina .= 'update delete '.$dns_name."\t A\r\n";
	    $dns_ina .= 'update add ' .$dns_name ."\t" .$this->conf->ddns_ttl 
              .' A ' .$host['ip'] ."\r\n";

         // clear update flag: TBD: we don't really know if the update will work..
         $upd_clear = "UPDATE ip SET lastupdate=NOW() WHERE id=".$host['id'];
            $this->debug($query, 3);
            $res2 = $conn->query($upd_clear);
            if ($res2 === FALSE)
              throw new DatabaseErrorException($conn->error);
      };

      $dns_update = "server {$this->conf->ddns_server}\r\n";
      //$dns_update .= "zone $dns_domain\r\n"; Zone must be in name
      $dns_update .= $dns_ina;
      #if ($logger->getDebugLevel()>1) {
        // request verbose answer, i.e. NOERROR below:
        # Outgoing update query:
        # ;; ->>HEADER<<- opcode: UPDATE, status: NOERROR, id:  29828
        # ;; flags: qr ra ; ZONE: 0, PREREQ: 0, UPDATE: 0, ADDITIONAL: 0
        $dns_update .= "answer\n";    
      #}
      $dns_update .= "send\n";
      echo "<br>The update request is:<pre>$dns_update</pre>";

    //if ($this->logger->getDebugLevel()=0) {
      $fp = popen("$nsupdate",'w');
      if( ! $fp ){
        print "<h3>Error while sending to: $nsupdate</h3>\n";
        return false;
      }
      fwrite($fp,$dns_update);
      pclose($fp);
      echo "<p>Update completed</p>";

    //} else {
    //  print "<h3>Debug only: </h3>\n";
    //  echo "<p>no commands actually sent to DNS.</p>";
    //}
    echo "<hr><p>Go back to the <a href='{$this->calling_href}'>previous page</a></p>";
  }

}   // class


/////////// main() should never get here .. ///////////////////////////////////////
if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger=Logger::getInstance();
  $logger->debug("Edit__:action:". $_POST['action'], 1);
}

if ( isset($_POST['submit']) ) {             // form submit, check fields
## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);
  $logger->debug("Edit__ main -submit");
  #echo handle_submit();

} else {
  # Do nothing, we've been included.
}


?>
