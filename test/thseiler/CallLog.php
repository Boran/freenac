<?php

/*
 * Freenac LogProxy
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Seiler Thomas
 * @copyright		2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */
 
class CallLog {
	protected $object;
	
	// we save a reference to the real object
	public function __construct($object) {
		$this->object = $object;
	}
	
	// here we log the return value of each method that is called in policy.inc.php
	public function __call($methodName, $parameters) {
		$value = call_user_func_array(array( $this->object, $methodName ), $parameters);
		echo get_class($this->object)."->".$methodName."(".join(",",$parameters).") = ".$value ."\n"; 
		return $value;
	}
};
 
?>