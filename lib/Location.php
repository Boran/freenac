<?php
/**
 * EndDevice.php
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
 * @link                        http://www.freenac.net
 */

class Location extends Common
{
   private $props = array();

   public function __construct($id)
   {
      $id = mysql_real_escape_string($id);
      if (is_numeric($id))
      {
         parent::__construct();
         $query=<<<EOF
SELECT 	l.id, 
	l.name, 
	b.id AS building_id, 
	b.name AS building_name 
	FROM location l 
	LEFT JOIN building b 
	ON l.building_id=b.id 
	WHERE l.id='$id';
EOF;
         $this->logger->debug($query,3);
         if ($temp = mysql_fetch_one($query))
         {
            $this->props=$temp; 
         }
      }
   }

   /**
   * Universal Accessor Method
   * We are redirecting all unresolved method calls to this handler,
   * so that we can emulate arbitraty accessor methods.
   * With this trick, the user can add new fields to the system tables
   * and will be able to access them in the policy as
   * $system->getDBFieldName() without haveing to change this class
   * @throws            If the db field does not exist, Log Error and Deny as default action
   * @return mixed      Property
   */
   public function __call($methodName, $parameters) 
   {
      # If methodname starts with get
      if (substr($methodName,0,3) == "get") 
      {
         $dbfieldname = substr($methodName,3);
         foreach(array_keys($this->props) as $key) 
         {
            if (strtolower($key) == strtolower($dbfieldname)) 
            {
               return $this->props[$key];
            }
         }
      }
      $this->logger->debug("Field $methodName doesn't exist",2);
   }

   /**
   * Return all properties assigned to this system. This method is here only for debugging purposes, please delete it after
   * @return array      All properties present
   */
   public function getAllProps()
   {
      return $this->props;
   }
}

?>
