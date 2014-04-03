<?php

require_once ROOT_PATH . "application/controllers/survey.php";

class SurveyRespondentsImportTest extends PHPUnit_Framework_TestCase {
  
  private static $CI;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    // Change Controller.
    self::$CI = new Survey();
    self::$CI->mongo_db->switchDb('mongodb://localhost:27017/aw_datacollection_test');
    self::$CI->config->set_item('aw_survey_files_location', ROOT_PATH . 'tests/test_resources/surveys/');
    
    self::$CI->mongo_db->batchInsert('surveys', array(
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Meteor usage',
        'status' => 1,
        'introduction' => 'The text the user has to read.',
        'files' => array(
          'xls' => NULL, // Not needed
          'xml' => "valid_survey.xml",
          'last_conversion' => array(
            'date' => Mongo_db::date(),
            'warnings' => NULL
          )
        ),
        'agents' => array(3),
        'created' => Mongo_db::date()
      )
    ));
    
    self::$CI->mongo_db->batchInsert('users', array(
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'admin@localhost.dev',
        'name' => 'Admin',
        'username' => 'admin',
        'password' => hash_password('admin'),
        'roles' => array(ROLE_ADMINISTRATOR),
        'author' => null,
        'status' => User_entity::STATUS_ACTIVE,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
    ));
   
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->session->sess_destroy();
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
  }

  /**
   * It's very difficult to test controllers specially this one
   * because it has file upload and form validation.
   * We want to mess with the actual app data as little as possible.
   * 
   * So we are only going to test _survey_respondents_import_check to check
   * if the system correctly identifies dups, invalid and valid numbers.
   * Doing this we assume the following as true:
   * - File is uploaded and stored correctly.
   * - Form validation works as supposed (required values, etc...).
   * - Call tasks are correctly saved.
   * - Status messages are properly set.
   */
   
  /**
   * Direct input testing.
   */
  public function test__survey_respondents_import_check__direct() {
    
    /**
     * 2 valid
     */
    $rows = array();
    $rows[] = '1234';
    $rows[] = '12345';
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '1234',
        '12345'
      ),
      'dups' => array(),
      'invalid' => array()
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    /**
     * 3 invalid
     */
    $rows = array();
    $rows[] = '1889798 548';
    $rows[] = '5484.0';
    $rows[] = '8754984.';    
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(),
      'dups' => array(),
      'invalid' => array(
        '1889798 548',
        '5484.0',
        '8754984.'
      )
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    /**
     * 3 valid
     * 1 dups
     * 5 invalid
     */
    $rows = array();
    $rows[] = '1234';
    $rows[] = '12345';
    $rows[] = '123456';
    $rows[] = '12345';
    $rows[] = '12e4';
    $rows[] = '123409875,2134234';
    $rows[] = '';
    $rows[] = '1889798 548';
    $rows[] = '5484.0';
    $rows[] = '8754984.';    
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '1234',
        '12345',
        '123456'
      ),
      'dups' => array(
        '12345'
       ),
      'invalid' => array(
        '12e4',
        '123409875,2134234',
        '1889798 548',
        '5484.0',
        '8754984.'
      )
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    /**
     * 2 valid
     * 1 dups with file
     * 1 dup with db
     */
    $rows = array();
    $rows[] = '1234';
    $rows[] = '12345';
    $rows[] = '12345';
    $rows[] = '999';
    
    // From DB - Array or call_task_entities
    // Fake data.
    $db_call_tasks = array();
    $db_call_tasks[] = Call_task_entity::build(array('number' => '999'));
    $db_call_tasks[] = Call_task_entity::build(array('number' => '998'));
    $db_call_tasks[] = Call_task_entity::build(array('number' => '997'));
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '1234',
        '12345'
      ),
      'dups' => array(
        '12345',
        '999'
       ),
      'invalid' => array()
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
  }

  /**
   * Direct input testing.
   */
  public function test__survey_respondents_import_check__file() {
    self::$CI->load->helper('csvreader');
    $CSV_path = ROOT_PATH . 'tests/test_resources/respondents_csv/';
    
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file($CSV_path . '10_valid.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '10053',
        '10054',
        '10055',
        '10056',
        '10057',
        '10058',
        '10059',
        '10060',
        '10061',
        '10062'
      ),
      'dups' => array(),
      'invalid' => array()
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file($CSV_path . '6_valid_3_invalid_1_dupe.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '10053',
        '10054',
        '10055',
        '10056',
        '10057',
        '10058',
      ),
      'dups' => array('10053'),
      'invalid' => array(
        '100s9',
        '10o60',
        '1006!',
      )
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file($CSV_path . '8_valid_2_invalid.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(
        '10053',
        '10054',
        '10055',
        '10056',
        '10057',
        '10058',
        '10059',
        '10060'
      ),
      'dups' => array(),
      'invalid' => array(
        '!006!',
        '1oo62'
      )
    );
    
    $this->assertEquals($expect, $respondents);
    
    /* ========================================================= */
    
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file($CSV_path . '10_invalid.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    $expect = array(
      'valid' => array(),
      'dups' => array(),
      'invalid' => array(
        '100ds53',
        '100s4',
        '100ss5',
        'd1d0056',
        '10o57',
        '10d058',
        '10dhj059',
        '10d060',
        '10f061',
        '10s062'
      )
    );
    
    $this->assertEquals($expect, $respondents);
    
  } 
 
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid CSV header. Make sure your column header is "phone_number".
   */
  public function test__survey_respondents_import_check__file_wrong_header() {
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file(ROOT_PATH . 'tests/test_resources/respondents_csv/10_valid_wrong_header.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
    
    /* ========================================================= */
  }
 
  /**
   * @expectedException Exception
   * @expectedExceptionMessage Invalid CSV header. Make sure your column header is "phone_number".
   */
  public function test__survey_respondents_import_check__file_no_header() {
    // Load CSVReader library.
    $csv = new CSVReader();
    $csv->separator = ',';
    $rows = $csv->parse_file(ROOT_PATH . 'tests/test_resources/respondents_csv/10_valid_no_header.csv');
    
    // From DB
    $db_call_tasks = array();
    $respondents = self::$CI->_survey_respondents_import_check($rows, $db_call_tasks);
  } 
}

?>