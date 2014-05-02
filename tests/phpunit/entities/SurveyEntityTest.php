<?php

class Survey_entity_test extends PHPUnit_Framework_TestCase
{
	
	public static function setUpBeforeClass() {
    load_entity('survey');
	}
	
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid field for the survey: foo
   */
	public function test_construct_non_existent_prop() {
	  $data = array(
      'title' => 'A survey with an invalid property.',
      'foo' => 'bar'
    );
    
    $survey = new Survey_entity($data);
	}
	
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Trying to save a file for a non saved survey.
   */
	public function test_save_xls_null_sid() {
	  $data = array(
      'title' => 'A survey',
    );
    
    $survey = new Survey_entity($data);
    $survey->save_xls(NULL);
	}
  
	public function test_valid_survey_status() {
	  $this->assertTrue(Survey_entity::is_valid_status(1));
	  $this->assertTrue(Survey_entity::is_valid_status(2));
	  $this->assertTrue(Survey_entity::is_valid_status(3));
	  $this->assertTrue(Survey_entity::is_valid_status(99));
    
	  $this->assertFalse(Survey_entity::is_valid_status(0));
	  $this->assertFalse(Survey_entity::is_valid_status(4));
	  $this->assertFalse(Survey_entity::is_valid_status(-1));
	  $this->assertFalse(Survey_entity::is_valid_status('Closed'));
	}
  
  public function test_has_files() {
    $data = array(
      'title' => 'A survey',
      'files' => array(
        'xml' => 'survey_1_xml.xml',
        'xls' => 'survey_1_xls.xls'
      )
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertTrue($survey->has_xls());
    $this->assertTrue($survey->has_xml());
    
    $survey->files['xml'] = null;
    $this->assertFalse($survey->has_xml());
  }
  
  /**
   * @depends test_has_files
   */
  public function test_get_file_path() {
    $data = array(
      'author' => 100,
      'title' => 'A survey',
      'files' => array(
        'xml' => 'survey_1_xml.xml',
        'xls' => NULL
      )
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertEquals('survey_1_xml.xml', $survey->get_xml_full_path());
    $this->assertEquals(100, $survey->author);
    $this->assertFalse($survey->get_xls_full_path());
    
    $survey->set_file_location('file/location/');
    $this->assertEquals('file/location/survey_1_xml.xml', $survey->get_xml_full_path());
  }
  
  public function test_is_assigned_agent() {
    $data = array(
      'title' => 'A survey',
      'agents' => array(1, 2, 3)
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertTrue($survey->is_assigned_agent(2));
    $this->assertFalse($survey->is_assigned_agent(4));
  }
  
  /**
   * @depends test_is_assigned_agent
   */
  public function test_assign_agent() {
    $data = array(
      'title' => 'A survey',
      'agents' => array(1, 2, 3)
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertTrue($survey->assign_agent(4));
    // Assigning an already assigned agent.
    $this->assertFalse($survey->assign_agent(1));
  }
  
  /**
   * @depends test_is_assigned_agent
   */
  public function test_unassign_agent() {
    $data = array(
      'title' => 'A survey',
      'agents' => array(1, 2, 3)
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertTrue($survey->unassign_agent(2));
    // Unassigning an non assigned agent.
    $this->assertFalse($survey->unassign_agent(4));
  }
  
  public function test_status_restrictions() {
    // Local vars
    $draft = Survey_entity::STATUS_DRAFT;
    $open = Survey_entity::STATUS_OPEN;
    $closed = Survey_entity::STATUS_CLOSED;
    $canceled = Survey_entity::STATUS_CANCELED;

    $mock_restrictions = array(
      'restriction 1' => array($draft, $open),
      'restriction 2' => array($draft, $open, $closed),
      'restriction 3' => array($draft, $canceled),
      'restriction 4' => array($open)
    );

    $mock_draft = array('status' => $draft);
    $survey_draft = new Survey_entity($mock_draft);
    $survey_draft->set_status_restrictions_array($mock_restrictions);
    
    $this->assertTrue($survey_draft->status_allows('restriction 1'));
    $this->assertTrue($survey_draft->status_allows('restriction 2'));
    $this->assertTrue($survey_draft->status_allows('restriction 3'));
    $this->assertFalse($survey_draft->status_allows('restriction 4'));


    $mock_open = array('status' => $open);
    $survey_open = new Survey_entity($mock_open);
    $survey_open->set_status_restrictions_array($mock_restrictions);
    
    $this->assertTrue($survey_open->status_allows('restriction 1'));
    $this->assertTrue($survey_open->status_allows('restriction 2'));
    $this->assertFalse($survey_open->status_allows('restriction 3'));
    $this->assertTrue($survey_open->status_allows('restriction 4'));


    $mock_canceled = array('status' => $canceled);
    $survey_canceled = new Survey_entity($mock_canceled);
    $survey_canceled->set_status_restrictions_array($mock_restrictions);
    
    $this->assertFalse($survey_canceled->status_allows('restriction 1'));
    $this->assertFalse($survey_canceled->status_allows('restriction 2'));
    $this->assertTrue($survey_canceled->status_allows('restriction 3'));
    $this->assertFalse($survey_canceled->status_allows('restriction 4'));
  }
  
}

?>