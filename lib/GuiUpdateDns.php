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

  function __construct($title="Updating DNS", $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("__construct debug=$debug_level", 2);

    $this->module='GuiUpdateDns';              // identify module, in Webcommon
    $this->table='ip';               // identify SQL table, in Webcommon
    echo $this->print_header1();
    echo "<hr><p class='text18'>$title</p>";
  }

  protected function sanitize_name($name) {
    // make sure there are only DNS-ok characters
    // currently ugly/basic - need to be improved
    $name = ltrim(rtrim($name,' '),' ');
    $name = str_replace(' ', '-', $name);
    if ($position=strpos($name, ".")) { // strip away a domain name, if there is one
      $name = substr($name, 0, $position);
    }
    return($name);
  }


  public function ViewDNS()
  {
    $cmd="nslookup -type=axfr {$this->conf->dns_domain} {$this->conf->ddns_server}";
    $res=syscall($cmd);
    echo "<br>$cmd<pre class='logtext'>$res</pre>";
    echo "<hr><p>Go back to the <a href='{$this->calling_href}'>previous page</a></p>";
  }


  public function UpdateDnsAll()
  {
    $this->UpdateDns(TRUE);
  }


  public function UpdateDns($ddns_update_all=FALSE)
  {
    // ddns_update_all: True= all hosts, false= hosts with lastchange>lastupdate

    // settings TBD: query from $conf later
    #$nsupdate="nsupdate -v -t 10 ";    // -d = debug, 10 sec timeout, use tcp
    $nsupdate="nsupdate -d -v -t 10 ";    // -d = debug, 10 sec timeout, use tcp

    if ($_SESSION['nac_rights'] < 2)    // TBD: change to 99 for production
      throw new InsufficientRightsException('UpdateDns() ' .$_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

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
    $host_count=0;
    while (($host = $res->fetch_assoc()) !== NULL) {

         if (($host['name'] == 'unknown') || ($host['ip'] == '') || ($host['name'] == '')) { 
            echo "Skipping invalid host={$host['name']}, ip={$host['ip']} <br>";
            next;
         }
         $host_count++;
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

    if ($host_count==0) {
      echo "<br>There are no new changes, no DNS updates pending.";
      
    } else {
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
      echo "<br>The update request [$nsupdate] is:<pre class='text13'>$dns_update</pre>";

/*    $fp = popen($nsupdate,'w');
      if( ! $fp ){
        print "<h3>Error while sending to: $nsupdate</h3>\n";
        return false;
      }
      fwrite($fp,$dns_update);
      pclose($fp);
*/
 
      $des= array( 0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                   2 => array("pipe", "w")   // stderr
      );
      $fp = proc_open($nsupdate, $des, $pipes);
      if (is_resource($fp) ) {
        fwrite($pipes[0], $dns_update);    // send dns updates
        fclose($pipes[0]);
        #fflush($pipes[0]);
        #usleep(100);  // # wait till something happens

        $ret='';
        #$ret= stream_get_contents($pipes[1]);  // read the answer
        while (!feof($pipes[1]))   // read the answer
          $ret.=fgets($pipes[1], 1024);

        while (!feof($pipes[2]))   // read the answer
          $ret.=fgets($pipes[2], 1024);

        fclose($pipes[1]);
        fclose($pipes[2]);
        $ret2=proc_close($fp);
      } 

      echo "<br>The answer is:<pre class='logtext'>$ret</pre>";
      echo "<br>Process answer =$ret2 (0 is success)";
      #echo "<p>Update completed</p>";
    }

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
