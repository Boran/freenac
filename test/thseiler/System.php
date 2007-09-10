<?php

/*
 * System Class
 *
 * This class represents a row in the systems table in the database.
 * In the current version, the host is identified by its mac address.
 * 
 * 
 *
 * This is the core of the logging / debugging and regression testing.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Seiler Thomas (contributer)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */


class System {
	private $mac;
	private $db_row = array();
	private $settings;
	

	/* The constructor takes the mac address of the system and creates 
	 * and instance representing that particular system.
	 * Access is read-only.
	 */
	public function __construct($mac) {
	  	
		/* Normalise mac address format by removing spaces, dashes, dots
		 * and collons, and by converting to lower case.
		 */
  	  	$mac = strtolower(preg_replace('/-|\.|\s|\:/', '', $mac));
	
		/* sanity check - Is this a valid MAC address ??? */
  	  	if (!preg_match("/^[0-9a-f]{12}$/",$mac)) DENY();
  	  	if ($mac === '000000000000') DENY();
	
	  	/* Rewrite mac address according to Cisco convention, XXXX.XXXX.XXXX */ 
	  	$this->mac="$mac[0]$mac[1]$mac[2]$mac[3].$mac[4]$mac[5]$mac[6]$mac[7].$mac[8]$mac[9]$mac[10]$mac[11]";	  	
	  	  
	  	/* query system table */
	  	
	  	// Todo: Update Query with SQL joins to user table and vlan table
	  	
	  	$sql_query="SELECT * FROM systems WHERE mac='" . $this->mac."';";
		//Todo fill the db_row array
                $this->db_row=mysql_fetch_one($sql_query);
		$this->settings=Settings::getInstance();
	}



/* Policy Checks ------------------------------------------------------------ */

	/* isExpired
	 * Check that the system is not yet expired
	 * Expiry date like 0000-00-00 00:00:00 is treated as never expire
	 */
	public function isExpired() {
		/* check if expiry checks are enabled */
		if ($this->settings->check_for_expired) {
			/* get systems expiry date*/
			$expiry = $this->db_row['expiry'];
			
			if ($expiry)
			{
				/* 0000-00-00 00:00:00 means no expiry */
				if(strcmp(trim($expiry),"0000-00-00 00:00:00")==0) 
				{
					return false;
				}
		
            	$timestamp=date('Y-m-d H:i:s');
            	$time=time_diff($timestamp,$expiry);
            	if ($time<0) 
            	{
            		return true;
           		}
           	}
		} 
		/* default is not to expire */
        	return false;
	}

	/* system->isVM()    Is this system a Virtual Machine ?
	 * It is, if the ventor string associated to the first 3 bytes of the mac
	 * contains "vmware" or "parallels"
	 */
	public function isVM() {
		/* check if VM checks are enabled */
		if ($conf->vm_lan_like_host) {
			if (stristr($this->getVendor(),"vmware")) return true;    // the original
			if (stristr($this->getVendor(),"parallels")) return true; // Mac VMWare-alike
			// Todo: Check with Sean if its okay to think that all Microsoft OUIs are
			// VirtualPCs ?
			//if (stristr(this->getVendor(),"microsoft")) return true; // VirtualPC
		}
		return false; 
	}
	
	
	
/* Information about system ------------------------------------------------- */
	
	/* Return the vendor name associated to first 3 bytes of the mac address 
	 */
	public function getVendor() {
		/* Todo: Implement a vendor Cache in an arry 
		 * to save an sql statement per request ;-) 
		 */
		 
		 return "vendorXYZ";
	}
	
	/* Return the Name of the systems default VLAN */
	public function getVlanName() {
		// Todo:
	}

	/* Universal Accessor Method
	 * We are redirecting all unresolved method calls to this handler, 
	 * so that we can emulate arbitraty accessor methods.
	 * With this trick, the user can add new fields to the system tables
	 * and will be able to access them in the policy as
	 * $system->getDBFieldName() without haveing to change this class
	 */
	/*public function __call($methodName, $parameters) {
		/* If methodname starts with get */
	/*	if (substr($methodName,0,3) == "get") {
			$dbfieldname = substr($methodName,3);
			foreach(array_keys($this->db_row) as $key) {
				if (strtolower($key) == strtolower($dbfieldname)) {
					return $this->db_row[$key];
				}
			}
		}
		/* If the db field does not exists, Log Error */
		// Todo: Log
		
		/* Then DENY as default action */
	/*	DENY();
	}*/

	public function __get($key)                                                  //Get the value of one var
   	{
      		return $this->props[$key];
   	}

	public function getAllProps()
	{
		return $this->db_row;
	}
}

?>
