<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
exit;
/**
 * Status Message
 * Static class to help with messages.
 * An helper is being used so the class is not instantiated.
 * 
 * @static
 * @uses session library
 * 
 */
class Logger {
  
  /**
   * Default path to store logs.
   */
  private static $location = 'application/logs/';
  
  /**
   * Whether the logger was bootstrapped.
   */
  private static $bootrap = FALSE;
  
  /**
   * Whether the logger is enabled.
   */
  private static $enabled = TRUE;
  
  private static $levels = array(
    
  );
  
  /**
   * Date format.
   */
  private static $date_format = 'Y-m-d H:i:s';
  
  private static function _bootstrap() {
    if (self::$bootrap) {
      return TRUE;
    }
    self::$bootrap = TRUE;
    
    if ( ! is_dir(self::$location) OR ! is_really_writable(self::$location)) {
      self::$enabled = FALSE;
    }
  }  
  
  /**
   * Logs a message.
   * 
   * @static
   */
  public static function log($level) {
    self::_bootstrap();
    
    if (self::$enabled === FALSE) {
      return FALSE;
    }

    $level = strtoupper($level);

    if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
    {
      return FALSE;
    }

    $filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
    $message  = '';

    if ( ! file_exists($filepath))
    {
      $message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
    }

    if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
    {
      return FALSE;
    }

    $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

    flock($fp, LOCK_EX);
    fwrite($fp, $message);
    flock($fp, LOCK_UN);
    fclose($fp);

    @chmod($filepath, FILE_WRITE_MODE);
    return TRUE;
  }
  
}

// ------------------------------------------------------------------------

/* End of file logger_helper.php */
/* Location: ./application/helpers/logger_helper.php */