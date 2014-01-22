<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Survey_entity extends Entity {
  
  public $sid = null;
  public $title;
  public $status;
  
  public $files = array('xls' => NULL, 'xml' => NULL);
  
  static $allowed_status = array(
    1 => 'Draft',
    2 => 'Open',
    3 => 'Closed',
    99 => 'Canceled'
  );
  
  function __construct($survey) {
    if (isset($survey['title'])) {
      $this->title = $survey['title'];
    }
    
    if (isset($survey['status'])) {
      $this->status = $survey['status'];
    }
    
    if (isset($survey['sid'])) {
      $this->sid = $survey['sid'];
    }
    
    if (isset($survey['files'])) {
      $this->files = $survey['files'];
    }
  }
  
  public function is_new() {
    return $this->sid == null;
  }
  
  public function get_url_view() {
    if ($this->sid == null) {
      throw new Exception("Trying to get link for a nonexistent survey.");       
    }    
    return base_url('survey/' . $this->sid);
  }
  
  public function get_url_edit() {
    if ($this->sid == null) {
      throw new Exception("Trying to get link for a nonexistent survey.");       
    }    
    return base_url('survey/' . $this->sid . '/edit') ;
  }
  
  public function get_url_delete() {
    if ($this->sid == null) {
      throw new Exception("Trying to get link for a nonexistent survey.");       
    }    
    return base_url('survey/' . $this->sid . '/delete');
  }
  
  public function compose_file_name($type) {
    return sprintf('survey_%d_%s.%s', $this->sid, $type, $type);
  }
}

/* End of file survey.php */
/* Location: ./application/entities/survey.php */