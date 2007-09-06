<?php

class system {

	private $mac;

	public function __construct($mac) {
		$this->mac = $mac;
	}

	/* 
	 *
	 */
	public function getMAC() {

	}

	/* system->isVM()    Is this system a Virtual Machine ?
	 * It is, if the ventor string associated to the first 3 bytes of the mac
	 * contains "vmware" or "parallels" */
	public function isVM() {
		if (stristr($this->getVendor(),"vmware")) return true;    // the original
		if (stristr($this->getVendor(),"parallels")) return true; // Mac VMWare-alike
		// Todo: Check with Sean if its okay to think that all Microsoft OUIs are
		// VirtualPCs ?
		//if (stristr(this->getVendor(),"microsoft")) return true; // VirtualPC
		
		return false; 
	}
	
	
	/* Return the vendor name associated to first 3 bytes of the mac address */
	public function getVendor() {
		/* Todo: Implement a vendor Cache in an arry 
		 * to save an sql statement per request ;-) 
		 */
		 
		 return "vendorXYZ";
	}

}

?>