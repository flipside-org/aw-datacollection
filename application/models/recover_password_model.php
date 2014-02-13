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
  
  public function validate($hash) {
    $result = $this->mongo_db
      ->where('hash', $hash)
      ->whereGte('expire', time())
      ->get('password_recovery');
    
    return empty($result) ? FALSE : $result[0];
  }
  
  public function invalidate($hash) {
    $result = $this->mongo_db
      ->where('hash', $hash)
      ->delete('password_recovery');
  }
  
  private function _gc($email = null) {
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