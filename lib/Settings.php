<?php
/**
 * Settings.php
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
 *
 */

/**
 * Define the Settings class which is a Singleton
 * used to hold global variables stored in the configuration files and running script.
*/
class Settings
{
   private $props=array();
   private static $instance;
   
   /**
   * Set our internal array to contain all variabled defined in the config table
   */
   private function __construct()
   {
      db_connect();
      $query="select * from config";						# Query 
      $res=mysql_query($query);
      if ($res)
      {
         while ($result=mysql_fetch_array($res, MYSQL_ASSOC))			# Iterate over all information found
         {
            switch ($result['type'])						# Now doing casts according to the field 'type'
            {									# And store them in an internal array
               case 'integer':$temp[$result['name']]=(integer)$result['value'];
                  break;
               case 'boolean':
                  {
                     if (strcasecmp($result['value'],'true')==0)
                        $temp[$result['name']]=(boolean)true;
                     else
                        $temp[$result['name']]=(boolean)false;
                  }
                  break;
               case 'float':$temp[$result['name']]=(float)$result['value'];
                  break;
               case 'double':$temp[$result['name']]=(double)$result['value'];
                  break;
               case 'string':$temp[$result['name']]=(string)$result['value'];
                  break;
               case 'array':$temp[$result['name']][]=$result['value'];
                  break;
            }
         }
         $this->props=$temp;							# Et voila, copy it to our internal array
      }
      else
         return;								# Nothing to return
   }

   /**
   * Get instance of this class
   * @return object     Current instance
   */
   public static function getInstance()
   {
      if (empty(self::$instance))						# Is there an instance of this class?
      {
         self::$instance=new Settings();					# No, then create it
      }
      return self::$instance;							# Yes, return it
   }

   /**
   * Get the value of one property if it exists
   * @param mixed $key          Property to lookup
   * @return mixed              The value of the wanted property, or false if such a property doesn't exist
   */
   public function __get($key)							# Get the value of one var
   {
      return $this->props[$key];
   }

   /**
   * Return all properties defined. This method is here only for debugging purposes, please delete it after
   * @return array      All properties present
   */
   public function getAllProps()						# Get our inner array
   {
      return $this->props;
   }
}

?>
