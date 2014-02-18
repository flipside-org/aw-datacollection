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
    // Ensure correct user.
    $this->assertEquals('test user', $user->name);
    // Alter name.
    $user->name = 'Another user name';
    // Alter password.
    $user->set_password('pass');
    // Check status saving.
    $user->set_status(User_entity::STATUS_ACTIVE);
    
    $result = self::$CI->user_model->save($user);
    $this->assertTrue($result);
    
    // Get from db and check.
    $user = self::$CI->user_model->get(2);
    $this->assertEquals('Another user name', $user->name);
    $this->assertEquals(sha1('pass'), $user->password);
    $this->assertEquals(User_entity::STATUS_ACTIVE, $user->status);
  }
  
  /**
   * @depends test_get_user_by_uid
   */
  public function test_add_user() {
    // Some values can be set in the constructor.
    $userdata = array(
      'name' => 'A new test user',
      'username' => 'new_test_user',
      'email' => 'test@testing.com',
    );
    
    $user = new User_entity($userdata);
    $user
      ->set_password('test_password')
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(NULL);
    
    // Save.
    // We have two test users. This one will be added with uid 3.
    self::$CI->user_model->save($user);
    
    $saved_user = self::$CI->user_model->get(3);    
    $this->assertEquals('A new test user', $saved_user->name);
    $this->assertEquals('new_test_user', $saved_user->username);
    $this->assertEquals('test@testing.com', $saved_user->email);
    $this->assertEquals(User_entity::STATUS_ACTIVE, $saved_user->status);
    $this->assertInternalType('int', $saved_user->status);
    $this->assertEmpty($saved_user->roles);
  }
  
  public function test_unique() {
    $this->assertTrue(self::$CI->user_model->check_unique('username', 'non_existen_user'));
    $this->assertFalse(self::$CI->user_model->check_unique('username', 'admin'));
    
    $this->assertTrue(self::$CI->user_model->check_unique('email', 'new_user_email@localhost.dev'));
    $this->assertFalse(self::$CI->user_model->check_unique('email', 'admin@localhost.dev'));
    
    // Invalid fields should return true.
    // It's not in the scope of the function to check field validity.
    $this->assertTrue(self::$CI->user_model->check_unique('invalid_field', 'nothing'));
  }
}

?>