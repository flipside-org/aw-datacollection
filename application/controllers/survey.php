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
    $this->load->helper('typography');
    $this->load->helper('security');
    $this->load->library('form_validation');
    $this->load->model('survey_model');
    $this->load->model('call_task_model');
  }
  
  /**
   * Controller index.
   */
	public function index() {
	  print "Available";
    krumo($this->call_task_model->get_available(2));
	  print "Reserved";
    krumo($this->call_task_model->get_reserved(2, current_user()->uid));
	  print "Resolved";
    krumo($this->call_task_model->get_resolved(2, current_user()->uid));
	  print "Unresolved";
    krumo($this->call_task_model->get_unresolved(2, current_user()->uid));
	}
  
  /**
   * Lists all surveys.
   * Route:
   * /surveys
   */
  public function surveys_list(){
    if (!has_permission('view survey list')) {
      show_403();
    }
    
    $surveys = $this->survey_model->get_all();
    
    $this->load->view('base/html_start');
    $this->load->view('navigation');
    $this->load->view('surveys/survey_list', array('surveys' => $surveys));
    $this->load->view('base/html_end');
    
  }
  
  /**
   * Shows a specific survey loading it by its id.
   * Route
   * /survey/:sid
   */
  public function survey_by_id($sid){
    if (!has_permission('view survey page')) {
      show_403();
    }
    
    $survey = $this->survey_model->get($sid);
    
    $messages = Status_msg::get();
    $data = array(
      'survey' => $survey,
      //'messages' => $messages,
      'messages' => $this->load->view('messages', array('messages' => $messages), TRUE)
    );
    
    if ($survey) {
      $this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('surveys/survey_page', $data);
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
    if (!has_permission('create survey')) {
      show_403();
    }
    
    $this->_survey_form_handle('add');
  }
  
  /**
   * Edit page for a specific survey loading it by its id.
   * Route
   * /survey/:sid/edit
   */
  public function survey_edit_by_id($sid){
    if (!has_permission('edit any survey')) {
      show_403();
    }
    
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
  protected function _survey_form_handle($action = 'add', $survey = null) {
    
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
    $this->form_validation->set_rules('survey_status', 'Survey Status', 'required|callback__cb_survey_status_valid');
    $this->form_validation->set_rules('survey_introduction', 'Survey Introduction', 'xss_clean');
    $this->form_validation->set_rules('survey_file', 'Survey File', 'callback__cb_survey_file_handle');
    
    // If no data submitted show the form.
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('surveys/survey_form', array('survey' => $survey));
      $this->load->view('base/html_end');
    }
    else {
      switch ($action) {
        case 'add':
          // Prepare survey data to construct a new survey_entity
          $survey_data = array();
          $survey_data['title'] = $this->input->post('survey_title', TRUE);
          $survey_data['status'] = $this->input->post('survey_status');
          $survey_data['introduction'] = $this->input->post('survey_introduction', TRUE);
          
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
          
          // Set data from form.
          $survey->title = $this->input->post('survey_title', TRUE);
          $survey->status = $this->input->post('survey_status');
          $survey->introduction = $this->input->post('survey_introduction', TRUE);
          
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
    if (!has_permission('delete any survey')) {
      show_403();
    }
    
    $this->form_validation->set_rules('survey_sid', 'Survey ID', 'required|callback__cb_survey_exists');
    $sid = $this->input->post('survey_sid');
    
    if ($this->form_validation->run() == TRUE) {
      $this->survey_model->delete($sid);
    }
    else {
      // Survey Id has been tempered with.
      show_error("An error occurred while deleting the survey.");
    }
    redirect('/surveys');
  }
  
  /**
   * Download survey files.
   * Route
   * /survey/:sid/files/(xls|xml)
   */
  public function survey_file_download($sid, $type) {
    if (!has_permission('download survey files')) {
      show_403();
    }
        
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
  
  /**
   * Starts enketo showing the form for data collection or for
   * a testrun. 
   * Route
   * /survey/:sid/(testrun|data_collection)
   */
   // TODO: Permissions for enketo.
  public function survey_enketo($sid, $type) {    
    $survey = $this->survey_model->get($sid);
    if ($survey) {      
      // Needed urls.
      $settings = array(
        'current_survey' => array(
          'sid' => $sid,
        ),
        'url' => array(
          'request_csrf' => base_url('api/survey/request_csrf_token'),
          'xslt_transform' => base_url('api/survey/' . $sid . '/xslt_transform'),
          'request_respondents' => base_url('api/survey/' . $sid . '/request_respondents'),
          'enketo_submit' => base_url('api/survey/enketo_submit'),
        )
      );
      $this->js_settings->add($settings);
      
      $this->load->view('base/html_start', array('using_enketo' => TRUE, 'enketo_action' => $type));
      $this->load->view('navigation');
      $this->load->view('surveys/survey_enketo', array('survey' => $survey, 'enketo_action' => $type));
      $this->load->view('base/html_end');
    }
    else {
     show_404();
    }
  }
  
  /**
   * Enketo API
   * Converts the survey xml file to html for enketo to use
   * 
   * @param int $sid
   *   The survey id
   * 
   * Output as text/xml
   */
  public function api_survey_xslt_transform($sid) {
    if (!has_permission('collect data with enketo')) {
      return $this->api_output(403, 'Not allowed.', array('xml_form' => NULL));
    }
      
    $survey = $this->survey_model->get($sid);
    // TODO: Collect Data: Check for other restrictions (like cc_op assigned) 
    if ($survey && $survey->has_xml()) {
      $this->load->helper('xslt_transformer');

      $xslt_transformer = Xslt_transformer::build($survey->get_xml_full_path());
      $result = $xslt_transformer->get_transform_result_sxe()->asXML();
      
      return $this->api_output(200, 'Ok!', array('xml_form' => $result));
    }
    else {
      return $this->api_output(404, 'Invalid survey.', array('xml_form' => NULL));
    }
  }
  
  /**
   * Enekto API
   * Requests respondents for enketo. It will always send all the reserved
   * numbers. It's needed to they are filtered against the ones in localstorage.
   * 
   * @param int $sid
   *   The survey id
   * 
   * JSON output:
   *  respondents : Call_task_entity[]
   */
  public function api_survey_request_respondents($sid) {
    if (!has_permission('collect data with enketo')) {
      return $this->api_output(403, 'Not allowed.', array('respondents' => NULL));
    }
      
    $survey = $this->survey_model->get($sid);
    // TODO: Collect Data: Check for other restrictions (like cc_op assigned) 
    if ($survey) {
      // Max to reserve - from config.
      $max_to_reserve = $this->config->item('aw_enketo_respondents_reserve');
      
      // Already reserved.
      $reserved = $this->call_task_model->get_reserved($sid, current_user()->uid);
      
      // Extra to reserve.
      $to_reserve = $max_to_reserve - count($reserved);
      if ($to_reserve > 0) {
        $newly_reserved = $this->call_task_model->reserve($sid, current_user()->uid, $to_reserve);
        
        // If false means that there are no respondents.        
        if ($newly_reserved !== FALSE) {
          $reserved = array_merge($reserved, $newly_reserved);
        }
      }
      return $this->api_output(200, 'Ok!', array('respondents' => $reserved));
    }
    else {
      return $this->api_output(404, 'Invalid survey.', array('respondents' => NULL));
    }
  }
  
  /**
   * Enekto API
   * Enketo submits data through AJAX but since it is a form submission
   * a CSRF token is required.
   * 
   * JSON output:
   *  csrf : string token
   */
  public function api_survey_request_csrf_token() {
    if (has_permission('api request csrf token')) {
      return $this->api_output(200, 'Ok!', array('csrf' => $this->security->get_csrf_hash()));
    }
    else {
      return $this->api_output(403, 'Not allowed.', array('csrf' => NULL));
    }
  }
  
  /**
   * TODO: Survey::survey_submit_enketo_form Docs
   */
  public function api_survey_enketo_form_submit() {
    
    if (!has_permission('collect data with enketo')) {
      return $this->api_output(403, 'Not allowed.');
    }

    $sid = (int) $this->input->post('sid');
    $respondent = $this->input->post('respondent');
    $ctid = (int) $respondent['ctid'];
    
    $call_task = $this->call_task_model->get($ctid);
    if (!$call_task) {
      return $this->api_output(500, 'Invalid call task.');
    }
    
    // Is the call task assigned to the provided survey?
    if ($sid != $call_task->survey_sid) {
      return $this->api_output(500, 'Call task not assigned to survey.');
    }
    
    // If the same computer is shared by different users it may happen
    // that an user uploads data another user left in the localStorage.
    // We do not save that data, but we send a response to keep it in
    // the localstorage.
    // The call task can't be resolved.
    // There has to be someone assigned to it.
    // It can't be the logged in user.
    if (!$call_task->is_resolved() && $call_task->is_assigned() && current_user()->uid != $call_task->assignee_uid) {
      return $this->api_output(201, 'Submitting data for another user.');
    }
    
    // TODO : api_survey_enketo_form_submit : additional checks (user assigned, survey in right status)
    
    if (current_user()->uid != $call_task->assignee_uid) {
      return $this->api_output(500, 'User not assigned to call task.');
    }
    
    // Was the survey completed?
    // If there's a form_data it's finished
    if (isset($respondent['form_data'])) {
      // TODO: api_survey_enketo_form_submit : Check if the data is valid.
      
      // TODO: api_survey_enketo_form_submit : Save the data.
      
      // Set successful status.
      try {
        $call_task->add_status(Call_task_status::create(Call_task_status::SUCCESSFUL, ''));
      } catch (Exception $e) {
        return $this->api_output(500, 'Trying to submit data for a resolved call task.');
      }
      
    }
    elseif (isset($respondent['new_status']['code']) && isset($respondent['new_status']['msg'])) {
      
      if ($respondent['new_status']['code'] == Call_task_status::SUCCESSFUL) {
        return $this->api_output(500, 'Successful status can not be set manually.');
      }
      
      try {
        $new_status = Call_task_status::create($respondent['new_status']['code'], xss_clean(trim($respondent['new_status']['msg'])));
        $call_task->add_status($new_status);
        
      } catch (Exception $e) {
        return $this->api_output(500, 'Invalid call task status.');
      }      
    }
    else {
      // No form_data or new_status found. Error.
      return $this->api_output(500, 'Missing data form_data and new_status.');
    }
    
    $this->call_task_model->save($call_task);
    return $this->api_output();
  }
  
  // TODO: Survey. Delete delay function.
  public function delay($sec) {
    sleep($sec);
    $this->output
    ->set_content_type('text')
    ->set_output('OK from server');
  }
  
  /********************************
   ********************************
   * Start of methods that do not relate to routes.
   * Helper methods.
   * Callback for form validation
   * etc
   */
   
   protected function api_output($code = 200, $msg = 'Ok!', $extra = array()) {
     $res = array(
      'status' => array(
        'code' => $code,
        'message' => $msg,
      )
    );
    $res = array_merge($res, $extra);
    
    $this->output
    ->set_content_type('text/json')
    ->set_output(json_encode($res));

    return TRUE;
   }
  
  /**
   * Checks if the submitted status is valid
   * Form validation callback.
   */
  public function _cb_survey_status_valid($status) {
    
    if (!Survey_entity::is_valid_status($status)) {
      $this->form_validation->set_message('_cb_survey_status_valid', 'The %s is not valid.');
      return FALSE;
    }
    
    return TRUE;
  }
  
  /**
   * Checks if the survey exists
   * Form validation callback.
   */
  public function _cb_survey_exists($sid) {
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
  public function _cb_survey_file_handle() {
    if (isset($_FILES['survey_file']) && !empty($_FILES['survey_file']['name'])) {
      if ($this->upload->do_upload('survey_file')) {
        // Set a $_POST value with the results of the upload to use later.
        $upload_data = $this->upload->data();
        $_POST['survey_file'] = $upload_data;
        return true;
      }
      else {
        // Possibly do some clean up ... then throw an error
        $this->form_validation->set_message('_cb_survey_file_handle', $this->upload->display_errors());
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