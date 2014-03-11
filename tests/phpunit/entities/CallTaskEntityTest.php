<?php

class CallTaskEntityTest extends PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    load_entity('call_task');
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid field for the call task: foo
   */
  public function test_construct_call_task_non_existent_prop() {
    $data = array(
      'number' => '1052168465',
      'foo' => 'bar'
    );

    $call_task = new Call_task_entity($data);
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid field for the call task status: foo
   */
  public function test_construct_call_task_status_non_existent_prop() {
    $data = array(
      'number' => '1052168465',
      'activity' => array(
        array(
          'code' => Call_task_status::SUCCESSFUL,
          'foo' => 'bar'
        )
      )
    );

    $call_task = new Call_task_entity($data);
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid code for a call task status: 1
   */
  public function test_build_call_task_status_invalid_code() {
    $call_task_status = Call_task_status::create(1, 'Invalid code.');
  }
  
  public function test_call_task_is_resolved() {    
    // Only set properties needed for testing.
    // 5 no-reply
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
      )
    );
    $call_task = new Call_task_entity($data);
    $this->assertTrue($call_task->is_resolved());
    
    // Only set properties needed for testing.
    // 4 no-reply + successfull
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::SUCCESSFUL,
          'message' => 'A message.'
        ),
      )
    );
    $call_task = new Call_task_entity($data);
    $this->assertTrue($call_task->is_resolved());
    
    // Only set properties needed for testing.
    // 1 cant complete + 4 no-reply + Discard
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::CANT_COMPLETE,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::DISCARD,
          'message' => 'A message.'
        ),
      )
    );
    $call_task = new Call_task_entity($data);
    $this->assertTrue($call_task->is_resolved());
    
    // Only set properties needed for testing.
    // 4 no-reply
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
        array(
          'code' => Call_task_status::NO_REPLY,
          'message' => 'A message.'
        ),
      )
    );
    $call_task = new Call_task_entity($data);
    $this->assertFalse($call_task->is_resolved());
    
    // Only set properties needed for testing.
    // 1 cant complete
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::CANT_COMPLETE,
          'message' => 'A message.'
        )
      )
    );
    $call_task = new Call_task_entity($data);
    $this->assertFalse($call_task->is_resolved());
    
    // Only set properties needed for testing.
    // emoty
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array()
    );
    $call_task = new Call_task_entity($data);
    $this->assertFalse($call_task->is_resolved());
  }

  /**
   * @depends test_call_task_is_resolved
   * @expectedException Exception
   * @expectedExceptionMessage Is not possible to add a new status. The Call Task is resolved.
   */
  public function test_add_status_resolved_call_task() {
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::DISCARD,
          'message' => 'A message.'
        )
      )
    );
    $call_task = new Call_task_entity($data);
    $call_task->add_status(Call_task_status::create(Call_task_status::SUCCESSFUL, 'SUCCESSFUL'));
  }

  public function test_add_status_resolved_call_task_multiple_times() {
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array()
    );
    $call_task = new Call_task_entity($data);
    
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::NO_REPLY, 'NO_REPLY')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::NO_REPLY, 'NO_REPLY')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::NO_REPLY, 'NO_REPLY')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::CANT_COMPLETE, 'CANT_COMPLETE')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::CANT_COMPLETE, 'CANT_COMPLETE')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::CANT_COMPLETE, 'CANT_COMPLETE')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::NO_REPLY, 'NO_REPLY')));
    $this->assertInstanceOf('Call_task_entity', $call_task->add_status(Call_task_status::create(Call_task_status::SUCCESSFUL, 'SUCCESSFUL')));
    
    $this->assertEquals(Call_task_status::SUCCESSFUL, $call_task->activity[7]->code);
    $this->assertEquals('SUCCESSFUL', $call_task->activity[7]->message);
  }

  public function test_call_task_is_assigned() {
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertTrue($call_task->is_assigned());
    $this->assertFalse($call_task->is_assigned(2));
    
    $data = array(
      'number' => '00000000000000',
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_assigned());
    $this->assertFalse($call_task->is_assigned(NULL));  
  }

  /**
   * @depends test_call_task_is_resolved
   */
  public function test_call_task_is_unresolved() {
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => NULL,
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_unresolved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_unresolved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => NULL,
      'activity' => array(
        array(
          'code' => Call_task_status::DISCARD,
          'message' => 'A message.'
        )
      )
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_unresolved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::DISCARD,
          'message' => 'A message.'
        )
      )
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_unresolved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::CANT_COMPLETE,
          'message' => 'A message.'
        )
      )
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertTrue($call_task->is_unresolved());
  }

  public function test_call_task_is_reserved() {
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => NULL,
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_reserved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array(
        array(
          'code' => Call_task_status::CANT_COMPLETE,
          'message' => 'A message.'
        )
      )
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertFalse($call_task->is_reserved());
    
    $data = array(
      'number' => '00000000000000',
      'assignee_uid' => 1,
      'activity' => array()
    );
    
    $call_task = new Call_task_entity($data);    
    $this->assertTrue($call_task->is_reserved());
  }
}
?>