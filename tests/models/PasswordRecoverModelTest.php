<?php

class Password_recover_model_test extends PHPUnit_Framework_TestCase
{
  
  private static $CI;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    
    self::$CI->load->model('recover_password_model');
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
  }
  
  public function test_generate() {
    // Cleanup.
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', Recover_password_model::COLLECTION);
    
    $result = self::$CI->recover_password_model->generate('email@test.com');
    $this->assertInternalType('string', $result);
    
    $result = self::$CI->recover_password_model->generate('email2@test.com');
    $this->assertInternalType('string', $result);
    
    // After two hashes, there should be 2 entries in the database.
    $number_entries = self::$CI->mongo_db->count(Recover_password_model::COLLECTION);
    $this->assertEquals(2, $number_entries);
    
    // When generating all previous hashes for given email are deleted.
    // So after this insert we should still have 2 hashes.
    $result = self::$CI->recover_password_model->generate('email@test.com');
    $this->assertInternalType('string', $result);
    
    $number_entries = self::$CI->mongo_db->count(Recover_password_model::COLLECTION);
    $this->assertEquals(2, $number_entries);
  }
  
  public function test_validate() {
    // Cleanup.
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', Recover_password_model::COLLECTION);
    
    // Fixtures.
    $fixtures = array(
      array(
        'email' => 'email@test.com',
        'hash' => sha1('one'),
        'expire' => -1,
      ),
      array(
        'email' => 'email2@test.com',
        'hash' => sha1('two'),
        'expire' => time() + 9999,
      ),
    );
    self::$CI->mongo_db->batchInsert(Recover_password_model::COLLECTION, $fixtures);
    
    // This hash is expired.
    $result_expired = self::$CI->recover_password_model->validate(sha1('one'));
    $this->assertFalse($result_expired);
    
    // When valid the email is returned.
    $result_valid = self::$CI->recover_password_model->validate(sha1('two'));
    $this->assertEquals('email2@test.com', $result_valid);
  }
  
  /**
   * @depends test_validate
   */
  public function test_invalidate() {
    // Cleanup.
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', Recover_password_model::COLLECTION);
    
    // Fixtures.
    $fixtures = array(
      array(
        'email' => 'email@test.com',
        'hash' => sha1('one'),
        'expire' => -1,
      ),
      array(
        'email' => 'email2@test.com',
        'hash' => sha1('two'),
        'expire' => time() + 9999,
      ),
    );
    self::$CI->mongo_db->batchInsert(Recover_password_model::COLLECTION, $fixtures);
    
    self::$CI->recover_password_model->invalidate(sha1('two'));
    
    $result_after_invalidate = self::$CI->recover_password_model->validate(sha1('two'));
    $this->assertFalse($result_after_invalidate);
  }
  
}

?>