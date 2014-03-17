<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Status Message
 * Static class to help with messages.
 * An helper is being used so the class is not instantiated.
 * 
 * @static
 * @uses session library
 * 
 */
class Status_msg {
  
  /**
   * Key used by session to store data.
   */
  private static $userdata_key = 'status_msg';
  
  /**
   * Stores success messages.
   */
  private static $success = array();
  
  /**
   * Stores warning messages.
   */
  private static $warning = array();
  
  /**
   * Stores error messages.
   */
  private static $error = array();
  
  /**
   * Sets a success message.
   * 
   * @static
   */
  public static function success($msg) {
    self::$success[] = $msg;
    self::store();
  }
  
  /**
   * Sets a warning message.
   * Writes the message to session.
   * 
   * @static
   */
  public static function warning($msg) {
    self::$warning[] = $msg;
    self::store();
  }
  
  /**
   * Sets a error message.
   * Writes the message to session.
   * 
   * @static
   */
  public static function error($msg) {
    self::$error[] = $msg;
    self::store();
  }
  
  /**
   * Writes messages to session.
   * @static
   */
  public static function store() {    
    $CI =& get_instance();
    $CI->session->set_userdata(self::$userdata_key, array(
      'success' => self::$success,
      'warning' => self::$warning,
      'error' => self::$error
    ));
  }
  
  /**
   * Retrieves the messages from session.
   * By default after the messages are retrieved they
   * are removed form session.
   * 
   * @static
   * 
   * @param booblean $erase_data
   *   Whether or not to erase the messages from session.
   *   Default to TRUE.
   * @return array
   *   Messages from session
   */
  public static function get($erase_data = TRUE) {
    $CI =& get_instance();
    $data = $CI->session->userdata(self::$userdata_key);
    if ($erase_data) {
      $CI->session->unset_userdata(self::$userdata_key);
    }
    return $data;
  }
  
}

// ------------------------------------------------------------------------

/* End of file status_msg_helper.php */
/* Location: ./application/helpers/status_msg_helper.php */