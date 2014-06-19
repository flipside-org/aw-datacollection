<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Fixtures extends CI_Controller {
  
  function __construct() {
    parent::__construct();
    $this->load->helper('password_hashing');
    $this->load->model('survey_model');
    $this->load->model('call_task_model');
    $this->load->model('survey_result_model');
  }
  
  /**
   * Drop database.
   * Start with clean slate.
   */
  private function _tear_down() {
    // Use drop collection because the user does not have permission
    // to drop and create databases at will.
    $this->mongo_db->dropCollection('aw_datacollection', 'call_tasks');
    $this->mongo_db->dropCollection('aw_datacollection', 'counters');
    $this->mongo_db->dropCollection('aw_datacollection', 'session');
    $this->mongo_db->dropCollection('aw_datacollection', 'surveys');
    $this->mongo_db->dropCollection('aw_datacollection', 'survey_results');
    $this->mongo_db->dropCollection('aw_datacollection', 'users');
    
    // Clean directories.
    $dir_to_empty = array(
      'files/surveys/',
      'files/survey_results/'
    );
    foreach($dir_to_empty as $dir) {
      if (!is_dir($dir)) {
        continue;
      }
            
      $contents = scandir($dir);
      // remove ./
      array_shift($contents);
      // remove ../
      array_shift($contents);
      
      foreach($contents as $file) {
        unlink($dir . $file);
      }
    }
  }

  /**
   * Development helper to switch between users.
   */
  public function switch_user($uid) {
    if (ENVIRONMENT != 'development') {
      show_error('Not allowed. Only available during development');
    }
    
    // Login user.
    $this->session->set_userdata(array('user_uid' => $uid));
    // Force user reloading.
    current_user(TRUE);
    redirect($this->input->get('current'));
  }
  
  /**
   * Setup the base status of the application.
   * These are not fixtures. This is needed data for the app
   * to work.
   */
  public function live_setup() {
    if (ENVIRONMENT != 'development') {
      show_error('Not allowed. Only available during development');
    }
    
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
    if (ENVIRONMENT != 'development') {
      show_error('Not allowed. Only available during development');
    }
    
    echo '<h1>Setup:<h1>';
    echo anchor('fixtures/all', 'Development') . '<br/><br/>';
    echo anchor('fixtures/live_setup', 'Live');
  }

  /**
   * Populate db.
   */
  public function all() {
    if (ENVIRONMENT != 'development' && ENVIRONMENT != 'demo'){
        show_error('Not allowed. Only available during development');
    }
    else if (ENVIRONMENT == 'demo') {
      $demo_key = trim(@file_get_contents('reset.demo.key'));
      
      if (!$demo_key || $demo_key != $_GET['reset_key']) {
        show_error('Wrong or missing key.');
      }      
    }
    // Down with the DB.
    $this->_tear_down();
    
    // Create needed folders.
    if (!is_dir('files/surveys')) {
      mkdir('files/surveys', 0777, TRUE);
    }
    
    if (!is_dir('files/survey_results')) {
      mkdir('files/survey_results', 0777, TRUE);
    }
    
    // Users.
    require_once(APPPATH . 'fixtures_data/users.fix.php');
    
    // Surveys.
    require_once(APPPATH . 'fixtures_data/survey1.fix.php');
    require_once(APPPATH . 'fixtures_data/survey2.fix.php');
    require_once(APPPATH . 'fixtures_data/survey3.fix.php');
    require_once(APPPATH . 'fixtures_data/survey4.fix.php');
    
    // Indexes.
    $this->mongo_db->addIndex('call_tasks', array('ctid' => 'asc'));
    
    if (ENVIRONMENT == 'development') {
      redirect('/');
    }
    else {
      print "Done";
    }
  }
}

/* End of file fixtures.php */
/* Location: ./application/controllers/fixtures.php */