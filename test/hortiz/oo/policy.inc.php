<?php

#Policy file


function handle_request()
{
   $pre1=new PreConnect_1(1);
   $pre2=new PreConnect_2(1);

   //We don't want neither 'killed' nor 'expired' systems on the network
   if ($pre1->isKilled() ||  $pre1->isExpired())
   {
      $pre1->Deny();
   }
   //Allow devices whose patches are up to date and that pass a custom check
   else if ($pre2->isPatchOK() && $pre2->customCheck())
   {
      $pre2->Allow();
   }
   //Otherwise, simply deny
   else
   {
      $pre1->Deny();
   }
}

?>
