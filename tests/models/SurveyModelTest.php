<?php

class Survey_model_test extends PHPUnit_Framework_TestCase
{
  
  private static $CI;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    // Some data!
    $fixture = array(
      array(
        'sid' => 1,
        'title' => 'Meteor usage',
        'status' => 1,      
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,        
          'last_conversion' => array(
            'date' => 1390493562,
            'warnings' => NULL
          )
        ),      
        'created' => Mongo_db::date()
      ),
      array(
        'sid' => 123456,
        'title' => 'Second survey',
        'status' => 99,      
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,        
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),      
        'created' => Mongo_db::date()
      )
    );
    
    
    self::$CI->mongo_db->batchInsert('surveys', $fixture);
    
    // Load model
    self::$CI->load->model('survey_model');
    
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
  }
  
  public function test_get_all_surveys() {
    $all_surveys = self::$CI->survey_model->get_all();
    
    $this->assertCount(2, $all_surveys);
    $this->assertContainsOnlyInstancesOf('Survey_entity', $all_surveys);
    
  }
  
  public function test_get_one_surveys() {
    $sid = 1;
    $survey_one = self::$CI->survey_model->get($sid);
    
    $this->assertInstanceOf('Survey_entity', $survey_one);
    $this->assertEquals(1, $survey_one->sid);
    
    $survey_two = self::$CI->survey_model->get('abc');
    $this->assertFalse($survey_two);
  }

  /**
   * @depends test_get_all_surveys
   * @depends test_get_one_surveys
   */
  public function test_delete_one_survey() {
    $sid = 1;
    self::$CI->survey_model->delete($sid);
    
    $survey_one = self::$CI->survey_model->get($sid);
    $this->assertFalse($survey_one);
  }
  
  /**
   * @depends test_delete_one_survey
   */
  public function test_insert_survey() {
    
    $mock_data = array(
      'title' => 'test_insert_survey',
      'status' => 1,
    );
    
    $insert_survey = new Survey_entity($mock_data);
    
    // Save method will call increment_counter()
    // but since the database was cleared, the returned value will be 1.
    // The $insert survey is passed by reference and the sid will be added.
    $result = self::$CI->survey_model->save($insert_survey);
    
    $this->assertTrue($result);
    $this->assertEquals(1, $insert_survey->sid);
    
    $query_survey = self::$CI->survey_model->get(1);    
    $this->assertEquals('test_insert_survey', $query_survey->title);
  }
  
}

?>