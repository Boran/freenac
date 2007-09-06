<?php

require_once "PreConnect.php";

class PreConnect_1 extends PreConnect  
{
   private $myresult=0;

   function getmyResult()		//Required by the parent class	
   {
      return $this->myresult;
   }
 
   function Allow()		//Required by the parent class
   {
      echo "PreConnect_1 is allowing {$this->request->mac}\n";
      return true;
   }

   function Deny()		//Required by the parent class
   {
      echo "PreConnect_1 is denying {$this->request->mac}\n";
      return false;
   }

   function __construct($var=NULL)
   {
      parent::__construct();	//Shouln't this be automatically done??
      $this->myresult=$var;
   }
   
   function __get($key)
   {
      echo "Inside class \"Preconnect_1\"\n";
      return $this->request->$key;
   }

   function isKilled()
   {
      if ($this->getmyResult())
      {
         echo "Device {$this->request->mac} not killed\n";
         $this->syslog->debug(2,"DEVICE NOT KILLED");
         return false;
      }
      else
      {
         echo "Device {$this->request->mac} KILLED\n";
         return true;
      }
   }

   function isExpired()
   {
      if ($this->getmyResult())
      {
         echo "Device {$this->request->mac} still valid\n";
         return false;
      }
      else
      {
         echo "Device {$this->request->mac} has EXPIRED\n";
         return true;
      }
   }

   
}
