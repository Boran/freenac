<?php
/**
 *
 * WebCommon.php
 *
 * Long description for file:
 * Class of common objects/functions for the WebGUI
 * see also the 'Common' Class from which this extends
 *
 * @package     FreeNAC
 * @author      Sean Boran (FreeNAC Core Team)
 * @copyright   2008 FreeNAC
 * @license     http://www.gnu.org/copyleft/gpl.html   GNU Public License Version 3
 * @version     SVN: $Id
 * @link        http://freenac.net
 *
 */
class WebCommon extends Common 
{
  protected $calling_script;


  function __construct()
  {
    parent::__construct();
    $this->calling_script=basename($_SERVER['SCRIPT_FILENAME']);   // TBD: clean?

    if (! function_exists('mysqli_connect')) {
      throw new DatabaseErrorException("PHP has not been compiled with mysqli support");
    }

    // Show Webpage start, is the constructor the right place?
    echo $this->print_header();  
  }

  /**
   * Add the class name to debug messages
   */
  protected function debug($msg, $level=1) 
  {
    $this->logger->debug(get_class($this) ." " .$msg, $level);
  }


  public function print_header($print_links=true)
  {
    global $header1, $header2, $head_right1, $head_right2;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_header: HEADER already true',2);

    } else {
      if ($print_links===false) {
        $lthis->debug('print_header: do not print right links', 3);
        $head_right1='';
        $head_right2='';
      }
      $ret= $header1 . $header2;
      define('HEADER',true); // The header is out
      $this->debug('print_header: done', 3);
      return $ret;
    }
  }


  public function print_headerSmall($print_links=true)
  {
    global $header1, $header2, $head_right1, $head_right2;

    if (defined('HEADER')){   // already displayed?
      $this->debug('print_headerSmall: HEADER already true',2);

    } else {
      if ($print_links===false) {
        $lthis->debug('print_header: do not print right links', 3);
        $head_right1='';
        $head_right2='';
      }
      #$ret= $header1 . $header2;
      $ret= $header1 . $header2_small;
      define('HEADER',true); // The header is out
      $this->debug('print_header: done', 3);
      return $ret;
    }
  }


  public function print_footer()
  {
        if(!defined(FOOTER)){
                $ret="</table></body></html>";
                define('FOOTER',true);
                return $ret;
        }
  }


}

?>
