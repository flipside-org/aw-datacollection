<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the user entity.
// Since the model works with user entity its safe to load it here.
load_entity('user');

/**
 * User model.
 */
class User_model extends CI_Model {
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }
  
  /**
   * Returns the user with the given username
   * @return User_entity
   */
  public function get_by_username($username) {
    $result = $this->mongo_db
      ->where('username', $username)
      ->get('users');
    
    if (!empty($result)) {
      return User_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * Returns the user with the given uid
   * @return User_entity
   */
  public function get($uid) {
    $result = $this->mongo_db
      ->where('uid', $uid)
      ->get('users');
    
    if (!empty($result)) {
      return User_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */