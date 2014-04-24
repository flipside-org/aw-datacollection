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
    
    // Create temp directory.
    $path = ROOT_PATH . 'tests/tmp/survey_results/';
    if (!file_exists($path)) mkdir($path, 0777, true);
    self::$CI->config->set_item('aw_survey_results_location', $path);
    
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
        'agents' => array(),
        'created' => Mongo_db::date(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Cat ladies around the neighborhood',
        'status' => 2,
        'introduction' => 'The text the user has to read.',
        'files' => array(
          'xls' => NULL, // Not needed
          'xml' => "valid_survey.xml",
          'last_conversion' => array(
            'date' => Mongo_db::date(),
            'warnings' => NULL
          )
        ),
        'agents' => array(1, 2, 3),
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
      array(
        'uid' => increment_counter('user_uid'),
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
        'uid' => increment_counter('user_uid'),
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
        'uid' => increment_counter('user_uid'),
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
        'survey_sid' => 1,
        'activity' => array()
      );
    }
    self::$CI->mongo_db->batchInsert('call_tasks', $respondents);
   
  }
  
  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->session->sess_destroy();
    session_destroy();
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
    self::$CI->session->set_userdata(array('user_uid' => 3));
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
    // User is agent.
    // User is assigned to survey.
    self::$CI->session->set_userdata(array('user_uid' => 3));
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
    
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    // User is administrator.
    // User is not assigned to survey.
    // All assigned users must be able to get the file.
    // All unassigned users with testrun any permission must be able
    // to get the file.
    self::$CI->api_survey_xslt_transform(3);
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
    self::$CI->session->set_userdata(array('user_uid' => 3));
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
    self::$CI->api_survey_enketo_form_submit(1);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }
  
  public function test_api_survey_enketo_form_submit_logged_in() {
    // Some vars to help:
    // A call center agent.
    $agent = 3;
    // Another user.
    $other_user = 1;
    // A survey to which $agent is assigned
    $working_survey = 1;
    // Another survey.
    $other_survey = 2;
    
    // Login user.
    // User 3 is our call center agent.
    self::$CI->session->set_userdata(array('user_uid' => 3));
    // Force user reloading.
    current_user(TRUE);
    
    /*************************************************************************/
    
    // Call task doesn't exist.
    // User is assigned to survey.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array('ctid' => 999)
    );
    self::$CI->api_survey_enketo_form_submit(1);
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
        'survey_sid' => $working_survey,
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User not assigned to call task.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1002,
        'number' => "110020000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => $other_user,
        'survey_sid' => $other_survey,
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 201, 'message' => 'Submitting data for another user.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1003,
        'number' => "110030000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => $agent,
        'survey_sid' => 3,
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 201, 'message' => 'Submitting data for another survey.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1004,
        'number' => "1100400000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => $agent,
        'survey_sid' => 2,
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
    self::$CI->api_survey_enketo_form_submit(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1005,
        'number' => "1100500000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => $agent,
        'survey_sid' => $working_survey,
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Missing data form_data and new_status.'), $result['status']);
    
    /*************************************************************************/
    
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /*************************************************************************/
    
    // Adding Successful status.
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Successful status can not be set manually.'), $result['status']);
    
    /*************************************************************************/
    
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
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1006,
        'number' => "110060000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => $agent,
        'survey_sid' => $working_survey,
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
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1006,
        'new_status' => array(
          'code' => Call_task_status::DISCARD,
          'msg' => 'Adding valid status.'
        )
      )
    );
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid call task status.'), $result['status']);
    
    /*************************************************************************/
    
    // Submitting data for an already resolved call task.
    $_POST = array(
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
      'respondent' => array(
        'ctid' => 1006,
        'form_data' => '<valid><tag/></valid>'
      )
    );
    self::$CI->api_survey_enketo_form_submit($working_survey);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Trying to submit data for a resolved call task.'), $result['status']);
    
    /*************************************************************************/

  }

  public function test_api_survey_manage_agents_logged_out() {
    // Logout user
    self::$CI->session->set_userdata(array('user_uid' => NULL));
    // Force user reloading.
    current_user(TRUE);
    
    // Not logged
    self::$CI->api_survey_manage_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 403, 'message' => 'Not allowed.'), $result['status']);
  }

  public function test_api_survey_manage_agents() {
    // Login user.
    self::$CI->session->set_userdata(array('user_uid' => 1));
    // Force user reloading.
    current_user(TRUE);
    
    // Missing user id.
    $_POST = array(
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /*************************************************************************/
    
    // Non existent user and survey.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(999);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid survey.'), $result['status']);
    
    /*************************************************************************/
    
    // Non existent user.
    $_POST = array(
      'uid' => 999,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /*************************************************************************/
    
    // User is not an agent.
    $_POST = array(
      'uid' => 2,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'User is not an agent.'), $result['status']);
    
    /*************************************************************************/
    
    // User is not an agent.
    // Action unassign
    $_POST = array(
      'uid' => 2,
      'action' => 'unassign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    /*************************************************************************/
    
    // Assign Ok!.
    $_POST = array(
      'uid' => 3,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
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
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 200, 'message' => 'Ok!'), $result['status']);
    
    $survey = self::$CI->survey_model->get(2);
    $this->assertEmpty($survey->agents);
    
    /*************************************************************************/
    
    // Assigning a blocked agent.
    $_POST = array(
      'uid' => 4,
      'action' => 'assign',
      'csrf_aw_datacollection' => self::$CI->security->get_csrf_hash(),
    );
    
    self::$CI->api_survey_manage_agents(2);
    $result = json_decode(self::$CI->output->get_output(), TRUE);
    $this->assertEquals(array('code' => 500, 'message' => 'Invalid user.'), $result['status']);
    
    /*************************************************************************/
    
  }

  public function test_api_survey_enketo_form_submit_data_logged_in() {
    // Login user.
    // User 3 is our call center agent.
    self::$CI->session->set_userdata(array('user_uid' => 3));
    // Force user reloading.
    current_user(TRUE);
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1007,
        'number' => "110070000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 3,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
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
    
    /*************************************************************************/
    
    self::$CI->mongo_db->insert('call_tasks', array(
        'ctid' => 1008,
        'number' => "110080000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 3,
        'survey_sid' => 1,
        'activity' => array()
      )
    );
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
    
    /*************************************************************************/
    
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
          'assignee_uid' => 3,
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
      $this->assertEquals(3, $survey_result->author);
      $this->assertEquals(1010 + $key, $survey_result->call_task_ctid);
      $this->assertEquals(1, $survey_result->survey_sid);
      // Filename pattern survey_result_[srid]_[ctid]_[sid].xml
      $filename = sprintf('survey_result_%d_%d_%d.xml', $key + 1, $survey_result->call_task_ctid, 1);
      $this->assertEquals($filename, $survey_result->files['xml']);
    }
    
    /*************************************************************************/
  }
}

?>