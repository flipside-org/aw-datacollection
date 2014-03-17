<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Store general helper functions.

if ( ! function_exists('property_if_not_null')) {
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
  function property_if_not_null($obj, $prop, $default = '') {
    return $obj !== NULL ? $obj->{$prop} : $default;
  }
}

if ( ! function_exists('is_logged')) {
  /**
   * Checks whether the user is logged.
   * Alias of current_user()->is_logged()
   * 
   * @return boolean
   */
  function is_logged() {
    return current_user()->is_logged() === TRUE;
  }
}

if ( ! function_exists('has_permission')) {
  /**
   * Checks whether the logged user has a given permission.
   * Alias of current_user()->has_permission($perm)
   * 
   * @return boolean
   */
  function has_permission($perm) {
    return current_user()->has_permission($perm);
  }
}

if ( ! function_exists('current_user')) {
  /**
   * Returns the logged user.
   * 
   * @param boolean $reset
   *   If TREU fetches the userdata from the database. (default FALSE)
   *   To increase performance once the user is fetched it is stored in a
   *   static variable.
   * 
   * @return mixed
   *   User entity if there's a logged user, FALSE otherwise
   */
  function current_user($reset = FALSE) {
    static $current_user;
    
    if (!isset($current_user) || $reset) {
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

if ( ! function_exists('show_403')) {
  /**
   * Show a 403 Operation not allowed error
   */
  function show_403() {
    if (!is_logged()) {
      $login_link = '<a href="' . base_url('login') . '" title="Login Page">login</a>';
      show_error("The requested operation is not allowed. Please $login_link and try again.", 403, 'Operation not allowed');
    }
    show_error("The requested operation is not allowed.", 403, 'Operation not allowed');
  }
}


// ------------------------------------------------------------------------

/* End of file general_utils_helper.php */
/* Location: ./application/helpers/general_utils_helper.php */