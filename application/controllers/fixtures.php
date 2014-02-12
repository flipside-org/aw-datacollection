<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fixtures extends CI_Controller {
  
  public function index() {
    
    echo '<h1>Set fixtures:<h1>';
    echo anchor('fixtures/all', 'All') . '<br/>';
    echo anchor('fixtures/surveys', 'Surveys'). '<br/>';
    echo anchor('fixtures/users', 'Users'). '<br/>';    
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
	  $this->_env_check();
	  // Down with the DB.
    $this->_tear_down();
    
	  $this->_fix_surveys();
    $this->_fix_users();
    redirect('/');
	}
  
  public function surveys() {
    $this->_env_check();
    $this->mongo_db->dropCollection('aw_datacollection', 'surveys');
    $this->_fix_surveys();
    redirect('/');
  }
  
  public function users() {
    $this->_env_check();
    $this->mongo_db->dropCollection('aw_datacollection', 'users');
    $this->_fix_users();
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
    
    $this->mongo_db->batchInsert('surveys', array(
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Meteor usage',
        'status' => 1,
        
        'files' => array(
          'xls' => "survey_1_xls.xls",
          'xml'=> "survey_1_xml.xml",
          
          'last_conversion' => array(
            'date' => 1390493562,
            'warnings' => NULL
          )
        ),
        
        'created' => Mongo_db::date()
        
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Handlebars vs something else',
        'status' => 1,
        
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,
          
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        
        'created' => Mongo_db::date(),
        
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Cat ladies around the neighborhood',
        'status' => 2,
        
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,
          
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        
        'created' => Mongo_db::date()
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Knowledge of html',
        'status' => 2,
        
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,
          
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        
        'created' => Mongo_db::date()
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Running out of titles',
        'status' => 3,
        
        'files' => array(
          'xls' => NULL,
          'xml'=> NULL,
          
          'last_conversion' => array(
            'date' => NULL,
            'warnings' => NULL
          )
        ),
        
        'created' => Mongo_db::date()
      ),
      array(
        'sid' => increment_counter('survey_sid'),
        'title' => 'Another survey',
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
        'email' => 'admin@localhost',
        'name' => 'Admin',
        'username' => 'admin',
        'password' => sha1('admin'),
        'roles' => array('administrator'),
        'author' => null,
        'status' => 2,
        
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()        
      ),
      array(
        'uid' => increment_counter('user_uid'),
        'email' => 'regular@localhost',
        'name' => 'Regular user',
        'username' => 'regular',
        'password' => sha1('regular'),
        'roles' => array(),
        'author' => 1,
        'status' => 2,
        
        'created' => Mongo_db::date(),
        'updated' => Mongo_db::date()
      )
    ));
  }
}

/* End of file fixtures.php */
/* Location: ./application/controllers/fixtures.php */