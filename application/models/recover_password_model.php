<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Recover Password model.
 */
class Recover_password_model extends CI_Model {
  /**
   * @var int
   * Duration of the hash
   */
  private $duration;
  
  /**
   * Mongo db collection for this model.
   */
  const COLLECTION = 'password_recovery';
  
  /**
   * Model constructor.
   */
  function __construct() {
    parent::__construct();
    
    // Default duration - 15 min
    $this->set_duration(60 * 15);
    
    // Garbage collector.
    $this->_gc();
  }
  
  /**
   * Generates a new unique hash and stores it in the database.
   * 
   * @param string $email
   *   The user email.
   * @return mixed
   *   The hash if it was correctly stored, FALSE otherwise.
   */
  public function generate($email) {    
    // Delete any previously set hashes for this email.
    $this->_gc($email);
    
    // Generate hash.
    $hash = sha1(microtime(TRUE) . rand(1, 999) . $email);
    
    $data = array(
      'email' => $email,
      'hash' => $hash,
      'expire' => time() + $this->duration
    );
    
    $result = $this->mongo_db->insert(self::COLLECTION, $data);    
    return $result !== FALSE ? $hash : FALSE;
    
  }
  
  /**
   * Checks if the hash is still valid.
   * 
   * @param string $hash
   *   The hash to check.
   * @return mixed
   *   The user email if the hash is valid, FALSE otherwise.
   */
  public function validate($hash) {
    $result = $this->mongo_db
      ->where('hash', $hash)
      ->whereGte('expire', time())
      ->get(self::COLLECTION);
    
    return empty($result) ? FALSE : $result[0]['email'];
  }
  
  /**
   * Invalidates the hash setting the expire date in the past.
   * 
   * @param string $hash
   *   The hash to invalidate.
   */
  public function invalidate($hash) {
    $result = $this->mongo_db
      ->set('expire', -1)
      ->where('hash', $hash)
      ->update(self::COLLECTION);
  }
  
  /**
   * Set the duration of the hash from the moment of its creation.
   * 
   * @param int $sec
   *   Duration of the hash in seconds.
   * @return mixed
   *   This to allow chaining, or FALSE if $sec is invalid
   */
  public function set_duration($sec) {
    if (is_numeric($sec) && $sec > 0) {
      $this->duration = $sec;
      return $this;
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * Garbage collector for password recovery.
   * Deleted all the expired hashes.
   * 
   * @param string $email
   *   Default to NULL. If given the hashes for the given email will also
   *   be removed, even if they are not expired.
   */
  protected function _gc($email = NULL) {
    $where = array(
      'expire' => array('$lt' => time())
    );    
    if ($email) {
      $where['email'] = $email;
    }
    
    $this->mongo_db
    ->orWhere($where)
    ->deleteAll(self::COLLECTION);
  }
}

/* End of file recover_password_model.php */
/* Location: ./application/models/recover_password_model.php */