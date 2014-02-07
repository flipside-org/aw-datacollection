<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * JavaScript settings library.
 * 
 * Used to pass data to JavaScript in an object.
 * 
 * @access public
 * @package CodeIgniter
 * @subpackage library
 * @version 1.0
 * @author Daniel da Silva (daniel.silva@flipside.org)
 */
class Js_settings {
  
  /**
   * Holds all settings.
   * @var array
   */
  private $js_settings = array();

  /**
   * Class constructor.
   * Sets the defaults.
   */
  function __construct() {
    $this->set_defaults();
  }
  
  /**
   * Sets the default settings.
   * @return this
   *   To allow chaining.
   */
  public function set_defaults() {
    $this->add('base_url', base_url());
    $this->add('check_connection_url', base_url('assets/checkforconnection.php'));
    return $this;
  } 
  
  /**
   * Resets the settings.
   * 
   * @param array $data
   *   If the input array is empty the settings will be reset
   *   to the defaults, otherwise will be set to the given value.
   *   Default value is empty.
   * 
   * @return this
   *   To allow chaining.
   */
  public function reset($data = array()) {
    if (empty($data)) {
      $this->set_defaults();
    }
    else {
      $this->js_settings = $data;
    }
    return $this;
  }
  
  /**
   * Clears all the settings.
   * 
   * @return this
   *   To allow chaining.
   */
  public function clear() {
    $this->js_settings = array();
    return $this;
  }
  
  /**
   * Adds a setting.
   * 
   * @param mixed $key
   *   The name of the setting.
   *   This can also be an array, and in that case it will be
   *   recursively merged with the settings.
   * @param mixed $value
   *  The value. This is only relevant if the $key in not an array.
   * 
   * @return this
   *   To allow chaining.
   * 
   */
  public function add($key, $value = '') {    
    if (is_array($key)) {
      $data = $key;
      $this->js_settings = array_merge_recursive($this->js_settings, $data);
    }
    else {
      $this->js_settings[$key] = $value;
    }
    
    return $this;
  }
  
  /**
   * Returns the settings in an array keyed "settings"
   * 
   * @return array
   *   All settings.
   */
  public function get_settings() {
    $data = array(
      'settings' => $this->js_settings
    );
    
    return $data;
  }
  
  /**
   * Encodes the settings as json.
   * 
   * @return string
   *   The json encoded version of the settings.
   */
  public function prepare_json() {    
    return json_encode($this->get_settings()); 
  }
  
  /**
   * Returns the JavaScript script tag with the settings.
   * 
   * @return string
   */
  public function get_output_script() {
    $output = '<script>';
    $output .= 'var Aw = ' . $this->prepare_json();
    $output .= '</script>';
    
    return $output;
  }
  
}

// ------------------------------------------------------------------------

/* End of file Js_settings.php */
/* Location: ./application/libraries/Js_settings.php */