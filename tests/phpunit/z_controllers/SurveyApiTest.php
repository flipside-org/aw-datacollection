<?php

require_once ROOT_PATH . "application/controllers/survey.php";

class SurveyApiTest extends PHPUnit_Framework_TestCase {
  
  private static $CI;
  
  private static $status_resctriction_config;
  
  public static function setUpBeforeClass() {
    self::$CI =& get_instance();
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    // Change Controller.
    self::$CI = new Survey();
    self::$CI->mongo_db->switchDb('mongodb://localhost:27017/aw_datacollection_test');
    self::$CI->config->set_item('aw_survey_files_location', ROOT_PATH . 'tests/test_resources/surveys/');
    
    self::$CI->load->helper('password_hashing');
    
    // Original status restriction.
    self::$CI->config->load('status_restrictions');
    self::$status_resctriction_config = self::$CI->config->item('status_restrictions');
    
    // Create temp directory.
    $path = ROOT_PATH . 'tests/tmp/survey_results/';
    if (!file_exists($path)) mkdir($path, 0777, true);
    self::$CI->config->set_item('aw_survey_results_location', $path);
    
    // Index.
    self::$CI->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    
    // Instead of creating all the content before starting the tests
    // we only create users since those will not be updated.
    // Every other content will be created when needed.
    // This allows more control over what's happening.
    self::$CI->mongo_db->batchInsert('users', array(
      array(
        'uid' => 9901,
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
      array(
        'uid' => 9902,
        'email' => 'regular@localhost.dev',
        'name' => 'Regular user',
        'username' => 'regular',
        'password' => hash_password('regular'),
        'roles' => array(),
        'author' => 1,
        'status' => User_entity::STATUS_ACTIVE,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => 9903,
        'email' => 'agent@localhost.dev',
        'name' => 'The Agent',
        'username' => 'agent',
        'password' => hash_password('agent'),
        'roles' => array(ROLE_CC_AGENT),
        'author' => 1,
        'status' => User_entity::STATUS_ACTIVE,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => 9904,
        'email' => 'blocked_agent@localhost.dev',
        'name' => 'The Blocked Agent',
        'username' => 'bloked_agent',
        'password' => hash_password('blocked_agent'),
        'roles' => array(ROLE_CC_AGENT),
        'author' => 1,
        'status' => User_entity::STATUS_BLOCKED,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    ));
   
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->session->sess_destroy();
    session_destroy();
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
  }
  
  /**
   * Helper function to change logged in user.
   */
  public function _change_user($uid) {
    // Change user.
    self::$CI->session->set_userdata(array('user_uid' => $uid));
    // Force user reloading.
    current_user(TRUE);
  }
  
  /**
   * Helper function to reset the status restriction.
   */
  public function _reset_status_restrictions() {
    $this->_set_status_restrictions(self::$status_resctriction_config);
  }
  
  /**
   * Helper function to set custom status restriction.
   */
  public function _set_status_restrictions($mock_config) {
    self::$CI->config->set_item('status_restrictions', $mock_config);
  }
  
  //////////////////////////////////////////////////////////////////////////
  // Let the tests begin.
  //////////////////////////////////////////////////////////////////////////
  // These tests are meant to test the controller's api methods.
  // Except when specified the system relies on the default
  // permissions and status restrictions.
  // There's no special permission files only for testing.
  
  public function test_api_survey_request_csrf_token() {
    // Logout user
    $this->_change_user(NULL);
    
    // Not logged user.
    self::$CI->api_survey_request_csrf_token();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    // Login user.
    // Agent
    $this->_change_user(9903);

    self::$CI->api_survey_request_csrf_token();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('csrf', $result);
  }

  public function test_api_survey_xslt_transform() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    
    
    // Logout user
    $this->_change_user(NULL);
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    // Not logged
    self::$CI->api_survey_xslt_transform(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    
    // Login user.
    // User is agent.
    // User is assigned to survey.
    $this->_change_user(9903);
    
    // Invalid survey
    self::$CI->api_survey_xslt_transform(100);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 404, 'message' => 'Invalid survey.'), $result['status']);
    
    
    // Logged user is 9903
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    // User is assigned to survey.
    $survey = Survey_entity::build(array(
      'sid' => 2,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    self::$CI->api_survey_xslt_transform(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
    
    
    // Logged user is 9903
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    // User is not assigned to survey.
    $survey = Survey_entity::build(array(
      'sid' => 3,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    self::$CI->api_survey_xslt_transform(3);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
    
    
    // Login user.
    $this->_change_user(9901);
    
    // Create survey.
    // Status open.
    // Valid xml file.
    // User is not assigned to survey.
    $survey = Survey_entity::build(array(
      'sid' => 4,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    // User is administrator.
    // User is not assigned to survey.
    // All unassigned users with testrun any permission must be able
    // to get the file.
    self::$CI->api_survey_xslt_transform(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
  }

  public function test_api_survey_request_respondents() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();
    
    
    // Logout user
    $this->_change_user(NULL);
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    // Not logged
    self::$CI->api_survey_request_respondents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    
    // Login user.
    // User is agent.
    // User is assigned to survey.
    $this->_change_user(9903);
    
    // Invalid survey
    self::$CI->api_survey_request_respondents(100);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 404, 'message' => 'Invalid survey.'), $result['status']);
    
    
    // Logged user is 9903
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    // User is assigned to survey.
    $survey = Survey_entity::build(array(
      'sid' => 2,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    // Even though we are requesting respondents there's no need for
    // respondents in the database.    
    self::$CI->api_survey_request_respondents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('respondents', $result);
    
    
    // Logged user is 9903
    // User is agent.
    
    // Create survey.
    // Status canceled.
    // Valid xml file.
    // User is assigned to survey.
    // Not possible to request respondents for canceled surveys.
    $survey = Survey_entity::build(array(
      'sid' => 3,
      'status' => Survey_entity::STATUS_CANCELED,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    // Even though we are requesting respondents there's no need for
    // respondents in the database.
    self::$CI->api_survey_request_respondents(3);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }
  
  public function test_api_survey_enketo_form_submit_logged_out() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();
    
    
    // Logout user
    $this->_change_user(NULL);
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    // Not logged
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }
  
  public function test_api_survey_enketo_form_submit_logged_in() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();

    
    // Create survey.
    // Status open.
    // Valid xml file.
    // This survey will not be changed and can be used in several tests.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    

    // Login user.
    // User is agent.
    $this->_change_user(9903);
    
    // Call task doesn't exist.
    // User is assigned to survey.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 999)
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task.'), $result['status']);

    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1001,
        'number' => "1100100000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => NULL,
        'author' => 1,
        'assignee_uid' => NULL,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
    // Submitting data for a non reserved call task.
    // User 3 assigned to survey.
    // User not assigned to call task.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 1001)
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User not assigned to call task.'), $result['status']);

    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1002,
        'number' => "110020000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9901,
        'survey_sid' => 1,
        'activity' => array()
      )
    ); 
    // Submitting data for another user.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1002,
        'form_data' => 'the data'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 201, 'message' => 'Submitting data for another user.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 2,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1003,
        'number' => "110030000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 2,
        'activity' => array()
      )
    ); 
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is not the one data is being submitted for.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1003,
        'form_data' => 'the data'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 201, 'message' => 'Submitting data for another survey.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 3,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1004,
        'number' => "1100400000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 3,
        'activity' => array()
      )
    );
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is NOT assigned to survey.
    // Survey is the one data is being submitted for.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 1004)
    );
    self::$CI->api_survey_enketo_form_submit(3);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.

    // This next survey and call task will be used in several assertions.
    // We are testing the addition of statuses and since the tests
    // only continue if they don't fail it's ok to do this.
        
    // Create survey.
    // Status open.
    // Valid xml file.
    // Agent 9903 assigned
    $survey = Survey_entity::build(array(
      'sid' => 4,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1005,
        'number' => "1100500000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 4,
        'activity' => array()
      )
    );
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Missing data form_data and new_status.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Missing data form_data and new_status.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 4, open
    // Call task 1005, no activity.
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Invalid call task status.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005,
        'new_status' => array(
          'code' => 12345,
          'msg' => 'Adding invalid status'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 4, open
    // Call task 1005, no activity.
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Adding successful status.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005,
        'new_status' => array(
          'code' => Call_task_status::SUCCESSFUL,
          'msg' => 'Adding valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Successful status can not be set manually.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 4, open
    // Call task 1005, no activity.
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Adding Invalid number status.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005,
        'new_status' => array(
          'code' => Call_task_status::INVALID_NUMBER,
          'msg' => 'Adding a valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 4, open
    // Call task 1005, with activity from last assertion (Resolved)
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Adding Invalid number status.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005,
        'new_status' => array(
          'code' => Call_task_status::INVALID_NUMBER,
          'msg' => 'Adding a valid status to a resolved activity.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 4, open
    // Call task 1005, with activity from last assertion (Resolved)
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Submitting data for an already resolved call task.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1005,
        'form_data' => '<valid><tag/></valid>'
      )
    );
    self::$CI->api_survey_enketo_form_submit(4);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Trying to submit data for a resolved call task.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////

  }
  
  public function test_api_survey_enketo_form_submit_data_logged_in() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();

    
    // Create survey.
    // Status open.
    // Valid xml file.
    // This survey will not be changed and can be used in several tests.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);

    // Login user.
    // User is agent.
    $this->_change_user(9903);

    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 1, open
    
    // Create call task, unresolved
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1007,
        'number' => "110070000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Submitting invalid data.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1007,
        'form_data' => '<invalid xml data'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid data.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 1, open
    
    // Create call task, unresolved
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1008,
        'number' => "110080000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    // Submitting xml bomb. Just because it funny.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1008,
        'form_data' => '<?xml version="1.0"?>
          <!DOCTYPE lolz [
           <!ENTITY lol "lol">
           <!ELEMENT lolz (#PCDATA)>
           <!ENTITY lol1 "&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;">
           <!ENTITY lol2 "&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;&lol1;">
           <!ENTITY lol3 "&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;">
           <!ENTITY lol4 "&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;&lol3;">
           <!ENTITY lol5 "&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;&lol4;">
           <!ENTITY lol6 "&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;&lol5;">
           <!ENTITY lol7 "&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;&lol6;">
           <!ENTITY lol8 "&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;&lol7;">
           <!ENTITY lol9 "&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;&lol8;">
          ]>
          <lolz>&lol9;</lolz>'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid data.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9903.
    // User is agent.
    
    // Survey 1, open
    
    // Test with different examples.
    $enketo_data_example = file_get_contents(ROOT_PATH . 'tests/test_resources/survey_data_enketo_submit');
    $enketo_data_example = explode(str_repeat('=', 60), $enketo_data_example);
    
    // Drop the survey result collection and reset the counter to 
    // ensure consistency.
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', Survey_result_model::COLLECTION);
    reset_counter(Survey_result_model::COUNTER_COLLECTION);
    
    foreach($enketo_data_example as $key => $enketo_example) {
      self::$CI->mongo_db->insert('call_tasks', array(
          'ctid' => 1010 + $key,
          'number' => "11000000000" . $key,
          'created' => Mongo_db::date(),
          'updated' => Mongo_db::date(),
          'assigned' => Mongo_db::date(),
          'author' => 1,
          'assignee_uid' => 9903,
          'survey_sid' => 1,
          'activity' => array()
        )
      );
      // Submitting valid data.
      $_POST = array(
        'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
        'respondent' => array(
          'ctid' => 1010 + $key,
          'form_data' => $enketo_example
        )
      );
      self::$CI->api_survey_enketo_form_submit(1);
      $result = json_decode(self::$CI->output->get_output(), TRUE);
      $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status'], 'Failed on item ' . $key);
      
      // Verify data in the database.
      // The counter was reset we can assume $key + 1.
      $survey_result = self::$CI->survey_result_model->get($key + 1);
      // Test
      $this->assertEquals(9903, $survey_result->author);
      $this->assertEquals(1010 + $key, $survey_result->call_task_ctid);
      $this->assertEquals(1, $survey_result->survey_sid);
      // Filename pattern survey_result_[srid]_[ctid]_[sid].xml
      $filename = sprintf('survey_result_%d_%d_%d.xml', $key + 1, $survey_result->call_task_ctid, 1);
      $this->assertEquals($filename, $survey_result->files['xml']);
    }
    
    /////////////////////////////////////////////////////////////////
  }

  public function test_api_survey_manage_agents_logged_out() {
    // Logout user
    $this->_change_user(NULL);
    
    // Not logged
    self::$CI->api_survey_manage_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }

  public function test_api_survey_manage_agents() {
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();
    
    
    // Login user.
    // User is administrator
    $this->_change_user(9901);
    
    // Create survey.
    // Status canceled.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      )
    ));
    self::$CI->survey_model->save($survey);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // Missing user id.
    $_POST = array(
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // Non existent user and survey.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid survey.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // Non existent user.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // User is not an agent.
    $_POST = array(
      'uid' => 9902,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User is not an agent.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // User is not an agent.
    // Action unassign
    $_POST = array(
      'uid' => 9902,
      'action' => 'unassign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // Assign Ok!.
    $_POST = array(
      'uid' => 9903,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    $survey = self::$CI->survey_model->get(1);
    $this->assertEquals(array(9903), $survey->agents);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    // User 9903 is assigned from previous assertion.
    
    // Unassign Ok!.
    $_POST = array(
      'uid' => 9903,
      'action' => 'unassign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    $survey = self::$CI->survey_model->get(1);
    $this->assertEmpty($survey->agents);
    
    /////////////////////////////////////////////////////////////////
    
    // Logged user 9901.
    // User is administrator.
    
    // Survey 1, open
    
    // Assigning a blocked agent.
    $_POST = array(
      'uid' => 9904,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
  }

  public function test_api_survey_with_status_restrictions() {
    // Here we are testing all the API but only for status restrictions.
    // Every other test case should be tested elsewhere.
    
    // Cleanup
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'surveys');
    self::$CI->mongo_db->dropCollection('aw_datacollection_test', 'call_tasks');
    $this->_reset_status_restrictions();
    
    // Shorter statuses.
    $draft = Survey_entity::STATUS_DRAFT;
    $open = Survey_entity::STATUS_OPEN;
    $closed = Survey_entity::STATUS_CLOSED;
    $canceled = Survey_entity::STATUS_CANCELED;
    
    
    // Login user
    $this->_change_user(9903);
    
    /////////////////////////////////////////////////////////////////
    
    // Set actions to be allowed only in Draft status.
    $mock_config = self::$status_resctriction_config;
    $mock_config['enketo collect data'] = array(Survey_entity::STATUS_DRAFT);
    $mock_config['enketo testrun'] = array(Survey_entity::STATUS_DRAFT);
    $this->_set_status_restrictions($mock_config);
    
    // Logged user is 9903
    // User is agent.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    // User is assigned to survey.
    $survey = Survey_entity::build(array(
      'sid' => 1,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array(9903)
    ));
    self::$CI->survey_model->save($survey);
    
    // Create call task
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1001,
        'number' => "1100500000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 9903,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
    
    self::$CI->api_survey_xslt_transform(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
    
    self::$CI->api_survey_request_respondents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1001,
        'form_data' => '<valid><tag/></valid>'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    // Test again with correct status restrictions.
    $mock_config = self::$status_resctriction_config;
    $mock_config['enketo collect data'] = array(Survey_entity::STATUS_OPEN);
    $mock_config['enketo testrun'] = array(Survey_entity::STATUS_OPEN);
    $this->_set_status_restrictions($mock_config);
    
    
    self::$CI->api_survey_xslt_transform(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
    
    self::$CI->api_survey_request_respondents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    // User assigned to call task.
    // Call task is assigned to survey.
    // User is assigned to survey.
    // Survey is the one data is being submitted for.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1001,
        'form_data' => '<valid><tag/></valid>'
      )
    );
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    // To test the manage agents api we need an admin.
    
    $this->_change_user(9901);
    // Logged user 9901.
    // User is administrator.
    
    // Create survey.
    // Status open.
    // Valid xml file.
    $survey = Survey_entity::build(array(
      'sid' => 2,
      'status' => Survey_entity::STATUS_OPEN,
      'files' => array(
        'xml' => 'valid_survey.xml'
      ),
      'agents' => array()
    ));
    self::$CI->survey_model->save($survey);
    
    // Create new agent.
    // Absolute minimum properties for the test.
    $user_agent = User_entity::build(array(
      'uid' => 8801,
      'status' => User_entity::STATUS_ACTIVE,
      'roles' => array(ROLE_CC_AGENT)
    ));
    self::$CI->user_model->save($user_agent);
    
    // Set conditions.
    $mock_config = self::$status_resctriction_config;
    $mock_config['manage agents'] = array(Survey_entity::STATUS_DRAFT);
    $this->_set_status_restrictions($mock_config);
    
    // User is an agent.
    // Action assign
    $_POST = array(
      'uid' => 8801,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    /////////////////////////////////////////////////////////////////
    
    // Set conditions.
    $mock_config = self::$status_resctriction_config;
    $mock_config['manage agents'] = array(Survey_entity::STATUS_OPEN);
    $this->_set_status_restrictions($mock_config);
    
    // User is an agent.
    // Action assign
    $_POST = array(
      'uid' => 8801,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
  }

}
?>