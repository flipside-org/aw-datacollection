<?php

class Status_msg_helper_test extends PHPUnit_Framework_TestCase
{
  
  public static function setUpBeforeClass() {
  }
  
  public function test_data_storage(){
    Status_msg::success('Success message.');
    Status_msg::error('Error message.');
    Status_msg::warning('Warning message.', TRUE);
    // Execution is fast enough that the time won't vary.
    $time = time();
    
    $msg = Status_msg::get();
    $expected = array(
      array(
        'level' => 'success',
        'msg' => 'Success message.',
        'sticky' => FALSE,
        'time' => $time,
      ),
      array(
        'level' => 'error',
        'msg' => 'Error message.',
        'sticky' => TRUE,
        'time' => $time,
      ),
      array(
        'level' => 'warning',
        'msg' => 'Warning message.',
        'sticky' => TRUE,
        'time' => $time,
      )
    );    
    $this->assertEquals($msg, $expected);
    
    // After getting the first get the messages should be cleared. 
    $msg = Status_msg::get();
    $expected = NULL;
    $this->assertEquals($msg, $expected);  
    
  }
}

?>