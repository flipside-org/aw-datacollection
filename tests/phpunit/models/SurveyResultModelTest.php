<?php

class Survey_result_model_test extends PHPUnit_Framework_TestCase {
  
  private static $CI;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    
    // Load model
    self::$CI->load->model('survey_result_model');
    
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    // Some data!
    $fixture = array(
      array(
        'srid' => 100,
        'call_task_ctid' => 1,
        'survey_sid' => 1,
        'files' => array(
          'xml' => 'file_location.xml'
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'author' => 1
      ),
      array(
        'srid' => 101,
        'call_task_ctid' => 1,
        'survey_sid' => 1,
        'files' => array(
          'xml' => 'file_location.xml'
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'author' => 1
      )
    );
    
    self::$CI->mongo_db->batchInsert(Survey_result_model::COLLECTION, $fixture);
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
  }
  
  public function test_get_one() {
    $result_one = self::$CI->survey_result_model->get(100);
    
    $this->assertInstanceOf('Survey_result_entity', $result_one);
    $this->assertEquals(100, $result_one->srid);
    
    $result_two = self::$CI->survey_result_model->get('abc');
    $this->assertFalse($result_two);
  }
  
  public function test_insert_survey_result() {
    
    $mock_data = array(
      'files' => array('xml' => 'the_file_loc.xml')
    );
    
    $insert = new Survey_result_entity($mock_data);
    
    // Save method will call increment_counter()
    // but since the database was cleared, the returned value will be 1.
    // The $insert survey is passed by reference and the sid will be added.
    $result = self::$CI->survey_result_model->save($insert);
    
    $this->assertTrue($result);
    $this->assertEquals(1, $insert->srid);
    
    $query_survey = self::$CI->survey_result_model->get(1);
    $this->assertEquals('the_file_loc.xml', $insert->files['xml']);
  }
}

?>