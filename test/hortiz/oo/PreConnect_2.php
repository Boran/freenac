<?php

require_once "PreConnect.php";

class PreConnect_2 extends PreConnect 
{
   private $myresult=0;

   function getmyResult()			//Required by the interface	
   {
      return $this->myresult;
   }
 
   function Allow()			//Required by the interface
   {
      echo "PreConnect_2 is allowing {$this->request->mac}\n";
      return true;
   }

   function Deny()			//Required by the interface
   {
      echo "PreConnect_2 is denying {$this->request->mac}\n";
      return false;
   }

   function __construct($var=NULL)
   {
      parent::__construct();
      $this->myresult=$var;
   }
   
   function __get($key)
   {
      echo "Inside class \"Preconnect_1\"\n";
      return $this->request->$key;
   }

   function isPatchOK()
   {
      if ($this->getmyResult())
      {
         echo "Patch of {$this->request->mac} is up to date\n";
         return true;
      }
      else
      {
         echo "Patch of {$this->request->mac} IS OUT OF DATE\n";
         return false;
      }
   }

   function customCheck()
   {
      if ($this->getmyResult())
      {
         echo "Custom Check of {$this->request->mac} succeeded\n";
         return true;
      }
      else
      {
         echo "Custom Check of {$this->request->mac} FAILED\n";
         return false;
      }
   }

   
}
