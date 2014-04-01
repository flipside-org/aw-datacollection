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
   * Stores messages in the same order has they were set.
   */
  private static $messages = array();
    
  /**
   * Sets a notice message.
   * 
   * @static
   */
  public static function set($msg, $level, $sticky = FALSE) {
    self::$messages[] = array(
      'msg' => $msg,
      'level' => $level,
      'sticky' => $sticky,
      'time' => time(),
    );
    self::store();
  }
  
  /**
   * Sets a notice message.
   * 
   * @static
   */
  public static function notice($msg, $sticky = TRUE) {
    self::set($msg, 'notice', $sticky);
  }
  
  /**
   * Sets a success message.
   * 
   * @static
   */
  public static function success($msg, $sticky = FALSE) {
    self::set($msg, 'success', $sticky);
  }
  
  /**
   * Sets a warning message.
   * Writes the message to session.
   * 
   * @static
   */
  public static function warning($msg, $sticky = TRUE) {
    self::set($msg, 'warning', $sticky);
  }
  
  /**
   * Sets a error message.
   * Writes the message to session.
   * 
   * @static
   */
  public static function error($msg, $sticky = TRUE) {
    self::set($msg, 'error', $sticky);
  }
  
  /**
   * Writes messages to session.
   * @static
   */
  public static function store() {    
    $CI =& get_instance();
    $CI->session->set_userdata(self::$userdata_key, self::$messages);
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