<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Survey controller.
 */
class Survey extends CI_Controller {
  
  /**
   * Constructor 
   */
  public function __construct() {
    parent::__construct();
    
    // Load stuff needed for this controller.
    $this->load->helper('form');
    $this->load->library('form_validation');
    $this->load->model('survey_model');
  }
  
  /**
   * Controller index.
   */
	public function index() {
		print 'Index function of Survey controller';
	}
  
  /**
   * Lists all surveys.
   * Route:
   * /surveys
   */
  public function surveys_list(){
    $surveys = $this->survey_model->get_all();
    
    $this->load->view('base/html_start');
    $this->load->view('survey_list', array('surveys' => $surveys));
    $this->load->view('base/html_end');
    
  }
  
  /**
   * Shows a specific survey loading it by its id.
   * Route
   * /survey/:sid
   */
  public function survey_by_id($sid){
    $survey = $this->survey_model->get($sid);
    $messages = Status_msg::get();
    $data = array(
      'survey' => $survey,
      'messages' => $messages,
    );
    
    if ($survey) {
      $this->load->view('base/html_start');
      $this->load->view('survey_page', $data);
      $this->load->view('base/html_end');
    }
    else {
     show_404(); 
    }
  }
  
  /**
   * Page to add new survey.
   * Route
   * /survey/add
   */
  public function survey_add(){    
    $this->_survey_form_handle('add');
  }
  
  /**
   * Edit page for a specific survey loading it by its id.
   * Route
   * /survey/:sid/edit
   */
  public function survey_edit_by_id($sid){
    $survey = $this->survey_model->get($sid);
    
    if ($survey) {
      $this->_survey_form_handle('edit', $survey);
    }
    else {
     show_404();
    }
    
  }
  
  /**
   * Handles form to add and edit survey.
   * 
   * @param int $action
   *  Action to take on the survey add|edit
   * 
   * @param Survey_entity $survey.
   *   If editing the survey is passed to the function.
   */
  private function _survey_form_handle($action = 'add', $survey = null) {
    
    // Config data for the file upload.
    $file_upload_config = array(
      'upload_path' => '/tmp/',
      'allowed_types' => 'xls|xlsx',
      'file_name' => md5(microtime(true))
    );
    
    // Load needed libraries
    $this->load->library('upload', $file_upload_config);
    $this->load->helper('pyxform');
    
    // Set form validation rules.
    $this->form_validation->set_rules('survey_title', 'Survey Title', 'required');
    $this->form_validation->set_rules('survey_status', 'Survey Status', 'required|callback__survey_status_valid');
    $this->form_validation->set_rules('survey_file', 'Survey File', 'callback__survey_file_handle');
    
    // If no data submitted show the form.
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('survey_form', array('survey' => $survey));
      $this->load->view('base/html_end');
    }
    else {
      switch ($action) {
        case 'add':
          // Prepare survey data to construct a new survey_entity
          $survey_data = array();
          $survey_data['title'] = $this->input->post('survey_title', TRUE);
          $survey_data['status'] = $this->input->post('survey_status');
          
          // Construct survey.
          $new_survey = Survey_entity::build($survey_data);
          
          // Save survey.
          // Survey files can only be handled after the survey is saved.
          // TODO: Handle error during save.
          $this->survey_model->save($new_survey);
          
          // The survey is saved. We can rename the file that was just uploaded
          // if there's one.
          $file = $this->input->post('survey_file');
          if ($file ==! FALSE) {
            $new_survey->save_xls($file);
            $result = $new_survey->convert_xls_to_xml();
            // Save again.
            // TODO: Handle error during save.
            $this->survey_model->save($new_survey);
            
            // Set status messages.
            switch ($result->code) {
              case 101:
                Status_msg::warning('Survey file successfully converted but there are some warnings:');
                foreach ($result->warnings as $value) {
                  Status_msg::warning($value);
                }
                break;
              case 999:
                Status_msg::error('Survey file conversion failed:');
                Status_msg::error($result->message);
                break;
            }
            
          }
          
          // If it reaches this point the survey was saved.
          Status_msg::success('Survey successfully created.');
          
          redirect('/survey/' . $new_survey->sid);          
          break;
        case 'edit':
          $sid_submitted = $this->input->post('survey_sid');
          
          if ($sid_submitted != $survey->sid) {
            // If the submitted sid is different than the loaded, means
            // that the form has been tempered with. Redirect and show error.
            // TODO: Form has been tempered with. Redirect and show error.
            redirect('/surveys');
          }
          
          // Set data from form.
          $survey->title = $this->input->post('survey_title', TRUE);
          $survey->status = $this->input->post('survey_status');
          
          // Handle uploaded file:
          $file = $this->input->post('survey_file');
          if ($file ==! FALSE) {
            $survey->save_xls($file);
            $result = $survey->convert_xls_to_xml();
            
             // Set status messages.
            switch ($result->code) {
              case 101:
                Status_msg::warning('Survey file successfully converted but there are some warnings:');
                foreach ($result->warnings as $value) {
                  Status_msg::warning($value);
                }
                break;
              case 999:
                Status_msg::error('Survey file conversion failed:');
                Status_msg::error($result->message);
                break;
            }
            
          }

          // TODO: Handle error during save.
          $this->survey_model->save($survey);
          Status_msg::success('Survey successfully updated.');
          
          redirect('/survey/' . $survey->sid);          
          break;
      }
    }
    
  }
  
  /**
   * Delete handler for surveys.
   * Route (POST data)
   * /survey/delete
   */
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
  
  /**
   * Download survey files.
   * Route
   * /survey/:sid/files/(xls|xml)
   */
  public function survey_file_download($sid, $type) {    
    $survey = $this->survey_model->get($sid);
    if ($survey && isset($survey->files[$type]) && $survey->files[$type] !== NULL) {
      $this->load->helper('download');     
      $file_storage = $this->config->item('aw_survey_files_location');
      
      force_download($survey->files[$type], $file_storage . $survey->files[$type]);
    }
    else {
     show_404();
    }
  }
  
  /********************************
   ********************************
   * Start of methods that do not relate to routes.
   * Helper methods.
   * Callback for form validation
   * etc
   */
  
  /**
   * Checks if the submitted status is valid
   * Form validation callback.
   */
  public function _survey_status_valid($status) {
    
    if (!array_key_exists($status, Survey_entity::$allowed_status)) {
      $this->form_validation->set_message('_survey_status_valid', 'The %s is not valid.');
      return FALSE;
    }
    
    return TRUE;    
  }
  
  /**
   * Checks if the survey exists
   * Form validation callback.
   */
  public function _survey_exists($sid) {
    $survey = $this->survey_model->get($sid);
    
    return $survey ? TRUE : FALSE;
  }


  /**
   * The file upload library does not interact with the form validation.
   * To trigger ab error if something went wrong we use the approach 
   * specified at:
   * http://keighl.com/post/codeigniter-file-upload-validation/
   * Form validation callback.
   */
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