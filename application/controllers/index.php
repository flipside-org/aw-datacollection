<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
    $this->load->model('survey_model');
  }
  
  public function index() {
    if (!is_logged()) {
      redirect('login');
    }
    
    // Use the same permissions for the list but use different statuses.
    $surveys = array();
    if (has_permission('view survey list any')) {
      redirect('surveys');
    }
    else if (has_permission('view survey list assigned')) {
      redirect('surveys/open');
    }
    
    // If regular user just show a empty page.
    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'dashboard'));
    $this->load->view('base/html_end');
  }
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */