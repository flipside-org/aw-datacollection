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
      'title' => 'A survey',
      'files' => array(
        'xml' => 'survey_1_xml.xml',
        'xls' => NULL
      )
    );
    
    $survey = new Survey_entity($data);
    
    $this->assertEquals('survey_1_xml.xml', $survey->get_xml_full_path());
    $this->assertFalse($survey->get_xls_full_path());
    
    $survey->set_file_location('file/location/');
    $this->assertEquals('file/location/survey_1_xml.xml', $survey->get_xml_full_path());
  }
  
}

?>