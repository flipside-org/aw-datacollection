<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('SURVEY_RESPONDENT_CSV_HEADER', 'phone_number');

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
    load_entity('call_task');
    // this is neede
    session_start();
  }

  /**
   * Controller index.
   */
	public function index() {
	  redirect('surveys');
	}

  /**
   * Lists all surveys.
   * Route:
   * /surveys
   */
  public function surveys_list(){    
    if (!has_permission('view survey list any') && !has_permission('view survey list assigned')) {
      show_403();
    }

    if (has_permission('view survey list any')) {
      $surveys = $this->survey_model->get_all();
    }
    else if (has_permission('view survey list assigned')) {
      $allowed_statuses = array(
        Survey_entity::STATUS_OPEN,
        Survey_entity::STATUS_CLOSED,
        Survey_entity::STATUS_CANCELED
      );
      $surveys = $this->survey_model->get_all($allowed_statuses, current_user()->uid);
    }

    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'surveys'));
    $this->load->view('surveys/survey_list', array('surveys' => $surveys));
    $this->load->view('base/html_end');

  }

  /**
   * Shows a specific survey loading it by its id.
   * Route
   * /survey/:sid
   */
  public function survey_by_id($sid){
    if (!has_permission('view any survey page') && !has_permission('view assigned survey page')) {
      show_403();
    }

    $survey = $this->survey_model->get($sid);

    if (!$survey) {
      show_404();
    }
    // TODO: Status restriction
    if (!has_permission('view any survey page') && has_permission('view assigned survey page')) {
      // Is assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        show_403();
      }
    }

    $data = array();
    $data['survey'] = $survey;

    // Agents. Each array element contains the user and
    // properties for the select. (selected, disabled)
    $agents = array();
    // Prepare users.
    $all_agents = $this->user_model->get_with_role(ROLE_CC_AGENT);
    foreach ($all_agents as $index => $user) {
      $agents[$index]['user'] = $user;
      $agents[$index]['properties'] = array();

      if ($survey->is_assigned_agent($user->uid)) {
        $agents[$index]['properties'][] = 'selected';

        // TODO: Check if the user can be unassigned.
      }

    }
    $data['agents'] = $agents;
    
    // Call tasks, statistics and stuff.
    // We need call tasks to compute the progress bars and the cal tasks tables.
    // Instead of doing complicated aggragate queries for each one, we just get all
    // the call tasks and extract all the data we need.
    $all_call_tasks = $this->call_task_model->get_all($survey->sid);
    
    $call_tasks_status_bar = array(
      'total' => count($all_call_tasks),
      'success' => 0,
      'failed' => 0,
      'pending' => 0,
      'remaining' => 0,
    );
    
    // Prepare call tasks table by looping over assigned agents.
    $call_tasks_table = array();
    foreach ($survey->agents as $uid) {
      $user = $this->user_model->get($uid);          
      $call_tasks_table[$uid] = array(
        'name' => $user->name,
        'sum' => 0,
        'success' => 0,
        'failed' => 0,
        'pending' => 0,
      );
    }
    
    // The mighty call tasks loop.
    foreach ($all_call_tasks as $call_task) {
      
      // Compute call tasks status bar.
      if ($call_task->is_unresolved()) {
        $call_tasks_status_bar['pending']++;
      }
      else if ($call_task->is_success()) {
        $call_tasks_status_bar['success']++;
      }
      else if ($call_task->is_failed()) {
        $call_tasks_status_bar['failed']++;
      }
      else {
        $call_tasks_status_bar['remaining']++;
      }
      // //END Compute call tasks status bar.
      /**********************************************/
      
      // Prepare table data.
      if ($call_task->is_assigned()) {
        $uid = $call_task->assignee_uid;
        
        if (isset($call_tasks_table[$uid])) {
          if ($call_task->is_unresolved()) {
            $call_tasks_table[$uid]['pending']++;
            $call_tasks_table[$uid]['sum']++;
          }
          else if ($call_task->is_success()) {
            $call_tasks_table[$uid]['success']++;
            $call_tasks_table[$uid]['sum']++;
          }
          else if ($call_task->is_failed()) {
            $call_tasks_table[$uid]['failed']++;
            $call_tasks_table[$uid]['sum']++;
          }
          // else
          // Call tasks that are reserved (assigned but without activity)
          // do not interest us. Skip them.
        }
        // else
        // The agent was probably unassigned. just ignore it.
      }
      // //END Prepare table data.
      /**********************************************/
      
    }
    
    $data['call_tasks_status_bar'] = $call_tasks_status_bar;
    $data['call_tasks_table'] = $call_tasks_table;
    
    //krumo($data); die();

    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'surveys'));
    $this->load->view('surveys/survey_page', $data);
    $this->load->view('base/html_end');

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
    $this->form_validation->set_rules('survey_client', 'Survey Client', 'required');
    $this->form_validation->set_rules('survey_goal', 'Survey Goal', 'is_natural_no_zero');
    $this->form_validation->set_rules('survey_status', 'Survey Status', 'required|callback__cb_survey_status_valid');
    $this->form_validation->set_rules('survey_introduction', 'Survey Introduction', 'xss_clean');
    $this->form_validation->set_rules('survey_description', 'Survey Description', 'xss_clean');
    $this->form_validation->set_rules('survey_file', 'Survey File', 'callback__cb_survey_file_handle');

    // If no data submitted show the form.
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'surveys'));
      $this->load->view('surveys/survey_form', array('survey' => $survey));
      $this->load->view('base/html_end');
    }
    else {
      switch ($action) {
        case 'add':
          // Prepare survey data to construct a new survey_entity
          $survey_data = array();
          $survey_data['title'] = $this->input->post('survey_title', TRUE);
          $survey_data['client'] = $this->input->post('survey_client', TRUE);
          $survey_data['goal'] = $this->input->post('survey_goal') ? (int) $this->input->post('survey_goal') : NULL;
          $survey_data['status'] = (int) $this->input->post('survey_status');
          $survey_data['introduction'] = $this->input->post('survey_introduction', TRUE);
          $survey_data['description'] = $this->input->post('survey_description', TRUE);

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
          $survey->client = $this->input->post('survey_client', TRUE);
          $survey->goal = $this->input->post('survey_goal') ? (int) $this->input->post('survey_goal') : NULL;
          $survey->status = (int) $this->input->post('survey_status');
          $survey->introduction = $this->input->post('survey_introduction', TRUE);
          $survey->description = $this->input->post('survey_description', TRUE);

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
   * Route
   * /survey/:sid/delete
   */
  public function survey_delete_by_id($sid){
    verify_csrf_get();
    
    if (!has_permission('delete any survey')) {
      show_403();
    }
    
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    
    if ($this->survey_model->delete($sid)) {
      Status_msg::success('Survey successfully deleted.');
    }
    else {
      Status_msg::error('An error occurred while deleting the survey.');
    }
    
    redirect('/surveys');
  }

  /**
   * Download survey files.
   * Route
   * /survey/:sid/files/(xls|xml)
   */
  public function survey_file_download($sid, $type) {
    if (!has_permission('download any survey files')) {
      show_403();
    }

    $survey = $this->survey_model->get($sid);
    if ($survey && isset($survey->files[$type]) && $survey->files[$type] !== NULL) {
      $this->load->helper('download');
      $file_storage = $this->config->item('aw_survey_files_location');

      force_download($survey->files[$type], file_get_contents($file_storage . $survey->files[$type]));
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
  public function survey_enketo($sid, $type) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!$survey->has_xml()) {
      show_403();
    }
    
    // If can collect|testrun any let through, otherwise must be assigned. 
    switch ($type) {
      case 'data_collection':
        $perm_any = 'enketo collect data any';
        $perm_assigned = 'enketo collect data assigned';
        break;

      case 'testrun':
        $perm_any = 'enketo testrun any';
        $perm_assigned = 'enketo testrun assigned';
        break;
    }
    
    if (!has_permission($perm_any) && !has_permission($perm_assigned)) {
      show_403();

    }else if (!has_permission($perm_any) && has_permission($perm_assigned)) {
      // Is assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        show_403();
      }
    }

    // Needed urls.
    $settings = array(
      'url' => array(
        'request_csrf' => base_url('api/survey/request_csrf_token'),
        'xslt_transform' => base_url('api/survey/' . $sid . '/xslt_transform'),
        'request_respondents' => base_url('api/survey/' . $sid . '/request_respondents'),
        'enketo_submit' => base_url('api/survey/' . $sid . '/enketo_submit'),
      )
    );
    $this->js_settings->add($settings);

    $this->load->view('base/html_start', array('using_enketo' => TRUE, 'enketo_action' => $type));
    $this->load->view('components/navigation', array('active_menu' => 'surveys'));
    $this->load->view('surveys/survey_enketo', array('survey' => $survey, 'enketo_action' => $type));
    $this->load->view('base/html_end');

  }

  /**
   * Enketo data collection for a specific call task.
   *
   * Route:
   * /survey/:sid/data_collection/:ctid
   */
  public function survey_enketo_single($sid, $ctid) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!$survey->has_xml()) {
      show_403();
    }
    else if (!has_permission('enketo collect data any') && !has_permission('enketo collect data assigned')) {
      show_403();
    }
    else if (!has_permission('enketo collect data any') && has_permission('enketo collect data assigned')) {
      // Is assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        show_403();
      }
    }

    $call_task = $this->call_task_model->get($ctid);
    $survey = $this->survey_model->get($sid);
    if ($call_task && $survey && $survey->sid == $call_task->survey_sid) {
      // Can only collect directly if:
      // - Call task is assigned to current user
      // - Call task is not resolved but it was started (unresolved).
      if ($call_task->is_assigned(current_user()->uid) && $call_task->is_unresolved()) {
        // Needed urls.
        $settings = array(
          'single_call_task' => $call_task,
          'url' => array(
            'request_csrf' => base_url('api/survey/request_csrf_token'),
            'xslt_transform' => base_url('api/survey/' . $sid . '/xslt_transform'),
            'enketo_submit' => base_url('api/survey/' . $sid . '/enketo_submit'),
          )
        );
        $this->js_settings->add($settings);

        $this->load->view('base/html_start', array('using_enketo' => TRUE, 'enketo_action' => 'data_collection_single'));
        $this->load->view('components/navigation', array('active_menu' => 'surveys'));
        $this->load->view('surveys/survey_enketo', array('survey' => $survey, 'call_task' => $call_task, 'enketo_action' => 'data_collection_single'));
        $this->load->view('base/html_end');
      }
      else {
        show_403();
      }
    }
    else {
     show_404();
    }
  }

  /**
   * Call task activity page. Shows a list of completed call tasks
   * and call tasks still to complete.
   *
   * Route:
   * /survey/:sid/call_activity
   */
  public function survey_call_activity($sid) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!has_permission('enketo collect data any') && !has_permission('enketo collect data assigned')) {
      show_403();
    }
    else if (!has_permission('enketo collect data any') && has_permission('enketo collect data assigned')) {
      // Is assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        show_403();
      }
    }

    $resolved = $this->call_task_model->get_resolved($sid, current_user()->uid);
    $unresolved = $this->call_task_model->get_unresolved($sid, current_user()->uid);

    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'surveys'));
    $this->load->view('surveys/survey_call_activity', array(
      'survey' => $survey,
      'call_tasks_resolved' => $resolved,
      'call_tasks_unresolved' => $unresolved)
    );
    $this->load->view('base/html_end');
  }

  /**
   * Summary page to list the respondents associated to a given survey.
   * @param $sid
   *
   * Route - /survey/:sid/respondents/:page
   */
  public function survey_respondents($sid, $page = 1){
    $page = $page < 1 ? 1 : $page;    
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!has_permission('manage respondents any survey')) {
      show_403();
    }

    // Respondents to show per page.
    $respondents_pp = $this->config->item('aw_respondents_per_page');
    $this->load->library('pagination');
    
    // Prepare pagination.
    $this->pagination->initialize(array(
      'base_url' => $survey->get_url_respondents(),
      'use_page_numbers' => TRUE,
      'uri_segment' => 4,
      'total_rows' => $this->call_task_model->get_total_count($sid),
      'per_page' => $respondents_pp,
      
      'full_tag_open' => '<ul class="bttn-group bttn-center">',
      'full_tag_close' => '</ul>',
      
      'num_tag_open' => '<li>',
      'anchor_class' => 'class="bttn bttn-default bttn-small"',
      'num_tag_close' => '</li>',
      
      // By default current is not a link.
      'cur_tag_open' => '<li><a class="bttn bttn-default bttn-small current" href="' . $survey->get_url_respondents($page) . '">',
      'cur_tag_close' => '</li></a>',
      
      'first_tag_open' => '<li>',
      'first_tag_close' => '</li>',
      
      'last_tag_open' => '<li>',
      'last_tag_close' => '</li>',
      
      'next_tag_open' => '<li>',
      'next_tag_close' => '</li>',
      
      'prev_tag_open' => '<li>',
      'prev_tag_close' => '</li>',
    ));
        
    $respondents = $this->call_task_model->get_all_paginated($sid, $page, $respondents_pp);

    $data = array(
      'survey' => $survey,
      'respondents' => $respondents,
    );
    
    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'surveys'));
    $this->load->view('surveys/survey_respondents', $data);
    $this->load->view('base/html_end');
  }

  /**
   * Summary page to add respondents to a given survey.
   * @param $sid
   *
   * Route - /survey/:sid/respondents/(file|input)
   */
  public function survey_respondents_add($sid, $type){
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!has_permission('manage respondents any survey')) {
      show_403();
    }
    
    // Form validation based on import type.
    switch ($type) {
      case 'file':
        // Config data for the file upload.
        $file_upload_config = array(
          'upload_path' => '/tmp/',
          'allowed_types' => 'csv',
          'file_name' => 'respondents_' . md5(microtime(true))
        );
    
        // Load needed libraries.
        $this->load->library('upload', $file_upload_config);
        $this->form_validation->set_rules('survey_respondents_file', 'Respondents File', 'callback__cb_survey_respondents_add_file_handle');
        break;
      case 'direct':
        $this->form_validation->set_rules('survey_respondents_text', 'Respondents Text', 'trim|required|xss_clean');
        break;
    }
    
    // If the import has invalid respondents they are added to the textarea
    // to allow the user to correct them.
    $invalid_respondents = array();
    if (isset($_SESSION['respondents_numbers']['invalid'])) {
      $invalid_respondents = $_SESSION['respondents_numbers']['invalid'];
      // Unset so they don't bother anymore.
      unset($_SESSION['respondents_numbers']);
    }

    // If no data submitted show the form.
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'surveys'));
      $this->load->view('surveys/survey_respondents_add', array('survey' => $survey, 'import_type' => $type, 'invalid_respondents' => $invalid_respondents));
      $this->load->view('base/html_end');
    }
    else {
      
      // Processing based on import type.
      switch ($type) {
        case 'file':
          // Read file.
          $file = $this->input->post('survey_respondents_file');
          if (isset($file['full_path'])) {
            // Load CSVReader library.
            $this->load->helper('csvreader');
            $csv = new CSVReader();
            $csv->separator = ',';
            $rows = $csv->parse_file($file['full_path']);
          }
          
          break;
          
        case 'direct':
          $rows = explode("\n", $this->input->post('survey_respondents_text'));
          
          break;
      }
      
      if (!isset($rows)) {
        Status_msg::error('An error occurred when processing the numbers. Try again.');
        redirect($survey->get_url_respondents());
      }
      
      // Load all call_tasks from DB to check for duplicates.
      $db_call_tasks = $this->call_task_model->get_all($sid);
      
      try {
        $respondents_numbers = $this->_survey_respondents_import_check($rows, $db_call_tasks);
      }
      catch (Exception $e) {
        Status_msg::error($e->getMessage());
        redirect($survey->get_url_respondents());        
      }

      // Create a call_task for each respondent number.
      $success_saves = array();
      foreach ($respondents_numbers['valid'] as $number) {
        // Prepare survey data to construct a new survey_entity
        $call_task_data = array();
        $call_task_data['survey_sid'] = (int) $sid;
        $call_task_data['number'] = (string) $number;

        // Construct survey.
        $new_call_task = Call_task_entity::build($call_task_data);

        // Save call task.
        if ($this->call_task_model->save($new_call_task)) {
          $success_saves[] = $number;
        }
        else{
          // If an error occurred flag as invalid to be imported again.
          // This should not happen, but you never know.
          $respondents_numbers['invalid'][] = $number;
        }
      }
      
      $respondents_numbers['valid'] = $success_saves;
      
      // Setting messages.
      $total_valid = count($respondents_numbers['valid']);
      $total_invalid = count($respondents_numbers['invalid']);
      $total_dups = count($respondents_numbers['dups']);
      $total_numbers = $total_valid + $total_invalid + $total_dups;
      
      if ($total_valid) {
        Status_msg::success("$total_valid of $total_numbers respondents were successfully imported.", TRUE);
      }
      else {
        Status_msg::warning("No respondents were imported.");
      }
      
      if ($total_invalid) {
        Status_msg::error("$total_invalid of $total_numbers respondents were not in a valid format.");
      }
      
      if ($total_dups) {
        Status_msg::notice("$total_dups of $total_numbers respondents have duplicate phone numbers.");
      }
      
      // Store invalid in any.
      if ($total_invalid) {
        $_SESSION['respondents_numbers']['invalid'] = $respondents_numbers['invalid'];
        redirect($survey->get_url_respondents_add('direct'));
      }
      redirect($survey->get_url_respondents());
    }
  }

  /**
   * Validates respondents prior to import.
   * For each respondent (input line) checks if the number is
   * valid, invalid or duplicated (both in the DB and in the input data).
   * 
   * Note: This method should be protected but since we need to test it is
   * set as public.
   * 
   * @param array $rows
   *   The input data splitted by lines
   * @param array[Call_task_entity] $db_call_tasks
   *   All the respondents in the db to check for dups.
   * 
   * @throws Exception
   *   When invalid CSV header
   * 
   * @return array
   *   Results of the checks. Keyed array with numbers:
   *    - valid 
   *    - invalid
   *    - dups
   */
  public function _survey_respondents_import_check($rows, $db_call_tasks) {
    $respondents_numbers = array(
      'valid' => array(),
      'dups' => array(),
      'invalid' => array()
    );
    
    foreach ($rows as $line => $row) {
      // Silently skip empty rows.
      if (empty($row)) {
        continue;
      }

      // Prepare data.
      // Context: CSV - data comes in array format.
      if (is_array($row)) {
        // Make sure it's not a random CSV.
        if (!isset($row[SURVEY_RESPONDENT_CSV_HEADER])) {
          throw new Exception('Invalid CSV header. Make sure your column header is "' . SURVEY_RESPONDENT_CSV_HEADER .'".');
        }
        
        $row = trim($row[SURVEY_RESPONDENT_CSV_HEADER]);
      }
      // Context: Textarea - One number per line, no header. 
      else {
        $row = trim($row);
      }
      
      // Check db double in submitted data.
      if (in_array($row, $respondents_numbers['valid']) ||
          in_array($row, $respondents_numbers['invalid']) ||
          in_array($row, $respondents_numbers['dups'])) {
        $respondents_numbers['dups'][] = $row;
        continue;
      }
      
      // Check if number is in correct format.
      if (!preg_match('/^[0-9]+$/', $row)) {
        $respondents_numbers['invalid'][] = $row;
        continue;          
      }
      
      // Check db doubles.
      $is_double = FALSE;
      foreach ($db_call_tasks as $call_task) {
        if ($call_task->number == $row) {
          $is_double = TRUE;
          break;
        }
      }        
      if ($is_double) {
        // Db Double.
        $respondents_numbers['dups'][] = $row;
        continue;
      }
      // END Check db doubles.
      
      // Valid
      $respondents_numbers['valid'][] = $row;
    }
    
    return $respondents_numbers;
  }

  /**
   * Manage respondents. Used as submit for form or single link.
   * @param $sid
   *   Survey sid
   * @param $action
   *  Action can be bulk (when used by form) or delete.
   * @param $ctid
   *   Only used when action is not bulk.
   *
   * Route - /survey/:sid/respondents/bulk
   * Route - /survey/:sid/respondents/:ctid/delete
   */
  public function survey_respondents_manage($sid, $action, $ctid = null) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      show_404();
    }
    else if (!has_permission('manage respondents any survey')) {
      show_403();
    }
    
    switch ($action) {
      case 'bulk':
        
        $selected_respondents = $this->input->post('respondents-check');
        // No respondents to act. Just redirect.
        if (!$selected_respondents) {
          redirect($survey->get_url_respondents());
        }
        
        // Is the action to be applied to all the respondents?
        $all_selected = $this->input->post('respondents-check-all');
        
        $successes = 0;
        $with_data = 0;
        // Bulk action - delete.
        if ($this->input->post('respondent-delete') == 'respondent-delete') {
          
          if ($all_selected) {
            // Delete all.
            $call_tasks = $this->call_task_model->get_all($sid);
            // Loop over all.
            foreach ($call_tasks as $call_task) {
              // Only call tasks without data can be deleted.
              $activity = $call_task->get_activity();
              if (empty($activity)){
                // Delete.
                if ($this->call_task_model->delete($call_task->ctid)) {
                  $successes++;
                }
              }
              else {
                $with_data++;
              }
            }
          }
          else {
            // Loop over selected.
            foreach ($selected_respondents as $value) {
              $call_task = $this->call_task_model->get($value);
              
              if ($call_task && $call_task->survey_sid == $sid) {
              // Only call tasks without data can be deleted.
                $activity = $call_task->get_activity();
                if (empty($activity)){
                  if ($this->call_task_model->delete($call_task->ctid)) {
                    $successes++;
                  }
                }
                else {
                  $with_data++;
                }
              }
              // else skip silently.
              // Invalid respondent. Only through ID manipulation.
            }
          }
          
          if ($successes) {
            Status_msg::success($successes . " respondents successfully deleted.");
          }
          if ($with_data) {
            Status_msg::error($with_data . " respondents couldn't be deleted because there's data in the database.");
          }
          
        }
        
        break;
      case 'delete':
        // Verify CSRF token in url.
        verify_csrf_get();
        
        $call_task = $this->call_task_model->get($ctid);
        
        if ($call_task && $call_task->survey_sid == $sid) {
          $activity = $call_task->get_activity();
          if (empty($activity)){
            // Delete.
            if ($this->call_task_model->delete($call_task->ctid)) {
              Status_msg::success("Respondent successfully deleted.");
            }
            else {
              Status_msg::error("An error occurred while deleting the respondent. Please try again.");
            }
          }
          else {
            Status_msg::error("The respondent couldn't be deleted because there's data in the database.");
          }
        }
        else {
          show_error('Something went wrong...');
        }
        break;
    }
    
    redirect($survey->get_url_respondents());
  }

  /**
   * Enketo API
   * Converts the survey xml file to html for enketo to use
   *
   * @param int $sid
   *   The survey id
   *
   * JSON output:
   * status : {
   *   code : ,
   *   message:
   * },
   * xml_form : the xml form
   */
  public function api_survey_xslt_transform($sid) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      return $this->api_output(404, 'Invalid survey.', array('xml_form' => NULL));
    }
    else if (!$survey->has_xml()) {
      return $this->api_output(500, 'Xml file not present.', array('xml_form' => NULL));
    }
    else if (!has_permission('enketo collect data any') && !has_permission('enketo testrun any')) {

      if (!has_permission('enketo collect data assigned') && !has_permission('enketo testrun assigned')) {
        return $this->api_output(403, 'Not allowed.', array('xml_form' => NULL));
      }
      // Is assigned?
      else if (!$survey->is_assigned_agent(current_user()->uid)) {
        return $this->api_output(403, 'Not allowed.', array('xml_form' => NULL));
      }

    }
    // TODO: Collect Data: Check for other restrictions (like status)

    $this->load->helper('xslt_transformer');

    $xslt_transformer = Xslt_transformer::build($survey->get_xml_full_path());
    $result = $xslt_transformer->get_transform_result_sxe()->asXML();

    return $this->api_output(200, 'Ok!', array('xml_form' => $result));
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
   * status : {
   *   code : ,
   *   message:
   * },
   * respondents : Call_task_entity[]
   */
  public function api_survey_request_respondents($sid) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      return $this->api_output(404, 'Invalid survey.', array('respondents' => NULL));
    }
    else if (!$survey->has_xml()) {
      return $this->api_output(500, 'Xml file not present.', array('xml_form' => NULL));
    }
    else if (!has_permission('enketo collect data any') && !has_permission('enketo collect data assigned')) {
      return $this->api_output(403, 'Not allowed.', array('respondents' => NULL));
    }
    else if (!has_permission('enketo collect data any') && has_permission('enketo collect data assigned')) {
      // Is assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        return $this->api_output(403, 'Not allowed.', array('respondents' => NULL));
      }
    }
    // TODO: Collect Data: Check for other restrictions (like status)

    // Max to reserve - from config.
    $max_to_reserve = $this->config->item('aw_enketo_call_tasks_reserve');

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

  /**
   * Enekto API
   * Enketo submits data through AJAX but since it is a form submission
   * a CSRF token is required.
   *
   * JSON output:
   * status : {
   *   code : ,
   *   message:
   * },
   * csrf : string token
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
   * Enekto API
   * Enketo submission handler.
   *
   * JSON output:
   * status : {
   *   code : ,
   *   message:
   * }
   */
  public function api_survey_enketo_form_submit($sid) {
    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      return $this->api_output(404, 'Invalid survey.', array('respondents' => NULL));
    }
    else if (!$survey->has_xml()) {
      return $this->api_output(500, 'Xml file not present.', array('xml_form' => NULL));
    }
    else if (!has_permission('enketo collect data any') && !has_permission('enketo collect data assigned')) {
      return $this->api_output(403, 'Not allowed.');
    }

    $respondent = $this->input->post('respondent');
    $ctid = (int) $respondent['ctid'];

    $call_task = $this->call_task_model->get($ctid);
    if (!$call_task || $call_task->survey_sid == NULL) {
      return $this->api_output(500, 'Invalid call task.');
    }
    else if (!$call_task->is_assigned()) {
      return $this->api_output(500, 'User not assigned to call task.');
    }

    // After knowing that the call task is valid, knowing to who it belongs
    // is the first thing. All the other validations will be done when
    // the data is submitted under the right circumstances.

    // If the same computer is shared by different users it may happen
    // that an user uploads data another user left in the localStorage.
    // We do not save that data, but we send a response to keep it in
    // the localstorage.
    // The call task can't be resolved.
    // There has to be someone assigned to it.
    if (!$call_task->is_resolved() && $call_task->is_assigned()) {
      // If another user is assigned send response.
      if (current_user()->uid != $call_task->assignee_uid){
        return $this->api_output(201, 'Submitting data for another user.');
      }
      // Another scenario where we need to keep the data:
      // The current user worked on survey A and left data in the
      // localstorage. Now the user is working on survey B and data for
      // survey A gets submitted. The assigned user is the current user
      // but the data is for another survey. The data should be kept until
      // the user submits it with the correct survey.
      else if ($call_task->is_assigned(current_user()->uid) && $call_task->survey_sid != $sid) {
        return $this->api_output(201, 'Submitting data for another survey.');
      }
    }

    // Reaching this point we know:
    // - Call task is assigned to the current_user.
    // - The survey to which the call task is assigned is the one for
    // which data is being submitted.

    // Now that we know that the call task does not belong to another user
    // let's do some more validations.
    if (!has_permission('enketo collect data any') && has_permission('enketo collect data assigned')) {
      // Is the user assigned?
      if (!$survey->is_assigned_agent(current_user()->uid)) {
        return $this->api_output(403, 'Not allowed.');
      }
    }

    // TODO: Collect Data: Check for other restrictions (like status)

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

  /**
   * Survey API
   * API to manage survey agents.
   *
   * JSON output:
   * status : {
   *   code : ,
   *   message:
   * }
   */
  public function api_survey_manage_agents($sid) {
    if (!has_permission('assign agents')) {
      return $this->api_output(403, 'Not allowed.');
    }

    $uid = (int) $this->input->post('uid');
    $action = $this->input->post('action');

    $survey = $this->survey_model->get($sid);
    if (!$survey) {
      return $this->api_output(500, 'Invalid survey.');
    }
    // TODO : api_survey_assign_agents : additional checks (survey in right status, the user can be assigned | unassigned)

    $user = $this->user_model->get($uid);
    if (!$user || !$user->is_active()) {
      return $this->api_output(500, 'Invalid user.');
    }

    if ($action == 'assign' && !$user->has_role(ROLE_CC_AGENT)) {
      return $this->api_output(500, 'User is not an agent.');
    }

    $needs_saving = FALSE;
    if($action == 'assign') {
      // Returns true if agent is actually added.
      if ($survey->assign_agent($user->uid)) {
        $needs_saving = TRUE;
      }
    }
    else {
      // Returns true if agent is actually removed.
      if ($survey->unassign_agent($user->uid)) {
        $needs_saving = TRUE;
      }
    }

    // Only save if needed
    if ($needs_saving && !$this->survey_model->save($survey)) {
      return $this->api_output(500, 'Failed saving the survey.');
    }

    return $this->api_output(200, 'Ok!');

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

  /**
   * Produces output as JSON for API's
   *
   * JSON output:
   * status : {
   *   code : ,
   *   message:
   * }
   * extra fields
   *
   * @param int $code (default 200)
   *   Value for status.code
   * @param string $msg (default Ok!)
   *   Value for status.message
   * @param array $extra
   *   Any extra fields
   *
   * @return TRUE
   *   This method only sets the output using the output class.
   *   The script execution is not terminated so it is recomendend to
   *   use it as return $this->api_output()
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

  /**
   * The file upload library does not interact with the form validation.
   * To trigger an error if something went wrong we use the approach
   * specified at:
   * http://keighl.com/post/codeigniter-file-upload-validation/
   * Form validation callback.
   */
  public function _cb_survey_respondents_add_file_handle() {
    if (isset($_FILES['survey_respondents_file']) && !empty($_FILES['survey_respondents_file']['name'])) {
      if ($this->upload->do_upload('survey_respondents_file')) {
        // Set a $_POST value with the results of the upload to use later.
        $upload_data = $this->upload->data();
        $_POST['survey_respondents_file'] = $upload_data;
        return true;
      }
      else {
        // possibly do some clean up ... then throw an error
        $this->form_validation->set_message('_cb_survey_respondents_add_file_handle', $this->upload->display_errors());
        return false;
      }
    }
    else  {
      // Nothing was uploaded. That's ok.
      $this->form_validation->set_message('_cb_survey_respondents_add_file_handle', 'Please upload a file.');
      return false;
    }
  }


}
/* End of file survey.php */
/* Location: ./application/controllers/survey.php */