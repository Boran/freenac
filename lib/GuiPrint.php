<?php
/**
 * 
 * GuiPrint.php
 *
 * Generic class to show one html page with one record,
 * based on a query. It is normally called from a control script
 * that instaniates GuiList1 first.
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiPrint extends WebCommon {
  // See also WebCommon and Common


  function __construct($rep_name='')
  {
    parent::__construct();     // See also WebCommon and Common
    $this->logger->setDebugLevel(1);   // set to 3 for debugging
    $this->debug("__construct " .$_SESSION['login_data'] .":$rep_name:", 1);

    // Show Webpage start, is the constructor the right place?
    //echo print_header(false);  //false=no links

    $txt=<<<TXT
<div style='text-align: left;' class='text18'>
  <p>{$rep_name}
</div><br/>
TXT;
    echo $txt;
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
          #$output.="<tr><td>$fname</td><td>{$row[$fname]}</td></tr>";
          $output.="<tr><td>$fname</td><td>" .$this->htmlescape($row[$fname]) ."</td></tr>";
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

  function print_footer()            // Override 
  {
    $txt=<<<TXT
<A HREF='javascript:javascript:history.go(-1)'< Back</A>        
<A HREF='javascript:window.print()'>Print</A>
TXT;

  // TBD: why does the second part not work?
  #echo "<A HREF='javascript:javascript:history.go(-1)'<<< back</A><SCRIPT TYPE='text/javascript' <!-- var gb = new backlink(); gb.type='button'; gb.form=false; gb.text = 'Back'; gb.write(); //--></SCRIPT>\n";

    return($txt);
  }


} // class

?>
