<?php

class User_entity_test extends PHPUnit_Framework_TestCase
{
	
	public static function setUpBeforeClass() {
    load_entity('user');
	}
	
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid field for the user: foo
   */
	public function test_construct_non_existent_prop() {
	  $data = array(
      'name' => 'A user',
      'foo' => 'bar'
    );
    
    $user = new User_entity($data);
	}
  
  public function test_user_permissions() {
    $mock_permissions = array(
      'test system' => array('administrator'),
      'view site' => array('anonymous', 'administrator', 'authenticated'),
      'access strange feature' => array('authenticated'),
      'browse around' => array('ALL')
    );
    
    
    $mock_authenticated = array(
      'name' => 'authenticated',
    );    
    $user_authenticated = new User_entity($mock_authenticated);
    $user_authenticated->set_permissions_array($mock_permissions);
    $user_authenticated->set_logged();
    $this->assertTrue($user_authenticated->has_permission('view site'));
    $this->assertFalse($user_authenticated->has_permission('test system'));
    
    
    $mock_various = array(
      'name' => 'Various',
      'roles' => array('administrator'),
    );    
    $user_various = new User_entity($mock_various);
    $user_various->set_permissions_array($mock_permissions);
    $user_various->set_logged();
    $this->assertTrue($user_various->has_permission('view site'));
    $this->assertTrue($user_various->has_permission('browse around'));
    
    
    $mock_anonymous = array(
      'name' => 'anonymous',
    );    
    $user_anonymous = new User_entity($mock_anonymous);
    $user_anonymous->set_permissions_array($mock_permissions);
    $user_anonymous->set_logged(FALSE);    
    $expected = array('view site', 'browse around');
    $this->assertEquals($expected, $user_anonymous->all_permissions());    
  }

  public function test_set_methods() {
    // Some values can be set in the constructor.
    $userdata = array(
      'name' => 'A new test user',
      'username' => 'new_test_user',
      'email' => 'test@testing.com',
    );
    
    $user = new User_entity($userdata);
    // Must be converted to int before sending to DB.
    $user->set_status("2");
    $user->set_password('password');
    
    $this->assertInternalType('int', $user->status);
    $this->assertEquals('password', $user->password);
    
    $user->set_roles(array('role1', 'role2'));
    $this->assertEquals(array('role1', 'role2'), $user->roles);
    
    $user->set_roles(array('role1', 'role2', 'role2'));
    $this->assertEquals(array('role1', 'role2'), $user->roles);
    
    $user->set_roles('not_a_role');
    $this->assertEmpty($user->roles);
    
    $user->set_roles(NULL);
    $this->assertEmpty($user->roles);
  }
}

?>