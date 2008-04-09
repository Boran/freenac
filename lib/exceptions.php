<?php

/**
 * exceptions.php
 * 
 * Definition of Exceptions and functions that throw those exceptions to be used by vmpsd_external and vmps_lastseen
 *
 * PHP version 5
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation.
 *
 * @package			FreeNAC
 * @author			Seiler Thomas (contributer)
 * @copyright			2007 FreeNAC
 * @license			http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 2
 * @version			SVN: $Id$
 * @link			http://www.freenac.net
 */
 
/**
 * This Exception may be thrown at any point in the decision process to
 * indicate that the current request should be denied 
 * This class extends the {@link Exception} class.
 */
class DenyException extends Exception
{
   # constructor  	
   function __construct($why) {
      parent::__construct("DENY: ".$why); 
   }
}

/**
 * This function is some syntactic sugar to throw a DenyException from the 
 * policy class
 * @throws	DenyException 
 */
function DENY($why='') {
   throw new DenyException($why);
}

/**
 * This Exception may be thrown at any point in the decision process to
 * indicate that the current request should be allowed into a given VLAN
 * This class extends the {@link Exception} class.
 */
class AllowException extends Exception
{
   # The vlan that we communicate to VMPSD 
   protected $decidedVlan;

   # Constructor to set this VLAN  	
   function __construct($vlan) {
      if ( ! isset($vlan) )
         throw new DenyException('AllowException called without a vlan');
      if ( is_integer($vlan) )
      {
         # Get the vlan mapping
         if ( ! $lookup = vlanId2Name($vlan) )
         {
            throw new DenyException("AllowException could not map vlan number $vlan to a name");
         }
         else
         {
            $this->decidedVlan = $lookup;
         }
      }
      else
      {
         #Do we have only spaces?
         $vlan = trim($vlan);
         if ( empty($vlan) )
            throw new DenyException('AllowException called without a vlan');
         $this->decidedVlan = $vlan;
      }
      parent::__construct("ALLOW {$this->decidedVlan}"); 
   }

   public function getDecidedVlan() {
      return $this->decidedVlan;
   }	
}

/**
 * This function is some syntactic sugar to throw an AllowException 
 * from the policy class. It also handles default vlan
 * @param int $vlan	Vlan ID of the VLAN we want to assign
 * @throws	AllowException
 */
function ALLOW($vlan) 
{
   throw new AllowException($vlan);
}
?>
