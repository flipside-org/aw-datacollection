<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Javascript settings library.
 * 
 * Used to pass data to javascript in an object.
 * 
 */
class Js_settings {
  
  private $js_settings = array();
  
  public function reset($data = array()) {
    $this->js_settings = $data;
    return $this;
  }
  
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

  public function get_settings() {
    $data = array(
      'settings' => $this->js_settings
    );
    
    return $data;
  }
  
  public function prepare_json() {    
    return json_encode($this->get_settings()); 
  }
  
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