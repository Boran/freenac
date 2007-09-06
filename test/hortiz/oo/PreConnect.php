<?php
/**
 * PreConnect.php
 *
 * Long description for file:
 *
 * This file defines the Settings class which is a Singleton
 * used to hold global variables stored in the configuration files and running script.
 *
 * CONSTRUCTOR SUMMARY:
 * *    private __construct(array $var_list, array $exclude_list);
 *              Register a VMPS request and logging facilities
 *
 * METHOD SUMMARY:
 * *	abstract public Allow():
 *		To be implemented by child classes
 *
 * *	abstract public Deny():
 *		To be implemented by child classes
 *
 * *    public final getResult():;
 *              Gives back the result property
 *
 * *	public final resetResult():
 *		Set the result property to NULL
 *
 * *	public final setResult($value):
 *		Set the result property to a certain value
 *		Parameters:
 *		$value		The value to set
 *
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package                     FreeNAC
 * @author                      Hector Ortiz (FreeNAC Core Team)
 * @copyright                   2007 FreeNAC
 * @license                     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version                     SVN: $Id$
*/

abstract class PreConnect				//This class should function as a template for
							//child classes
{
   static protected $request=NULL;			//Only one copy shared by all child classes
   static protected $result=NULL;	
   static protected $syslog=NULL;

   abstract public function Allow();			//for these functions, so we are sure that we have a kind 
   abstract public function Deny();			//of standard way of querying them or performing certain actions

   public function __construct()
   {
      $this->request=VMPSRequest::getInstance(); 	//Get the VMPS request
      $this->syslog=SysLogger::getInstance();		//Provide syslogging facilities
   }

   protected final function setResult($var=NULL) 	//Child classes don't need to provide an implementation for
   {						 	//this class
      $this->result=$var;
      return true;
   }

   protected final function resetResult()
   {
      $this->result=NULL;
      return true;
   }

   public final function getResult()
   {
      return $this->result;
   }

}

?>

