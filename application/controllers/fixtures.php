<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fixtures extends CI_Controller {
  
  function __construct() {
    parent::__construct();
    $this->_env_check();
    $this->load->helper('password_hashing');
  }
  
  /**
   * Check environment. Fixtures are only allowed during development
   */
  private function _env_check() {
    if (ENVIRONMENT != 'development') {
      show_error('Not allowed. Only available during development');
    }
  }
  
  /**
   * Drop database.
   * Start with clean slate.
   */
  private function _tear_down() {
    $this->mongo_db->dropDb('aw_datacollection');
  }
  
  /**
   * Setup the base status of the application.
   * These are not fixtures. This is needed data for the app
   * to work.
   */
  public function setup() {
    $this->_tear_down();
    
    // Create needed folders.
    if (!is_dir('files/surveys')) {
      mkdir('files/surveys', 0777, TRUE);
    }
    
    if (!is_dir('files/survey_results')) {
      mkdir('files/survey_results', 0777, TRUE);
    }
    
    // Admin user
    $admin = new User_entity(array(
      'email' => 'admin@localhost.dev',
      'name' => 'Admin',
      'username' => 'admin',
      'author' => 0,
    ));
    
    $admin->set_password(hash_password('admin'))
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(array(ROLE_ADMINISTRATOR));
      
    $this->user_model->save($admin);
    
    // Database indexes.
    $this->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    
    redirect('/login');
  }

  public function index() {
    echo '<h1>Fixtures:<h1>';
    echo anchor('fixtures/all', 'Setup fixtures') . '<br/><br/>';
    echo anchor('fixtures/setup', 'Live (Only admin user is created)');
  }
  
  public function switch_user($uid) {
    // Login user.
    $this->session->set_userdata(array('user_uid' => $uid));
    // Force user reloading.
    current_user(TRUE);
    redirect($this->input->get('current'));
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

  /**
   * Fixtures
   * Sets up demo surveys.
   */
  private function _fix_surveys() {
    $this->load->model('survey_model');
    
    // Copy files for survey : Meteor usage
    copy('resources/valid_survey/survey_1_xls.xls', 'files/surveys/survey_1_xls.xls');
    copy('resources/valid_survey/survey_1_xml.xml', 'files/surveys/survey_1_xml.xml');
    
    // Copy files for survey : Handlebars vs something else
    copy('resources/valid_survey/survey_2_xls.xls', 'files/surveys/survey_2_xls.xls');
    copy('resources/valid_survey/survey_2_xml.xml', 'files/surveys/survey_2_xml.xml');
    
    // Survey 1.
    $survey = new Survey_entity(array(
      // Sid will be 1 (auto increment)
      'title' => 'Meteor usage',
      'client' => 'Flipside',
      'status' => Survey_entity::STATUS_DRAFT,
      'introduction' => 'The text the user has to read.',
      'description' => 'This survey will help us understand the reach of meteor.',
      'files' => array(
        'xls' => "survey_1_xls.xls",
        'xml' => "survey_1_xml.xml",
        'last_conversion' => array(
          'date' => NULL,
          'warnings' => NULL,
        )
       )
    ));
    $this->survey_model->save($survey);
    
    // Survey 2.
    $survey = new Survey_entity(array(
      // Sid will be 2 (auto increment)
      'title' => 'Handlebars vs something else',
      'client' => 'Flipside',
      'status' => Survey_entity::STATUS_OPEN,
      'goal' => 20,
      'introduction' => 'Hi, my name is ______ and I am calling on behalf of Handlebars. Handlebars is interested in learning more about you and your relation with it. You are being contacted because of your participation in the workshop held near your house. We would like to ask you some questions about your coffee consumption while using handlebars. Your participation is very important, because your responses will help us improve our framework and thus make it more usable. This survey will only take about 5 minutes of your time.',
      'description' => 'This survey will help us understand how handlebars is used and how it can be improved.',
      'files' => array(
        'xls' => "survey_2_xls.xls",
        'xml' => "survey_2_xml.xml",
        'last_conversion' => array(
          'date' => NULL,
          'warnings' => NULL,
        )
       )
    ));
    $survey->assign_agent(3);
    $this->survey_model->save($survey);
    
    // Survey 3.
    $survey = new Survey_entity(array(
      // Sid will be 3 (auto increment)
      'title' => 'Cat ladies around the neighbourhood',
      'client' => 'Flipside',
      'status' => Survey_entity::STATUS_OPEN,
      'introduction' => 'The text the user has to read.',
      'description' => 'This survey description.'
    ));
    $this->survey_model->save($survey);
    
    // Survey 4.
    $survey = new Survey_entity(array(
      // Sid will be 4 (auto increment)
      'title' => 'Knowledge of HTML',
      'client' => 'The world',
      'status' => Survey_entity::STATUS_CLOSED,
      'introduction' => 'The text the user has to read.',
      'description' => 'This survey description.'
    ));
    $this->survey_model->save($survey);
    
    // Survey 5.
    $survey = new Survey_entity(array(
      // Sid will be 5 (auto increment)
      'title' => 'Another survey',
      'client' => 'The world',
      'status' => Survey_entity::STATUS_CANCELED,
    ));
    $survey->assign_agent(3);
    $this->survey_model->save($survey);
  }

  /**
   * Fixtures
   * Sets up demo users.
   */
  private function _fix_users() {
    
    ////////////////////////////////////////////////
    // User 1
    $user = new User_entity(array(
      // uid will be 1 (auto increment)
      'email' => 'admin@localhost.dev',
      'name' => 'Admin',
      'username' => 'admin',
      'author' => 0,
    ));
    $user->set_password(hash_password('admin'))
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(array(ROLE_ADMINISTRATOR));
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 2
    $user = new User_entity(array(
      // uid will be 2 (auto increment)
      'email' => 'regular@localhost.dev',
      'name' => 'Regular user',
      'username' => 'regular',
      'author' => 1,
    ));
    $user->set_password(hash_password('regular'))
      ->set_status(User_entity::STATUS_ACTIVE);
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 3
    $user = new User_entity(array(
      // uid will be 3 (auto increment)
      'email' => 'agent@localhost.dev',
      'name' => 'The Agent',
      'username' => 'agent',
      'author' => 1,
    ));
    
    $user->set_password(hash_password('agent'))
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(array(ROLE_CC_AGENT));
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 4
    $user = new User_entity(array(
      // uid will be 4 (auto increment)
      'email' => 'moderator@localhost.dev',
      'name' => 'The Moderator',
      'username' => 'moderator',
      'author' => 1,
    ));
    
    $user->set_password(hash_password('moderator'))
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(array(ROLE_MODERATOR));
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 5
    $user = new User_entity(array(
      // uid will be 5 (auto increment)
      'email' => 'blocked@localhost.dev',
      'name' => 'The Blocked Agent',
      'username' => 'blocked',
      'author' => 1,
    ));
    
    $user->set_password(hash_password('blocked'))
      ->set_status(User_entity::STATUS_BLOCKED)
      ->set_roles(array(ROLE_CC_AGENT));
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 6
    $user = new User_entity(array(
      // uid will be 6 (auto increment)
      'email' => 'deleted@localhost.dev',
      'name' => 'The Deleted',
      'username' => 'deleted',
      'author' => 1,
    ));
    
    $user->set_password(hash_password('deleted'))
      ->set_status(User_entity::STATUS_DELETED);
      
    $this->user_model->save($user);
    
    ////////////////////////////////////////////////
    // User 7
    $user = new User_entity(array(
      // uid will be 7 (auto increment)
      'email' => 'all_roles@localhost.dev',
      'name' => 'The All Roles',
      'username' => 'all_roles',
      'author' => 1,
    ));
    
    $user->set_password(hash_password('all_roles'))
      ->set_status(User_entity::STATUS_ACTIVE)
      ->set_roles(array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT));
      
    $this->user_model->save($user);
  }

  /**
   * Fixtures
   * Sets up demo call tasks.
   */
  private function _fix_call_tasks() {
    $this->load->model('call_task_model');

    // Index.
    $this->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    
    //Every call task is added for survey 2 (Handlebars vs somthing else)
    
    ///////////////////////////////////////////////////////
    // Call task.
    $call_task = new Call_task_entity(array(
      // uid will be 1 (auto increment)
      'number' => "1000000000001",
      'assigned' => Mongo_db::date(),
      'author' => 1, // Admin
      'assignee_uid' => 3, // The Agent
      'survey_sid' => 2, // Handlebars vs somthing else
    ));

    $call_task->add_status(new Call_task_status(array(
      'code' => Call_task_status::NO_REPLY,
      'message' => NULL,
      'author' => 3, // The Agent (The author is usually the assignee)
      'created' => Mongo_db::date()
    )));

    $call_task->add_status(new Call_task_status(array(
      'code' => Call_task_status::NO_REPLY,
      'message' => NULL,
      'author' => 3, // The Agent (The author is usually the assignee)
      'created' => Mongo_db::date()
    )));

    $this->call_task_model->save($call_task);

    ///////////////////////////////////////////////////////
    // Call task.
    $call_task = new Call_task_entity(array(
      // uid will be 2 (auto increment)
      'number' => "1000000000002",
      'assigned' => Mongo_db::date(),
      'author' => 1, // Admin
      'assignee_uid' => 3, // The Agent
      'survey_sid' => 2, // Handlebars vs somthing else
    ));

    $call_task->add_status(new Call_task_status(array(
      'code' => Call_task_status::CANT_COMPLETE,
      'message' => 'Not to be done right now. Maybe later.',
      'author' => 3, // The Agent (The author is usually the assignee)
      'created' => Mongo_db::date()
    )));

    $this->call_task_model->save($call_task);

    ///////////////////////////////////////////////////////
    // Call task.
    $call_task = new Call_task_entity(array(
      // uid will be 3 (auto increment)
      'number' => "1000000000003",
      'assigned' => Mongo_db::date(),
      'author' => 1, // Admin
      'assignee_uid' => 3, // The Agent
      'survey_sid' => 2, // Handlebars vs somthing else
    ));

    $call_task->add_status(new Call_task_status(array(
      'code' => Call_task_status::INVALID_NUMBER,
      'message' => NULL,
      'author' => 3, // The Agent (The author is usually the assignee)
      'created' => Mongo_db::date()
    )));

    $this->call_task_model->save($call_task);

    ///////////////////////////////////////////////////////
    
    // Add some respondents to be used for data collection.
    for($r = 0; $r < 100; $r++) {
      
      // Call task.
      $call_task = new Call_task_entity(array(
        // uid will be from 4 to 104 (auto increment)
        'number' => (string)(2000000000000 + $r),
        'assigned' => Mongo_db::date(),
        'author' => 1, // Admin
        'assignee_uid' => 3, // The Agent
        'survey_sid' => 2, // Handlebars vs somthing else
      ));

      $this->call_task_model->save($call_task);
    }
  }
}

/* End of file fixtures.php */
/* Location: ./application/controllers/fixtures.php */