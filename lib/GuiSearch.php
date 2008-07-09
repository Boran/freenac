<?php
/**
 *
 *
 * @package     FreeNAC
 * @author      Many: S.Boran, T.Dagonnier, P.Bizeau
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id$
 * @link        http://freenac.net
 *
 */


class GuiSearch extends WebCommon
{
  private $id, $action;      // See also WebCommon and Common


  function __construct($action, $debug_level=1)
  {
    parent::__construct(false);     // See also WebCommon and Common
    $this->logger->setDebugLevel($debug_level);
    $this->debug("GuiSearch__construct, debug=$debug_level, action=$action", 2);

    // verify/clean 'action'
    // Now, have we a REQUEST action to carry out?
    if ( !isset($action) ) {
       throw new InvalidWebInputException("No action ");
    }
    $this->action=validate_webinput($action);

  }

  public function print_title($title)
  {
    echo $this->print_header();
    echo "<div id='GuiList1Title'>{$title}</div>";
    //$this->debug($_SESSION['login_data'] .":Id=$id:" , 1);
  }

  public function handle_request()
  {
    $action=$this->action;
    #global $_SESSION, $_REQUEST;
    #$_REQUEST=array_map('validate_webinput',$_REQUEST);
    $this->debug("handle_request() $action", 2);

    if (isset($action)) {
      if ($action==='Search') {
        $this->print_title('Search for end devices');
        echo $this->SearchForm();
        #echo $this->query();
        echo $this->print_footer();

      } else {
        // do nothing, action does not concern us.
      }
    }
  }

  public function SearchForm()
  {
    $this->debug("SearchForm()", 3);
    #var_dump($_REQUEST);
    if ($_SESSION['nac_rights']<1)
      throw new InsufficientRightsException('SearchForm() ' .$_SESSION['nac_rights']);
    $conn=$this->getConnection();     //  make sure we have a DB connection

    // Clean inputs from the web, (security). Use _REQUEST to
    // allow both GET (automation) or POST (interactive GUIs)
    $_REQUEST=array_map('validate_webinput',$_REQUEST);
    if (isset($_REQUEST['searchstring']))
      $cur_mac=$this->htmlescape($_REQUEST['searchstring']);
    $cur_mac= empty($cur_mac) ? '0006.5bdd.a929' : $cur_mac;

    $output=<<<TXT
<form action="search.php" method="GET">
<tr>
  <td width="87">MAC Address:</td> 
  <td width="400"> <input name="searchstring" type="text" value="{$cur_mac}"/></td>
</tr>
<tr>
  <td><input type="submit" name="action" class="bluebox" value="Find" 
   title='Find devices with this mac, which can be in 3 formats XXX.XXX.XXX XXXXXXXXXXXX or XX:XX:XX:XX:XX:XX'/> </td>
</tr>
</form>
TXT;


    return $output;
  }
  

} // class



/////////// main() should never get here .. ///////////////////////////////////////

if (isset($_POST['action']) && $_POST['action']=='Edit') {
  $logger=Logger::getInstance();
  $logger->debug("GuiSearch:action:". $_POST['action'], 1);
}

if ( isset($_POST['submit']) ) {             // form submit, check fields
## Initialise (standard header for all modules)
  dir(dirname(__FILE__)); set_include_path("./:../");
  require_once('webfuncs.inc');
  $logger=Logger::getInstance();
  $logger->setDebugLevel(1);
  $logger->debug("GuiSearch main -submit");
  #echo handle_submit();

} else {
  # Do nothing, we've been included.
}

?>

