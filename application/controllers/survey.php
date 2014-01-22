<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Survey extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
    
    $this->load->helper('form');
    $this->load->library('form_validation');
    
    $this->load->model('survey_model');
  }

	public function index() {
		//$this->load->view('welcome_message');
		print 'Index function of Survey controller';
	}
  
  public function surveys_list(){
    $surveys = $this->survey_model->get_all();
    
    $this->load->view('base/html_start');
    $this->load->view('survey_list', array('surveys' => $surveys));
    $this->load->view('base/html_end');
    
  }
  
  public function survey_by_id($sid){
    $survey = $this->survey_model->get($sid);
    
    if ($survey) {
      $this->load->view('base/html_start');
      $this->load->view('survey_page', array('survey' => $survey));
      $this->load->view('base/html_end');
    }
    else {
     show_404(); 
    }
  }
  
  public function survey_edit_by_id($sid){
    $survey = $this->survey_model->get($sid);
    
    if ($survey) {
      $this->_survey_form_handle('edit', $survey);
    }
    else {
     show_404();
    }
    
  }
  
  public function survey_add(){
    
    $this->_survey_form_handle('add');
  }
  
  private function _survey_form_handle($action = 'add', $survey = null) {
    
    $file_upload_config = array(
      'upload_path' => './files/surveys/',
      'allowed_types' => 'xls|xlsx',
      'file_name' => md5(microtime(true))
    );
    
    $this->load->library('upload', $file_upload_config);
    
    $this->form_validation->set_rules('survey_title', 'Survey Title', 'required');
    $this->form_validation->set_rules('survey_status', 'Survey Status', 'required|callback__survey_status_valid');
    $this->form_validation->set_rules('survey_file', 'Survey File', 'callback__survey_file_handle');
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('survey_form', array('survey' => $survey));
      $this->load->view('base/html_end');
    }
    else {
      switch ($action) {
        case 'add':
          $survey_data = array();
          $survey_data['title'] = $this->input->post('survey_title', TRUE);
          $survey_data['status'] = $this->input->post('survey_status');
          
          $new_survey = new Survey_entity($survey_data);
          $this->survey_model->save($new_survey);
          
          // The survey is saved. We can rename the file that was just uploaded
          // if there's one.
          $file = $this->input->post('survey_file');
          if ($file ==! FALSE) {
            $new_filename = $survey->compose_file_name('xls');
            rename($file['full_path'], $file['file_path'] . $new_filename);
            
            $new_survey->files['xls'] = $new_filename;
            // Save again.
            $this->survey_model->save($new_survey);
          }
          
          // TODO: Handle error during save.
          redirect('/survey/' . $new_survey->sid);          
          break;
        case 'edit':
          $sid_submitted = $this->input->post('survey_sid');
          
          if ($sid_submitted != $survey->sid) {
            // If the submitted sid is different than the loaded, means
            // that the form has been tempered with. Redirect and show error.
            redirect('/surveys');
          }
          
          $survey->title = $this->input->post('survey_title', TRUE);
          $survey->status = $this->input->post('survey_status');
          
          // Handle uploaded file:
          $file = $this->input->post('survey_file');
          if ($file ==! FALSE) {
            $new_filename = $survey->compose_file_name('xls');
            rename($file['full_path'], $file['file_path'] . $new_filename);
            
            $survey->files['xls'] = $new_filename;
          }
          
          $this->survey_model->save($survey);
          // TODO: Handle error during save.
          redirect('/survey/' . $survey->sid);
          
          break;
      }
    }
    
  }
  
  public function survey_delete_by_id(){
    
    $this->form_validation->set_rules('survey_sid', 'Survey ID', 'required|callback__survey_exists');
    $sid = $this->input->post('survey_sid');
    
    if ($this->form_validation->run() == TRUE) {
      $this->survey_model->delete($sid);
    }
    else {
      // Survey Id has been tempered with.
      // TODO: Survey Id has been tempered with. Show Message
    }
      redirect('/surveys');
  }
  
  public function survey_file_download($sid, $type) {    
    $survey = $this->survey_model->get($sid);
    if ($survey && isset($survey->files[$type]) && $survey->files[$type] !== NULL) {
      $this->load->helper('download');      
      // TODO: Move to config.
      $file_storage = './files/surveys/';
      
      force_download($survey->files[$type], $file_storage . $survey->files[$type]);
    }
    else {
     show_404();
    }
  }
  
  
  public function _survey_status_valid($status) {
    
    if (!array_key_exists($status, Survey_entity::$allowed_status)) {
      $this->form_validation->set_message('_survey_status_valid', 'The %s is not valid.');
      return FALSE;
    }
    
    return TRUE;    
  }
  
  public function _survey_exists($sid) {
    $survey = $this->survey_model->get($sid);
    
    return $survey ? TRUE : FALSE;
  }
  
  public function _survey_file_handle() {
    if (isset($_FILES['survey_file']) && !empty($_FILES['survey_file']['name'])) {
      if ($this->upload->do_upload('survey_file')) {
        // Set a $_POST value with the results of the upload to use later.
        $upload_data = $this->upload->data();
        $_POST['survey_file'] = $upload_data;
        return true;
      }
      else {
        // possibly do some clean up ... then throw an error
        $this->form_validation->set_message('_survey_file_handle', $this->upload->display_errors());
        return false;
      }
    }
    else  {
      // Nothing was uploaded. That's ok.
      $_POST['survey_file'] = FALSE;
    }
  }
}

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */