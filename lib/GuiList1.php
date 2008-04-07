<?php
/**
 * 
 * GuiList1.php
 *
 * Long description for file:
 * Class to Display a generic Query, with sorting and active buttons
 * USAGE:
 *   See also GuiEditDevice_control.php, which manages the Buttons and POST/Submits.
 *   The Constructor create the basic empty report with tailes. The Query()
 *   builts the SQL statement, executes and sends back results.
 *   Features common to other WebGUI parts are outsourced to the parent class WebCommon
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiList1 extends WebCommon 
{
  private $dynamic;           // See also WebCommon and Common


  function __construct($rep_name='', $dynamic=false , $debuglevel=1)
  {
    parent::__construct();     // See also WebCommon and Common
    $this->logger->setDebugLevel($debuglevel);  // 3 for max debugging
    $this->dynamic=$dynamic;
    $this->debug(" __construct() $rep_name, debug=" .$debuglevel, 1);
    echo "<div id='GuiList1Title'><p>{$rep_name}</div>";
  }


  /*
   * Setup a 'order by' form, that calls back the script that instantiated us
   */
  private function print_report_menu($limit, $order, $fields, $searchby, $searchstring) 
  {
    // Only create the sorting menu if this is a 'dynamic report'
    if (! $this->dynamic ) return;

    $this->debug("print_report_menu limit=$limit, order=$order, searchby=$searchby, searchstring=$searchstring" ,2);

    // Use GET rather than post?
    //<form name="GuiList1" action="{$this->calling_script}" method="GET">
    $output=<<<EOF
    \n<form name="GuiList1" action="{$this->calling_script}" method="post">
    <div id="GuiList1form1">
EOF;
       // Search fields
       $text=<<<EOF
    <ul>
       <li>Search:<input type='text' value='$searchstring' 
           name='searchstring' size='19' maxlength='20' /></li>
       <li>Search Field:<SELECT NAME=searchby>\n
EOF;
       $output.= $text;
       foreach ($fields as $field) {
         if ($searchby=="$field->table.$field->orgname") {
           $output.="<OPTION SELECTED VALUE='" .$field->table ."." .$field->orgname . "'>" . $field->name ."</OPTION>\n";
         } else {
           $output.="<OPTION VALUE='"          .$field->table ."." .$field->orgname . "'>" . $field->name ."</OPTION>\n";
         }
       }

       // Change button
       $text=<<<EOF
       </SELECT> </li>
       <li><input class="bluebox" type='submit' name='change' value='Change' /></li>
       </ul>
    <ul>
       <li>Max. records:<input type='text' value='$limit' name='sortlimit' size='11' maxlength='20' /></li>
    </ul>
       </div></form>
EOF;
       //<li><input class="bluebox" type='submit' name='change' value='Change'/></li>
       $output.= $text;
       return($output);
  }



  /**
   * Generic query report
   * Parameters: 
   *   query($q, $limit, $order, $action_menu, $action_fieldname, $idx_name, $searchstring, $searchby, $action_confirm)
   *   The (my)SQL query is built as:
   *      $q WHERE $searchby LIKE '%$searchstring%' ORDER BY $order $order_dir LIMIT $limit
   *   action_fieldname is the Title for the index column called idx_name
   *   Buttons in column 1:
   *     action_menu/action_confirm= array of Action button names & confirmation dialogs
   */
  public function query($q, $limit=0, $order='', $action_menu, 
    $action_fieldname='', $idx_name, $searchstring='', $searchby='',
    $action_confirm=array(''), $order_dir='DESC') 
  {
    $conn=$this->getConnection();     //  make sure we have a DB connection
    $_SESSION['report1_query']=$q;    // save for Report2, for re-use
    
    $output="<table id='GuiList1Table'>"; // note the CSS id: do your formatting in CSS!

    try {
      $rowcount=0;
      $searchby=preg_replace('/^\./', '', $searchby);  // kill leading dot, if any
      $order   =preg_replace('/^\./', '', $order);    
      if ((strlen($searchby)>0) && (strlen($searchstring)>0) ) {
        $searchby=$this->sqlescape($searchby);
        $searchstring=$this->sqlescape($searchstring);   // strip/escape unwanted characters
        $q.=" WHERE $searchby LIKE '%$searchstring%' "; 
      } 

      $order=$this->sqlescape($order);
      $order_dir=$this->sqlescape($order_dir);
      $limit=$this->sqlescape($limit);
      $this->debug("GuiList1.php->query() limit=$limit, order=$order searchby=$searchby, searchstring=$searchstring", 2);

      if (strlen($order)>0) $q.=" ORDER BY $order $order_dir"; 
      if ($limit>0)         $q.=" LIMIT $limit"; 

      $this->debug("$q", 3);

      $res = $conn->query($q);
      if ($res === FALSE)
        throw new DatabaseErrorException("Query=$q;" .$conn->error);

      if ( is_array($action_menu) )
        $output.="<th>Action</th>";         // field for menu icons

      // Title: Grab the list of field names
      $new_order_dir = $order_dir=='DESC' ? 'ASC' : 'DESC';   // next click reverse order
      $fields=$res->fetch_fields();
      foreach ($fields as $field) {
        # Simple titles:
        #$output.="<th>". $field->name . "</th>";
        # other attributes: table, max_length, flags, type
        // Titles with clickable sort links:
        $output.=<<<TXT
<th  class='light'>
<a href="{$this->calling_script}?sortlimit={$limit}&sortby={$field->table}.{$field->orgname}&order_dir={$new_order_dir}&searchstring=$searchstring&searchby=$searchby&change=Change"  title="Sort by this column">$field->name</a>
</th>
TXT;
      } 
      $output.= "<tr>";

      // detail lines
      $shade=false;
      while (($row = $res->fetch_assoc()) !== NULL) {
        $rowcount++;
        //$this->logit("save: " .$row["$action_fieldname"]); 
        $indices[]=$row["$action_fieldname"];   // remember index values
        $output.= ($shade) ? "<tr class='light'>" : "<tr class='dark'>";

        if ( is_array($action_menu) ) {
          if ( strlen($action_fieldname) >0 ) // add a field for menu icons?
            $row_id=$row[$action_fieldname];

          #Old, URL method
          #$output.="<td><a href='{$this->calling_script}?action={$action}&action_fieldname={$action_fieldname}&action_idx={$row_id}'>{$action}</a></td>";
          #$output.="<td>{$action_menu}</td>";      

          $output.='<td>';
          foreach ($action_menu as $key => $menu_item) {
            
            // add a Confirmation dialog to this action button?
            if (isset($action_confirm[$key]) && strlen($action_confirm[$key]) > 0)   // Get the confirmation dialog text.
              $confirm="onClick=\"javascript:return confirm('" .$action_confirm[$key] ."')\"";
            else
              $confirm='';
 
            $output.=<<<EOF
 <form name='Action1' action='{$this->calling_script}' method='post'>
     <input type=hidden name='action_idxname'   value='{$idx_name}' />
     <input type=hidden name='action_fieldname' value='{$action_fieldname}' />
     <input type=hidden name='action_idx'       value='{$row_id}' />
     <input class="greybox" type='submit' name='action' value='{$menu_item}' $confirm/>
 </form>
EOF;
          } 
          $output.='</td>';
        }

        foreach ($fields as $field) {
          $fname=$field->name;
          $output.= "<td align='center'>" . $row[$fname] . "</td>";
        } 
        $output.= "</tr>";
        $shade = !$shade;  // toggle shading for the next line
      }                    // while row

      if (! empty($indices))
        $_SESSION['report1_indices']=serialize($indices);  // remember index values

      // Inform the user if no data was returned
      if ( ($rowcount)==0 ) {
        #$output.= "<br/><tr><td colspan='4' align='center' class='text16red'>The report is empty</td></tr><tr></tr><br/>";
        $output= "<table id='GuiList1Table'><tr><td colspan='4' align='center' class='text16red'>The report is empty</td></tr><br/>";

      } else {
        // There is data, show the Limit/sortOrder menus
        $output.= $this->print_report_menu($limit, $order, $fields, $searchby, $searchstring);
        $output.= '<br>' .$rowcount .' matching result(s) found:</br>';
      }

      $res->close();

    } catch (Exception $e) {
      if (isset($conn))
        $conn->close();
      throw $e;
    }

    // close table, send back text
    $output.="</table>";
    return($output);
  }



} // class



if ( isset($_POST['submit']) ) {             // form submit, check fields
## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);

  $logger->debug("GuiList1 main -submit");
  #echo handle_submit();

} else {    
  # Do nothing, we've been included.
}

?>
