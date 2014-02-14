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
}

?>