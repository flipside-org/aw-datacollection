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
   * @param mixed $statuses
   *   Status or array of statuses to query for. Providing NULL is the same as
   *   providing all the statuses.
   *   By default only returns all users.
   * 
   * @return array of User_entity
   */
  public function get_all($statuses = NULL) {
    if ($statuses != NULL) {
      $statuses = !is_array($statuses) ? array($statuses) : $statuses;
      $this->mongo_db->whereIn('status', $statuses);
    }
    
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
   * Returns the user with the given username.
   * @param string $username
   * 
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
   * @param string email
   * 
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
   * @param int uid
   * 
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
   * Returns the users with the given roles.
   * @param mixed roles
   *   Single role or array of roles the user has to have.
   *   If an empty array is provided it will return users without roles.
   *   If ROLE_REGISTERED is provided, all users will be returned.
   * @param mixed $statuses
   *   Status or array of statuses to query for. Providing NULL is the same as
   *   providing all the statuses.
   *   By default only returns all users.
   * 
   * @return User_entity
   */
  public function get_with_role($roles, $statuses = User_entity::STATUS_ACTIVE) {
    if (!is_array($roles)) {
      $roles = array($roles);
    }
    
    if ($statuses != NULL) {
      $statuses = !is_array($statuses) ? array($statuses) : $statuses;
      $this->mongo_db->whereIn('status', $statuses);
    }
    
    if (!in_array(ROLE_REGISTERED, $roles)) {
      if (empty($roles)) {
        $this->mongo_db->where('roles', array());
      }
      else {
        $this->mongo_db->whereInAll('roles', $roles);
      }
    }
    
    $result = $this->mongo_db->get(self::COLLECTION);
    
    $users = array();
    foreach ($result as $value) {
      $users[] = User_entity::build($value);
    }
    
    return $users;
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
  
  /**
   * Checks if the value is unique for the given field.
   * If the field doesn't exist, true will be returned.
   * It's not in the scope of the function to check that
   * since it will never be directly used by the user.
   * 
   * @param string $field
   *   The field to check
   * @param string $value
   *   The field value
   * @return boolean
   */
  public function check_unique($field, $value) {
    $result = $this->mongo_db
      ->where($field, $value)
      ->count(self::COLLECTION);
      
    return $result == 0;
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */