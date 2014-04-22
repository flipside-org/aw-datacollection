<?php

class SurveyResultEntityTest extends PHPUnit_Framework_TestCase
{
	
	public static function setUpBeforeClass() {
    load_entity('survey_result');
	}
	
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid field for the survey result: foo
   */
	public function test_construct_non_existent_prop() {
	  $data = array(
      'srid' => 1,
      'foo' => 'bar'
    );
    
    $survey_result = new Survey_result_entity($data);
	}
	
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Trying to save a file for a non saved survey result.
   */
	public function test_save_xls_null_sid() {
	  $data = array(
      'surv' => 1,
    );
    
    $survey_result = new Survey_result_entity(array());
    $survey_result->save_xml(NULL);
	}

  public function test_get_file_path() {
    $data = array(
      'srid' => 1,
      'files' => array(
        'xml' => 'survey_result_1_1_1.xml'
      )
    );
    
    $survey_result = new Survey_result_entity($data);
    
    $this->assertEquals('survey_result_1_1_1.xml', $survey_result->get_xml_full_path());
    
    $survey_result->set_file_location('file/location/');
    $this->assertEquals('file/location/survey_result_1_1_1.xml', $survey_result->get_xml_full_path());
  }
  
}

?>