<?php
/**
 * CallWrapper.php
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Sean Boran (FreeNAC Core Team)
 * @author			Seiler Thomas (contributer)
 * @copyright			2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */
 
/**
 * This class creates proxy-objects, that wrap other objects, and log:
 *  - The methods that were called
 *  - The parameters of the calls
 *  - The return values.
 *
 * This is the core of the logging / debugging and regression testing.
 * This class extends the {@link Common} class.
*/ 
class CallWrapper extends Common {
   protected $object;
	
   /**
   * We save a reference to the real object in the constructor
   */
   public function __construct($object) {
      parent::__construct();
      $this->object = $object;
   }
	
   /**
   * Here we log the return value of each method that is called in policy.inc.php
   * @param mixed $methodName 	The function to call and log
   * @param mixed $parameters 	Parameters passed to the called function
   * @return mixed 		The return value of the called function
   */
   public function __call($methodName, $parameters) {
      $value = call_user_func_array(array( $this->object, $methodName ), $parameters);
      $this->logger->debug(get_class($this->object)."->".$methodName."(".join(",",$parameters).") = ".$value ."\n",2); 
      return $value;
   }
}
 
?>
