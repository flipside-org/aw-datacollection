<?php

require_once ROOT_PATH . "application/controllers/survey.php";

class SurveyEnketoApiTest extends PHPUnit_Framework_TestCase {
  
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
        'created' => Mongo_db::date()
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Handlebars vs something else',
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
        'created' => Mongo_db::date(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Cat ladies around the neighborhood',
        'status' => 2,
        'introduction' => 'The text the user has to read.',
        'files' => array(
          'xls' => NULL,
          'xml' => NULL,
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
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
        'status' => 2,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'regular@localhost.dev',
        'name' => 'Regular user',
        'username' => 'regular',
        'password' => hash_password('regular'),
        'roles' => array(),
        'author' => 1,
        'status' => 2,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'agent@localhost.dev',
        'name' => 'The Agent',
        'username' => 'agent',
        'password' => hash_password('agent'),
        'roles' => array(ROLE_CC_AGENT),
        'author' => 1,
        'status' => 2,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    ));
    
    self::$CI->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    
    // Add some respondents to be used for data collection.
    $respondents = array();
    for($r = 3; $r < 100; $r++) {
      $respondents[] =  array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => (string)(1000000000000 + $r),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => NULL,
        'author' => 1,
        'assignee_uid' => NULL,
        'survey_sid' => 2,
        'activity' => array()
      );
    }
    self::$CI->mongo_db->batchInsert('call_tasks', $respondents);
   
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->session->sess_destroy();
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
  }

  public function test_api_survey_request_csrf_token() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged user.
    self::$CI->api_survey_request_csrf_token();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);

    self::$CI->api_survey_request_csrf_token();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('csrf', $result);
  }

  public function test_api_survey_xslt_transform() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged
    self::$CI->api_survey_xslt_transform(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    // Invalid survey
    self::$CI->api_survey_xslt_transform(100);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 404, 'message' => 'Invalid survey.'), $result['status']);
    
    self::$CI->api_survey_xslt_transform(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('xml_form', $result);
  }
  
  public function test_api_survey_request_respondents() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged
    self::$CI->api_survey_request_respondents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    // Invalid survey
    self::$CI->api_survey_request_respondents(100);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 404, 'message' => 'Invalid survey.'), $result['status']);
    
    self::$CI->api_survey_request_respondents(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    $this->assertArrayHasKey('respondents', $result);
  }
  
  public function test_api_survey_enketo_form_submit_logged_out() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }
  
  public function test_api_survey_enketo_form_submit_logged_in() {   
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    /*************************************************************************/
    
    // Call task doesn't exist.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 999)
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1001,
        'number' => "1100100000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => NULL,
        'author' => 1,
        'assignee_uid' => NULL,
        'survey_sid' => 2,
        'activity' => array()
      )
    );    
    // TODO: User 1 assigned to survey.
    // User not assigned to call task.
    // The assigned user here is 3
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 1001)
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User not assigned to call task.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1002,
        'number' => "1100200000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 1,
        'survey_sid' => 2,
        'activity' => array()
      )
    );   
    // Call task not assigned to survey.
    $_POST = array(
      'sid' => 1,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 1002)
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Call task not assigned to survey.'), $result['status']);
    
    /*************************************************************************/
    
    // Missing data form_data and new_status.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1002
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Missing data form_data and new_status.'), $result['status']);
    
    /*************************************************************************/
    
    // Invalid call task status.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1002,
        'new_status' => array(
          'code' => 12345,
          'msg' => 'Adding invalid status'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /*************************************************************************/
    
    // Adding Successful status.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1002,
        'new_status' => array(
          'code' => Call_task_status::SUCCESSFUL,
          'msg' => 'Adding valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Successful status can not be set manually.'), $result['status']);
    
    /*************************************************************************/
    
    // Adding Invalid number status.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1002,
        'new_status' => array(
          'code' => Call_task_status::INVALID_NUMBER,
          'msg' => 'Adding a valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1003,
        'number' => "110030000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 1,
        'survey_sid' => 2,
        'activity' => array(
          array(
            'code' => Call_task_status::NO_CONSENT,
            'message' => NULL,
            'author' => 1,
            'created' => Mongo_db::date()
          )
        )
      )
    );   
    // Adding Discard status to an already resolved call task
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1003,
        'new_status' => array(
          'code' => Call_task_status::DISCARD,
          'msg' => 'Adding valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /*************************************************************************/
    
    // Submitting data for an already resolved call task.
    // TODO: Review once implemented data save method.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1003,
        'form_data' => 'the data'
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Trying to submit data for a resolved call task.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1004,
        'number' => "110040000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 3,
        'survey_sid' => 2,
        'activity' => array()
      )
    ); 
    // Submitting data for another user.
    $_POST = array(
      'sid' => 2,
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1004,
        'form_data' => 'the data'
      )
    );
    self::$CI->api_survey_enketo_form_submit();
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 201, 'message' => 'Submitting data for another user.'), $result['status']);
    
    /*************************************************************************/
  }

  public function test_api_survey_assign_agents_logged_out() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged
    self::$CI->api_survey_assign_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }

  public function test_api_survey_assign_agents() {
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    // Missing user id.
    $_POST = array(
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /*************************************************************************/
    
    // Non existent user and survey.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid survey.'), $result['status']);
    
    /*************************************************************************/
    
    // Non existent user.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /*************************************************************************/
    
    // User is not an agent.
    $_POST = array(
      'uid' => 2,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User is not an agent.'), $result['status']);
    
    /*************************************************************************/
    
    // Assign Ok!.
    $_POST = array(
      'uid' => 3,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    $survey = self::$CI->survey_model->get(2);
    $this->assertEquals(array(3), $survey->agents);
    
    /*************************************************************************/
    
    // Unassign Ok!.
    $_POST = array(
      'uid' => 3,
      'action' => 'unassign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_assign_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    $survey = self::$CI->survey_model->get(2);
    $this->assertEmpty($survey->agents);
    
    /*************************************************************************/
    
  }
}

?>