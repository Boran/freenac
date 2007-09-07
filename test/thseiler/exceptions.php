<?php

/*
 * Exception
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Seiler Thomas (contributer)
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */
 
 

	


/* This Exception may be trown at any point in the desicion process to
 * indicate that the current request should be denied 
 */
class DenyException extends Exception
{
	/* constructor */ 	
 	function __construct() {
 		parent::__construct("DENY"); 
 	}
}

/* This function is some syntactic sugar to throw a DenyException from the 
 * policy class 
 */
function DENY() {
	throw new DenyException;
}



/* This Exception may be trown at any point in the desicion process to
 * indicate that the current request should be denied and furthermore, the
 * current system should be killed
 */
class KillException extends Exception
{
	/* constructor */ 	
 	function __construct() {
 		parent::__construct("KILL"); 
 	}
}

/* This function is some syntactic sugar to throw a DenyException from the 
 * policy class 
 */
function KILL() {
	throw new KillException;
}





/* This Exception may be trown at any point in the desicion process to
 * indicate that the current request should be allowed into a given VLAN
 */
class AllowException extends Exception
{
 	/* The vlan that we communicate to VMPSD */
 	protected $decidedVlan;

	/* constructor to set this VLAN */ 	
 	function __construct($vlan) {
 		$this->decidedVlan = $vlan;
 		parent::__construct("ALLOW ".$vlan); 
 	}

 	public function getDecidedVlan() {
		return $decidedVlan;
	}	
}

/* This function is some syntactic sugar to throw an AllowException 
 * from the policy class. It also handles default vlan
 */
function ALLOW($vlan = -1) {
	if ($vlan == -1) {
		// Todo: get the default vlan logic straight
		$vlan = $defaultvlan;
	}
	throw new AllowException($vlan);
}
