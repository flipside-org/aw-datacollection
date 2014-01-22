<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

load_entity('survey');

class Survey_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
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
    
    public function delete($sid) {
      $result = $this->mongo_db
        ->where('sid', (int) $sid)
        ->delete('surveys');
    }
    
    public function save(&$survey_entity) {
      
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