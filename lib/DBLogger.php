<?php

require_once "Logger.php";

final class DBLogger implements Logger
{
   const host='localhost';
   const user='inventwrite';
   const pass='PASSWORD2';
   const db='opennac';
   
   private $conn=NULL;
   private $stmt=NULL;
   private $who=1;
   private $host=NULL;
   private $priority='info';
   private $what=NULL;

   public function __construct()
   {
      if ($this->conn=new mysqli(self::host,self::user,self::pass,self::db))
      {
         $this->who=1;
         $this->host='localhost';
         $this->priority='info';
         if ($this->stmt=$this->conn->prepare('insert into naclog set who=?, host=?,priority=?, what=?'))
         {
            $this->stmt->bind_param('isss',$this->who,$this->host,$this->priority,$this->what);
         }
      }
   }
   
   public function log($message='')
   {
      if ($this->conn && $this->stmt && is_string($message) && (strlen($message)>0))
      {
         $this->what=$message;
         $this->stmt->execute();
         if ($this->stmt->affected_rows!=1)
         {
            #echo mysqli_error($this->conn);
            return false;
         }
         $this->what=NULL;
         return true;
      }
      return false;
   }

   public function changePriority($string)
   {
      if (is_string($string) && (strlen($string)>0))
      {
         switch($string)
         {
            case 'info':
            case 'err':
            case 'crit':
               $this->priority=$string;
               break;
         }
         return true;
      }
      else return false;
   }
}

?>

