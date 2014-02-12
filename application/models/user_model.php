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
   * Returns the user with the given email
   * @return User_entity
   */
  public function get_by_email($email) {
    $result = $this->mongo_db
      ->where('email', $email)
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
      ->where('uid', (int) $uid)
      ->get('users');
    
    if (!empty($result)) {
      return User_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * Saves a user to the database.
   * If the user is not saved yet, its uid will be added to the 
   * user_entity.
   * @param User_entity (by reference)
   * 
   * @return boolean
   *   Whether or not the save was successful.
   */
  public function save(User_entity &$user_entity) {
    
    $prepared_data = array(
      'name' => $user_entity->name,
      'username' => $user_entity->username,
      'email' => $user_entity->email,
      'password' => $user_entity->password,
      'author' => $user_entity->author,
      'status' => $user_entity->username,
      'roles' => $user_entity->roles,
      'updated' => Mongo_db::date()  
    );
    
    if ($user_entity->is_new()) {
      $user_entity->uid = increment_counter('user_uid');
      $prepared_data['uid'] = $user_entity->sid;
      $prepared_data['created'] = Mongo_db::date();
      
      $result = $this->mongo_db->insert('users', $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('uid', $user_entity->uid)
        ->update('users');
      
      return $result !== FALSE ? TRUE : FALSE;
    }
    
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */