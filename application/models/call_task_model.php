<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Load the user entity.
// Since the model works with user entity its safe to load it here.
load_entity('call_task');

/**
 * Call Task model.
 */
class Call_task_model extends CI_Model {
  
  /**
   * Mongo db collection for this model.
   */
  const COLLECTION = 'call_tasks_test';
  
  /**
   * Mongo db counter collection for this model.
   */
  const COUNTER_COLLECTION = 'call_task_ctid';
  
  /**
   * Model constructor.
   */
  function __construct() {
      parent::__construct();
  }
  
  /**
   * Returns all the Call tasks of a given survey.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * 
   * @return array of Call_task_entity
   */
  public function get_all($sid) {
    $result = $this->mongo_db
      ->where('survey_sid', (int) $sid)
      ->orderBy(array('created' => 'desc'))
      ->get(self::COLLECTION);
    
    $call_tasks = array();
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    return $call_tasks;
  }
  
  /**
   * Returns all the Call tasks of a given survey that are not yet
   * assigned to an operator.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * 
   * @return array of Call_task_entity
   */
  public function get_available($sid) {
    $result = $this->mongo_db
      ->where('survey_sid', (int) $sid)
      ->where('assignee_uid', NULL)
      ->orderBy(array('created' => 'desc'))
      ->get(self::COLLECTION);
    
    $call_tasks = array();
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    return $call_tasks;
  }
  
  /**
   * Returns all the Call tasks of a given operator for a given survey that
   * are resolved.
   * A call task is resolved when:
   * - 1. The last status in the activity array is:
   *    - Successful
   *    - Invalid number
   *    - No interest
   *    - Number change
   *    - Discard
   * - 2. The activity array contains the "No Reply" status 5 times.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * @param int $uid
   *  The id of the user.
   * 
   * @return array of Call_task_entity
   *   The returned Call Task are not ordered because they are fetched in two
   *   requests. The first request gets the call task with a specific
   *   status (1) and the second the ones with 5 no replies (2).
   *   It is not in the scope of this function to order them, they can be
   *   order to one's likes after returned.
   */
  public function get_resolved($sid, $uid) {    
    $call_tasks = array();
    
    $completed_statuses = array(
      Call_task_status::INVALID_NUMBER,
      Call_task_status::SUCCESSFUL,
      Call_task_status::NO_INTEREST,
      Call_task_status::NUMBER_CHANGE,
      Call_task_status::DISCARD,
    );
    
    $result = $this->mongo_db
      ->whereIn('activity.code', $completed_statuses)
      ->get(self::COLLECTION);
    
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    // Get all call tasks with 5 no reply.
    $result = $this->mongo_db->command(array(
      'aggregate' => self::COLLECTION,
      'pipeline' => array(
        array(
          '$match' => array(
            'assignee_uid' => $uid,
            'survey_sid' => $sid,
            'activity.code' => array('$in' => array(Call_task_status::NO_REPLY))
          )
        ),
        array('$unwind' => '$activity'),
        array(
          '$group' => array(
            '_id' => array('ctid' => '$ctid', 'no_reply' => '$activity.code'),
            'no_reply' => array('$sum' => 1)
          )
        ),
        array(
          '$match' => array(
            'no_reply' => 5
          )
        ),
        array(
          '$project' => array(
            '_id' => 0,
            'ctid' => '$_id.ctid'
          )
        )
      )));  
    
    foreach ($result['result'] as $r) {
      $call_tasks[] = $this->get($r['ctid']);
    }
    
    return $call_tasks;
  }
  
  /**
   * Returns the call task with the given ctid
   * 
   * @param int $ctid
   *  The call task id 
   * 
   * @return Call_task_entity
   */
  public function get($ctid) {
    $result = $this->mongo_db
      ->where('ctid', (int) $ctid)
      ->get(self::COLLECTION);
    
    if (!empty($result)) {
      return Call_task_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */