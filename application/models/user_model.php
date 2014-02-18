<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the user entity.
// Since the model works with user entity its safe to load it here.
load_entity('user');

/**
 * User model.
 */
class User_model extends CI_Model {
  
  /**
   * Mongo db collection for this model.
   */
  const COLLECTION = 'users';
  
  /**
   * Mongo db counter collection for this model.
   */
  const COUNTER_COLLECTION = 'user_uid';
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }
  
  /**
   * Returns all the users as User_entity
   * @return array of User_entity
   */
  public function get_all() {
    $result = $this->mongo_db
      ->orderBy(array('created' => 'desc'))
      ->get(self::COLLECTION);
    
    $users = array();
    foreach ($result as $value) {
      $users[] = User_entity::build($value);
    }
    
    return $users;
  }
  
  /**
   * Returns the user with the given username
   * @return User_entity
   */
  public function get_by_username($username) {
    $result = $this->mongo_db
      ->where('username', $username)
      ->get(self::COLLECTION);
    
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
      ->get(self::COLLECTION);
    
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
      ->get(self::COLLECTION);
    
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
  public function save(User_entity &$entity) {
    // Set update date:
    $entity->updated = Mongo_db::date();
    
    $prepared_data = array();
    foreach ($entity as $field_name => $field_value) {
      $prepared_data[$field_name] = $field_value;
    }
        
    if ($entity->is_new()) {
      $entity->uid = increment_counter(self::COUNTER_COLLECTION);
      $prepared_data['uid'] = $entity->uid;
      // Set creation date:
      $prepared_data['created'] = Mongo_db::date();
      
      $result = $this->mongo_db->insert(self::COLLECTION, $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('uid', $entity->uid)
        ->update(self::COLLECTION);
      
      return $result !== FALSE ? TRUE : FALSE;
    }
    
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */