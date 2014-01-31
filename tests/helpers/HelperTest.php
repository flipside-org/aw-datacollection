<?php

class Status_msg_helper_test extends PHPUnit_Framework_TestCase
{
	
	public static function setUpBeforeClass() {
	}
	
	public function test_data_storage(){
		Status_msg::success('Success message.');
    
    $msg = Status_msg::get();
    $expected = array(
      'success' => array('Success message.'),
      'warning' => array(),
      'error' => array()
    );    
    $this->assertEquals($msg, $expected);
    
    // After getting the first get the messages should be cleared. 
    $msg = Status_msg::get();
    $expected = NULL;
    $this->assertEquals($msg, $expected);  
    
	}
}

?>