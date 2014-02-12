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
        'uid' => 1,
        'email' => 'admin@localhost',
        'name' => 'Admin',
        'username' => 'admin',
        'password' => sha1('admin'),
        'roles' => array('administrator'),
        'author' => null,
        'status' => 2,
        
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    );
    
    
    self::$CI->mongo_db->batchInsert('users', $fixture);
    
    // Load model
    self::$CI->load->model('user_model');
    
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
  
}

?>