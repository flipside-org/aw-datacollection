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
 * Alias of current_user()->is_logged()
 * 
 * @return boolean
 */
if ( ! function_exists('is_logged')) {
  function is_logged() {
    return current_user()->is_logged() === TRUE;
  }
}

/**
 * Checks whether the logged user has a given permission.
 * Alias of current_user()->has_permission($perm)
 * 
 * @return boolean
 */
if ( ! function_exists('has_permission')) {
  function has_permission($perm) {
    return current_user()->has_permission($perm);
  }
}

/**
 * Returns the logged user.
 * 
 * @return mixed
 *   User entity if there's a logged user, FALSE otherwise
 */
if ( ! function_exists('current_user')) {
  function current_user() {
    static $current_user;
    
    if (!isset($current_user)) {
      $CI = get_instance();
      $uid = $CI->session->userdata('user_uid');
      
      if ($uid !== FALSE) {
        // There is a logged user.
        $current_user = $CI->user_model->get($uid);
        if ($current_user && $current_user->is_active()){
          // Logged user found. Set logged and return.
          $current_user->set_logged();
          return $current_user;
        }
        elseif ($current_user && !$current_user->is_active()) {
          // The user is no longer active.
          // Kill session and redirect to login.
          $CI->session->sess_destroy();
          redirect('login');
        }
      }
      
      $current_user = User_entity::build(array());
      $current_user->set_logged(FALSE);
    }
    
    return $current_user;
  }
}

// ------------------------------------------------------------------------

/* End of file general_utils_helper.php */
/* Location: ./application/helpers/general_utils_helper.php */