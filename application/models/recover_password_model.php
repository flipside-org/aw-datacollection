<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Recover Password model.
 */
class Recover_password_model extends CI_Model {
  // TODO: Move duration to config!
  private $duration;
  
  /**
   * Model constructor.
   */
  function __construct() {
    parent::__construct();
    
    $this->duration = 60 * 15;
    
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
    // Delete any prevoiusly set hashes for this email.
    $this->_gc($email);
    
    // Generate hash.
    $hash = sha1(microtime(TRUE) . rand(1, 999) . $email);
    
    $duration = 60 * 15;
    $data = array(
      'email' => $email,
      'hash' => $hash,
      'expire' => time() + $duration
    );
    
    $result = $this->mongo_db->insert('password_recovery', $data);    
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
      ->get('password_recovery');
    
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
      ->update('password_recovery');
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
    ->deleteAll('password_recovery');
  }
}

/* End of file recover_password_model.php */
/* Location: ./application/models/recover_password_model.php */