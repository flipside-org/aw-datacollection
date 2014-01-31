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
  
}

?>