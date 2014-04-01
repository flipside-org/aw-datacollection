<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
  }
  
  public function test() {
    $this->load->view('base/html_start');
    $this->load->view('frontend');
    $this->load->view('base/html_end');
  }

	public function index() {
	  if (!is_logged()) {
	    redirect('login');
	  }
    $this->load->model('survey_model');
    
		$this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'dashboard'));
    
    // Use the same permissions for the list but use different statuses.
    $surveys = array();
    if (has_permission('view survey list any')) {
      $allowed_statuses = array(Survey_entity::STATUS_DRAFT, Survey_entity::STATUS_OPEN);
      $surveys = $this->survey_model->get_all($allowed_statuses);
    }
    else if (has_permission('view survey list assigned')) {
      $allowed_statuses = array(Survey_entity::STATUS_OPEN);
      $surveys = $this->survey_model->get_all($allowed_statuses, current_user()->uid);
    }
    
    // TEMP
    $data = '';
    if ($surveys) {
      $data = 'Your surveys:<br />';
      
      foreach ($surveys as $survey) {
        $data .= '-' . $survey->title . '<br />';
      }

      ob_start();
      krumo($surveys);
      $data .= ob_get_clean();
      
    }
    // /TEMP
    
    $this->load->view('dashboard', array('data' => $data));
    $this->load->view('base/html_end');
	}
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */