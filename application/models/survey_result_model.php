<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the Survey Result Entity.
// Since the model works with user entity its safe to load it here.
load_entity('survey_result');

/**
 * Survey Result model.
 */
class Survey_result_model extends CI_Model {
  
  /**
   * Mongo db collection for this model.
   */
  const COLLECTION = 'survey_results';
  
  /**
   * Mongo db counter collection for this model.
   */
  const COUNTER_COLLECTION = 'survey_result_srid';
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }
  
  /**
   * Returns the survey result with the given srid
   * @param int srid
   * 
   * @return Survey_result_entity
   */
  public function get($srid) {
    $result = $this->mongo_db
      ->where('srid', (int) $srid)
      ->get(self::COLLECTION);
    
    if (!empty($result)) {
      return Survey_result_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Saves a survey result to the database.
   * If the survey result is not saved yet, its srid will be added to the 
   * survey_result_entity.
   * @param Survey_result_entity (by reference)
   * 
   * @return boolean
   *   Whether or not the save was successful.
   */
  public function save(Survey_result_entity &$entity) {    
    // To ensure date consistency.
    $date = Mongo_db::date();
    // Set update date:
    $entity->updated = $date;
    
    if ($entity->author === NULL) {
      $entity->author = current_user()->uid;
    }
    
    $prepared_data = array();
    foreach ($entity as $field_name => $field_value) {
      $prepared_data[$field_name] = $field_value;
    }
        
    if ($entity->is_new()) {
      // Add new properties.
      $entity->srid = increment_counter(self::COUNTER_COLLECTION);
      $entity->created = clone $date;
      
      // Add properties to prepared_data.
      $prepared_data['srid'] = $entity->srid;
      $prepared_data['created'] = $entity->created;
      $result = $this->mongo_db->insert(self::COLLECTION, $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('srid', $entity->srid)
        ->update(self::COLLECTION);
      
      return $result !== FALSE ? TRUE : FALSE;
    }
  }
}

/* End of file survey_result_model.php */
/* Location: ./application/models/survey_result_model.php */