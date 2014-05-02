<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
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


  public function test() {
    $this->load->helper('ORXFormResults');
    
    //$f = new ORXFormResults('files/surveys/survey_1_xml.xml');
    //$r =$f->parse_result_file('files/survey_results/survey_result_1_101_1.xml');
    //krumo($r);
    
    $f = new ORXFormResults('resources/valid_survey/survey_3_xml.xml');
    
    $test = array_fill(0, 2500, 'resources/valid_results/survey_3_results.xml');
    $header_done = FALSE;
    
    $fp = fopen('files/file.csv', 'w');
    
    foreach ($test as $filename) {
      $r = $f->parse_result_file($filename);
      //krumo($r);
      
      if (!$header_done) {
        $header_done = TRUE;
        $head = array();
        foreach ($r as $result) {
          $head[] = $result['label'];
        }
        fputcsv($fp, $head);
      }
      
      $fields = array();
      foreach ($r as $result) {
        $fields[] = is_array($result['value']) ? implode(' ', $result['value']) : $result['value'];
      }
      fputcsv($fp, $fields);

    }
    fclose($fp);
  }
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */