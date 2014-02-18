<?php

class User_model_test extends PHPUnit_Framework_TestCase
{
  
  private static $CI;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    // Some data!
    $fixture = array(
      array(
        // Returns 1.
        'uid' => increment_counter('user_uid'),
        'email' => 'admin@localhost.dev',
        'name' => 'Admin',
        'username' => 'admin',
        'password' => sha1('admin'),
        'roles' => array('administrator'),
        'author' => null,
        'status' => 2,
        
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        // Returns 2.
        'uid' => increment_counter('user_uid'),
        'email' => 'test_user@airwolf.dev',
        'name' => 'test user',
        'username' => 'test_user',
        'password' => sha1('test_user'),
        'roles' => array(),
        'author' => null,
        'status' => 2,
        
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    );
    
    
    self::$CI->mongo_db->batchInsert(User_model::COLLECTION, $fixture);
    
    // User model is autoloaded.
    // No need to load.    
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
  }
  
  public function test_get_user_by_uid() {
    $user_one = self::$CI->user_model->get(1);
    $this->assertInstanceOf('User_entity', $user_one);
    $this->assertEquals(1, $user_one->uid);
    
    $user_two = self::$CI->user_model->get("1");    
    $this->assertInstanceOf('User_entity', $user_two);
    $this->assertEquals(1, $user_one->uid);
    
    $user_three = self::$CI->user_model->get('abc');
    $this->assertFalse($user_three);
  }
  
  public function test_get_user_by_username() {
    $username = 'admin';
    $user_one = self::$CI->user_model->get_by_username($username);
    
    $this->assertInstanceOf('User_entity', $user_one);
    $this->assertEquals($username, $user_one->username);
    
    $user_two = self::$CI->user_model->get_by_username('abc');
    $this->assertFalse($user_two);
  }
  
  public function test_get_user_by_email() {
    $email = 'admin@localhost.dev';
    $user_one = self::$CI->user_model->get_by_email($email);
    
    $this->assertInstanceOf('User_entity', $user_one);
    $this->assertEquals($email, $user_one->email);
    
    $user_two = self::$CI->user_model->get_by_email('abc');
    $this->assertFalse($user_two);
  }
  
  /**
   * @depends test_get_user_by_uid
   */
  public function test_edit_user() {
    $user = self::$CI->user_model->get(2);
    $this->assertEquals('test user', $user->name);
    
    $user->name = 'Another user name';
    $user->set_password('pass');
    
    $result = self::$CI->user_model->save($user);
    
    $this->assertTrue($result);
    $this->assertEquals('Another user name', $user->name);
    $this->assertEquals(sha1('pass'), $user->password);
    
    // Check status saving.
    // The status must be converted to integer before going into the DB.
    $user->set_status("2");
    self::$CI->user_model->save($user);
    // Query again user.
    $user = self::$CI->user_model->get(2);
    $this->assertInternalType('int', $user->status);
    
  }
  
}

?>