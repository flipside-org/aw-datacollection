<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the survey entity.
// Since the model work with survey entity its safe to load it here.
load_entity('survey');

/**
 * Survey model.
 */
class Survey_model extends CI_Model {
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }
  
  /**
   * Returns all the surveys as Survey_entity
   * @return array of Survey_entity
   */
  public function get_all() {
    $result = $this->mongo_db
      ->orderBy(array('created' => 'desc'))
      ->get('surveys');
    
    $surveys = array();
    foreach ($result as $value) {
      $surveys[] = new Survey_entity($value);
    }
    
    return $surveys;
  }
  
  /**
   * Returns a specific survey as Survey_entity
   * @param int $sid
   * 
   * @return mixed
   *   Returns survey_entity or false if it doesn't exist.
   */
  public function get($sid) {
    $result = $this->mongo_db
      ->where('sid', (int) $sid)
      ->get('surveys');
    
    if (!empty($result)) {
      return new Survey_entity($result[0]);
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * Deletes a survey by its sid.
   * @param int $sid
   */
  public function delete($sid) {
    $result = $this->mongo_db
      ->where('sid', (int) $sid)
      ->delete('surveys');
  }
  
  /**
   * Saves a survey to the database.
   * If the survey is not saved yet, its id will be added to the 
   * survey_entity.
   * @param Survey_entity (by reference)
   * 
   * @return boolean
   *   Whether or not the save was successful.
   */
  public function save(Survey_entity &$survey_entity) {
    
    $prepared_data = array(
      'title' => $survey_entity->title,
      'status' => $survey_entity->status,
      'files' => $survey_entity->files,
    );
    
    if ($survey_entity->is_new()) {
      $survey_entity->sid = increment_counter('survey_sid');
      $prepared_data['sid'] = $survey_entity->sid;
      
      $result = $this->mongo_db->insert('surveys', $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('sid', $survey_entity->sid)
        ->update('surveys');
      
      return $result !== FALSE ? TRUE : FALSE;
    }
    
  }
}

/* End of file post_model.php */
/* Location: ./application/models/post_model.php */