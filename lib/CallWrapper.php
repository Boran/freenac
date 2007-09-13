<?php

/*
 * CallWrapper Class
 *
 * This class creates proxy-objects, that wrap other objects, and log:
 *  - The methods that were called
 *  - The parameters of the calls
 *  - The return values.
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
 
 
 
class CallWrapper extends Common {
	protected $object;
	
	// we save a reference to the real object
	public function __construct($object) {
		parent::__construct();
		$this->object = $object;
	}
	
	// here we log the return value of each method that is called in policy.inc.php
	public function __call($methodName, $parameters) {
		$value = call_user_func_array(array( $this->object, $methodName ), $parameters);
		$this->syslogger->log(get_class($this->object)."->".$methodName."(".join(",",$parameters).") = ".$value ."\n"); 
		return $value;
	}
}
 
?>
