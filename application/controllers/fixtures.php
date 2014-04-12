<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fixtures extends CI_Controller {
  
  function __construct() {
    parent::__construct();
    $this->_env_check();
    $this->load->helper('password_hashing');
  }

  public function index() {

    echo '<h1>Set fixtures:<h1>';
    echo anchor('fixtures/all', 'All') . '<br/>';
    echo anchor('fixtures/surveys', 'Surveys') . '<br/>';
    echo anchor('fixtures/users', 'Users') . '<br/>';
    echo anchor('fixtures/call_tasks', 'Call tasks') . '<br/>';
  }
  
  public function switch_user($uid) {
    // Login user.
    $this->session->set_userdata(array('user_uid' => $uid));
    // Force user reloading.
    current_user(TRUE);
    redirect($this->input->get('current'));
  }

  private function _env_check() {
    if (ENVIRONMENT != 'development') {
      show_error('Not allowed. Only available during development');
    }
  }

  /**
   * Populate db.
   */
  public function all() {
    // Down with the DB.
    $this->_tear_down();

    $this->_fix_surveys();
    $this->_fix_users();
    $this->_fix_call_tasks();
    redirect('/');
  }

  public function surveys() {
    $this->mongo_db->dropCollection('aw_datacollection', 'surveys');
    $this->_fix_surveys();
    redirect('/');
  }

  public function users() {
    $this->mongo_db->dropCollection('aw_datacollection', 'users');
    $this->_fix_users();
    redirect('/');
  }

  public function call_tasks() {
    $this->mongo_db->dropCollection('aw_datacollection', 'call_tasks');
    $this->_fix_call_tasks();
    redirect('/');
  }

  /**
   * Drop database.
   * Start with clean slate.
   */
  private function _tear_down() {
    $this->mongo_db->dropDb('aw_datacollection');
  }

  /**
   * Fixtures
   * Sets up demo surveys.
   */
  private function _fix_surveys() {
    // Copy files for survey : Meteor usage
    copy('resources/valid_survey/survey_1_xls.xls', 'files/surveys/survey_1_xls.xls');
    copy('resources/valid_survey/survey_1_xml.xml', 'files/surveys/survey_1_xml.xml');
    
    // Copy files for survey : Handlebars vs something else
    copy('resources/valid_survey/survey_2_xls.xls', 'files/surveys/survey_2_xls.xls');
    copy('resources/valid_survey/survey_2_xml.xml', 'files/surveys/survey_2_xml.xml');

    $this->mongo_db->batchInsert('surveys', array(
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Meteor usage',
        'client' => 'Flipside',
        'status' => 1,
        'goal' => NULL,
        'introduction' => 'The text the user has to read.',
        'description' => 'This survey will help us understand the reach of meteor.',
        'files' => array(
          'xls' => "survey_1_xls.xls",
          'xml' => "survey_1_xml.xml",
          'last_conversion' => array(
            'date' => Mongo_db::date(),
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'agents' => array(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Handlebars vs something else',
        'client' => 'Flipside',
        'status' => 2,
        'goal' => 20,
        'introduction' => 'The text the user has to read.',
        'description' => 'This survey description',
        'files' => array(
          'xls' => "survey_2_xls.xls",
          'xml' => "survey_2_xml.xml",
          'last_conversion' => array(
            'date' => Mongo_db::date(),
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        // Assign user 3 (agent)
        'agents' => array(3)
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Cat ladies around the neighborhood',
        'client' => 'Flipside',
        'status' => 2,
        'goal' => NULL,
        'introduction' => 'The text the user has to read.',
        'description' => 'This survey description',
        'files' => array(
          'xls' => NULL,
          'xml' => NULL,
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'agents' => array(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Knowledge of html',
        'client' => 'Flipside',
        'status' => 2,
        'goal' => NULL,
        'introduction' => 'The text the user has to read.',
        'description' => 'This survey description',
        'files' => array(
          'xls' => NULL,
          'xml' => NULL,
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'agents' => array(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Running out of titles',
        'client' => 'The world',
        'status' => 3,
        'goal' => NULL,
        'introduction' => 'The text the user has to read.',
        'description' => 'This survey description',
        'files' => array(
          'xls' => NULL,
          'xml' => NULL,
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'agents' => array(),
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Another survey',
        'client' => 'The world',
        'status' => 99,
        'goal' => NULL,
        'introduction' => 'The text the user has to read.',
        'description' => NULL,
        'files' => array(
          'xls' => NULL,
          'xml' => NULL,
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'agents' => array(),
      ),
    ));
  }

  /**
   * Fixtures
   * Sets up demo users.
   */
  private function _fix_users() {
    $this->mongo_db->batchInsert('users', array(
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
        'email' => 'moderator@localhost.dev',
        'name' => 'The Moderator',
        'username' => 'moderator',
        'password' => hash_password('moderator'),
        'roles' => array(ROLE_MODERATOR),
        'author' => 1,
        'status' => User_entity::STATUS_ACTIVE,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'blocked@localhost.dev',
        'name' => 'The Blocked Agent',
        'username' => 'blocked',
        'password' => hash_password('clocked'),
        'roles' => array(ROLE_CC_AGENT),
        'author' => 1,
        'status' => User_entity::STATUS_BLOCKED,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'deleted@localhost.dev',
        'name' => 'The Deleted',
        'username' => 'deleted',
        'password' => hash_password('deleted'),
        'roles' => array(),
        'author' => 1,
        'status' => User_entity::STATUS_DELETED,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'all_roles@localhost.dev',
        'name' => 'The All Roles',
        'username' => 'all_roles',
        'password' => hash_password('all_roles'),
        'roles' => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT),
        'author' => 1,
        'status' => User_entity::STATUS_ACTIVE,
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    ));
  }

  /**
   * Fixtures
   * Sets up demo call tasks.
   */
  private function _fix_call_tasks() {
    load_entity('call_task');
    
    $this->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    // Add some respondents with very specific status.
    $this->mongo_db->batchInsert('call_tasks', array(
      array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => "1000000000000",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 1,
        'survey_sid' => 2,
        'activity' => array(
          array(
            'code' => Call_task_status::NO_REPLY,
            'message' => NULL,
            'author' => 1,
            'created' => Mongo_db::date()
          ),
          array(
            'code' => Call_task_status::NO_REPLY,
            'message' => NULL,
            'author' => 1,
            'created' => Mongo_db::date()
          )
        )
      ),
      array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => "1000000000001",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 3,
        'survey_sid' => 2,
        'activity' => array(
          array(
            'code' => Call_task_status::CANT_COMPLETE,
            'message' => 'Not to be done right now. Maybe later.',
            'author' => 3,
            'created' => Mongo_db::date()
          )
        )
      ),
      array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => "1000000000002",
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date(),
        'assigned' => Mongo_db::date(),
        'author' => 1,
        'assignee_uid' => 3,
        'survey_sid' => 2,
        'activity' => array(
          array(
            'code' => Call_task_status::INVALID_NUMBER,
            'message' => NULL,
            'author' => 3,
            'created' => Mongo_db::date()
          )
        )
      )
    ));
    
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
    $this->mongo_db->batchInsert('call_tasks', $respondents);
  }

}

/* End of file fixtures.php */
/* Location: ./application/controllers/fixtures.php */
