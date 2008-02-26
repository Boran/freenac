<?php
/**
 * 
 * GuiLogtail.php.php
 *
 * Show log files in the web GUI
 *
 * @package     FreeNAC
 * @author      Sean Boran/Thomas Dagonnier (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiLogtail extends WebCommon {
  // See also WebCommon and Common


  function __construct($filename, $length=10, $pattern="vmpsd|postconnect")
  {
    parent::__construct();     // See also WebCommon and Common
    $this->logger->setDebugLevel(3);


    if ($ad_auth===true)
    {
       if ($_SESSION['nac_rights']>=1) {
         echo $this->logtail($filename, $length, $pattern);
       }
       else {
         echo "<h1>ACCESS DENIED</h1>";
         echo "<p>Please verify the nac_rights for username: <" .$_SESSION['username']
          .">.</p>";
         $this->logger->logit("ACCESS DENIED: verify the nac_rights for username: <" .$_SESSION['username'] .">.</p>");
       }
    }
    else {       // TBD: test if ad_auth=false
       echo $this->logtail($filename, $length, $pattern);
    }

    echo $this->print_footer();

  }

  function logtail($filename, $length=10, $pattern='')
  {
        $logfile = fopen($filename,'r');
        // TBD: catch error if file cannot be read, or non existant.

        if ( strlen($pattern)>0 ) { 
          $cmd="/usr/bin/tail -n $length $filename | egrep \"$pattern\"";
        }
        else {
          $cmd="/usr/bin/tail -n $length $filename ";
        }
        $this->debug($cmd, 3);
        exec($cmd, $logtext, $error);

        $text = "<h2>The last $length lines in the log $filename :</h2>";
        if ($error){
            $text .= "Tail Error: ($cmd) $error\n";
            $this->logger->logit("Tail Error: ($cmd) $error");
        }
        else {
            $text .= "\n<p>\n<pre>\n";
            foreach ($logtext as $logline) {
                $text .= $logline."\n";
            };
            $text .= "</pre>";
        };
        return($text);
  }


  /**
   * Generic query report
   */
  public function query($q) 
  {
    $conn=$this->getConnection(); //  make sure we have a DB connection
    $output="<table id='t2' width='1000' border='0' class='text14'>";

    try {
      #$limit=$this->real_escape_string1($limit, $conn);
      $rowcount=0;
      #$q.=" LIMIT 1"; 
      $this->debug("query() $q", 2);

      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException($conn->error);

      // Title: Grab the list of field names
      $fields=$res->fetch_fields();
      while (($row = $res->fetch_assoc()) !== NULL) {
        $rowcount++;
        foreach ($fields as $field) {
          $fname=$field->name;
          $output.="<tr><td>$fname</td><td>{$row[$fname]}</td></tr>";
        } 
      }

      $output.="<tr><td>Date printed</td><td>" .date("F j, Y, g:i a") ."</td></tr>";

      // Inform the user if no data was returned
      if ( ($rowcount)==0 ) {
        $output.= "<br/><tr><td colspan='4' align='center' class='text16red'>The report is empty</td></tr><tr></tr><br/>";

      }
      $res->close();

    } catch (Exception $e) {
      #if ($in_db_conn === NULL and isset($conn))
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    // close table, send back text
    $output.="</table>";
    return($output);
  }



} // class

?>
