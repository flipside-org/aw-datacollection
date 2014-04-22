<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the survey entity.
// Since the model work with survey entity its safe to load it here.
load_entity('survey');

/**
 * Survey model.
 */
class Survey_model extends CI_Model {
  
  /**
   * Mongo db collection for this model.
   */
  const COLLECTION = 'surveys';
  
  /**
   * Mongo db counter collection for this model.
   */
  const COUNTER_COLLECTION = 'survey_sid';
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }

  /**
   * Returns all the surveys as Survey_entity.
   * It is possible to restrict the query by passing some params.
   * 
   * @param mixed $statuses (optional)
   *   Status or array of statuses to query for. Providing NULL is the same as
   *   providing all the statuses.
   *   Default : NULL
   * 
   * @param int $agent_uid (optional)
   *   Assigned agent.
   * 
   * @return array of Survey_entity
   */
  public function get_all($statuses = NULL, $agent_uid = NULL) {
    if ($statuses !== NULL) {
      if (empty($statuses)) {
        return array();
      }
      $statuses = !is_array($statuses) ? array($statuses) : $statuses;
      $this->mongo_db->whereIn('status', $statuses);
    }
    
    if ($agent_uid) {
      $this->mongo_db->where('agents', (int) $agent_uid);
    }
    
    $result = $this->mongo_db
      ->orderBy(array('created' => 'desc'))
      ->get(self::COLLECTION);
    
    $surveys = array();
    foreach ($result as $value) {
      $surveys[] = Survey_entity::build($value);
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
      ->get(self::COLLECTION);
    
    if (!empty($result)) {
      return Survey_entity::build($result[0]);
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
      ->delete(self::COLLECTION);
    
    return $result !== FALSE ? TRUE : FALSE;
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
  public function save(Survey_entity &$entity) {
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
      $entity->sid = increment_counter(self::COUNTER_COLLECTION);
      $entity->created = clone $date;
      
      // Add properties to prepared_data.
      $prepared_data['sid'] = $entity->sid;
      $prepared_data['created'] = $entity->created;
      $result = $this->mongo_db->insert(self::COLLECTION, $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('sid', $entity->sid)
        ->update(self::COLLECTION);
      
      return $result !== FALSE ? TRUE : FALSE;
    }
    
  }
}

/* End of file post_model.php */
/* Location: ./application/models/post_model.php */