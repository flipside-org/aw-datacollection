<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Store general helper functions.

/**
 * Returns an object's property if is not null.
 * When null it will return the default value.
 * 
 * @param object $obj
 *   The object.
 * @param string $prop
 *   The property to check.
 * @param mixed $default
 *   The value to return if the property is null. Default to ''
 * 
 * @return mixed
 *   The property value or the default.
 * 
 */
if ( ! function_exists('property_if_not_null')) {
  function property_if_not_null($obj, $prop, $default = '') {
    return $obj !== NULL ? $obj->{$prop} : $default;
  }
}

/**
 * Checks whether the user is logged.
 * 
 * @return boolean
 */
if ( ! function_exists('is_logged')) {
  function is_logged() {
    return get_instance()->session->userdata('is_logged');
  }
}

/**
 * Returns the logged user.
 * 
 * @return mixed
 *   User entity if there's a logged user, FALSE otherwise
 */
if ( ! function_exists('get_logged_user')) {
  function get_logged_user() {
    static $current_user;
    
    if (!isset($current_user)) {  
      $CI = get_instance();    
      $uid = $CI->session->userdata('user_uid');
      
      if ($uid !== FALSE) {
        $current_user = $CI->user_model->get($uid);
      }
      else {
        return FALSE;
      }
    }
    
    return $current_user;;
  }
}

// ------------------------------------------------------------------------

/* End of file general_utils_helper.php */
/* Location: ./application/helpers/general_utils_helper.php */