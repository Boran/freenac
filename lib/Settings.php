<?php
/**
 * Settings.php
 *
 * Long description for file:
 *
 * This file defines the Settings class which is a Singleton
 * used to hold global variables stored in the configuration files and running script.
 *
 * CONSTRUCTOR SUMMARY:
 * *	private __construct(array $var_list, array $exclude_list);
 *		Compute the difference of $var_list and $exclude_list and store it in an internal array.
 *	PARAMETERS: 
 *		$var_list : 	An array containing the list of variables defined.
 *		$exclude_list :	The list of variables we want to exclude from our final array.	
 *
 * INTERCEPTOR SUMMARY:
 * *	public __set($key,$value);
 *		Set the key $key with the value $val stored in our internal array. If $key doesn't existe, create it.
 *	PARAMETERS:
 *		$key :		The key in our internal array that we want to set.
 *		$value :	The value we want to assign to this key.
 *
 * *	public __get($key);
 *		Get the value of key $key from our internal array.
 *	PARAMETERS:
 *		$key : 		The key we want to retrieve from our internal array.
 *
 * METHOD SUMMARY: 
 * *	public static getInstance(array $vars=array(),array $list=array('GLOBALS','^_','^HTTP'));
 *		Create an instance of the Settings class if one hasn't been defined yet and return the instance to the calling code.
 *	PARAMETERS:
 *		array $vars:	Array containing the list of vars defined. Ideally, the result of get_defined_vars() should be passed on.
 *				If no $vars has been passed, an empty array will be used.
 *		array $list:	Array containing the list of vars we want to exclude.
 *				If no $list has been passed, a default list, which excludes PHP defined vars, is used.	
 * 
 * * 	public getAllProperties();
 *		Returns the internal array which contains the vars in the configuration files.
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

class Settings
{
   private $props=array();
   private static $instance;
   
   /*private function __construct($var_list,$exclude_list)
   {
      //Returns an array containing all defined variables without the $exclude_list vars
      //$exclude_list supports REGEX
      //$var_list is the list of vars returned by get_defined_vars
      if ((empty($var_list))&&(empty($exclude_list)))	
         return;
      else if ((!empty($exclude_list))&&(empty($var_list)))
         return;
      else if ((empty($exclude_list))&&(!empty($var_list)))
      {
         $this->props=$var_list;
         return;
      }
      else
      {
         $elements_to_exclude=count($exclude_list);           			//How many items we have in our exclude_list?
         foreach($var_list as $key => $value)                 			//Go over each key from the var_list
         {
            for ($i=0;$i<$elements_to_exclude;$i++)           			//And compare it with each element of our exclude_list
            {
               if (ereg($exclude_list[$i],$key))              			//If the REGEX matches
                  array_push($exclude_list,$key);             			//Add that key to our exclude_list
            }
         }
      
         $temp1=array_values(array_diff(array_keys($var_list),$exclude_list));	//Get the difference from our exclude_list and var_list
                                                                              	//And store it in $temp1
         $temp2=array();
      
         while (list($key,$value) = each($temp1))                                //Get the keys, values
         {
            global $$value;
            $temp2[$value]=$$value;                                              //And store them in $temp2
         }
         $this->props=$temp2;
      }
   }*/

   private function __construct()
   {
      // Returns an array containing all variables defined in the config table
      db_connect();
      $query="select * from config";						//Query
      $res=mysql_query($query);
      if ($res)
      {
         while ($result=mysql_fetch_array($res, MYSQL_ASSOC))			
         {
            switch ($result['type'])						//Now doing casts
            {
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
         $this->props=$temp;							//Et voila, return it
      }
      else
         return;								//Nothing to return
   }

   public static function getInstance()
   {
      if (empty(self::$instance))						//Is there an instance of this class?
      {
         self::$instance=new Settings();					//No, then create it
      }
      return self::$instance;							//Yes
   }

   public function __set($key,$val)						//Set a var in our array
   {
      $this->props[$key]=$val;
   }

   public function __get($key)							//Get the value of one var
   {
      return $this->props[$key];
   }

   public function getAllProperties()						//Get our inner array
   {
      return $this->props;
   }
}

?>
