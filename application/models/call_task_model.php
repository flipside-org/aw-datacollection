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
  const COLLECTION = 'call_tasks';
  
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
    
    // A call should only be resolved when the last status in the activity
    // array matches one of the $complete_statues. However, we are searching
    // for one of those statuses anywhere in the array. We assume this is ok
    // because it's up to the system to prevent adding new statuses to the
    // array after one of those is added. Therefore this is a safe assumption.
    $result = $this->mongo_db
      ->where('assignee_uid', $uid)
      ->where('survey_sid', $sid)
      ->whereIn('activity.code', $completed_statuses)
      ->get(self::COLLECTION);
    
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    // Get all call tasks with 5 no reply.
    $result = $this->mongo_db->command(array(
      'aggregate' => self::COLLECTION,
      'pipeline' => array(
        // 1 - Match only the correct call tasks and that have NO_REPLY.
        array(
          '$match' => array(
            'assignee_uid' => $uid,
            'survey_sid' => $sid,
            'activity.code' => array('$in' => array(Call_task_status::NO_REPLY), '$nin' => $completed_statuses)
          )
        ),
        // 2 - Unwind activity array.
        array('$unwind' => '$activity'),
        // 3 - Group by ctid and by no_reply code.
        // The group by activity_code is needed to be able to count only the
        // no replies. Otherwise if a call task had 4 no-reply and a
        // can't complete would be counted as well.
        array(
          '$group' => array(
            '_id' => array('ctid' => '$ctid', 'activity_code' => '$activity.code'),
            'activity_code_count' => array('$sum' => 1)
          )
        ),
        // 4 - Math only the ones with no reply.
        array(
          '$match' => array(
            '_id.activity_code' => Call_task_status::NO_REPLY,
            'activity_code_count' => Call_task_status::THRESHOLD_NO_REPLY
          )
        ),
        // 5 - Project. Only the ctid is needed.
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
   * Returns all the Call tasks of a given operator for a given survey that
   * are unresolved.
   * A call task is unresolved when:
   * - 1. The last status in the activity array is:
   *    - Can't Complete
   * - 2. The activity array contains the "No Reply" status 4 or less times.
   * - 3. None of the complete statuses are in the array.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * @param int $uid
   *  The id of the user.
   * 
   * @return array of Call_task_entity
   *   The returned Call Task are not ordered because they are fetched in two
   *   requests. The first request gets the call task with a specific
   *   status (1) and the second the ones with 4 or less no replies (2).
   *   It is not in the scope of this function to order them, they can be
   *   order to one's likes after returned.
   */
  public function get_unresolved($sid, $uid) {
    $call_tasks = array();
    
    $completed_statuses = array(
      Call_task_status::INVALID_NUMBER,
      Call_task_status::SUCCESSFUL,
      Call_task_status::NO_INTEREST,
      Call_task_status::NUMBER_CHANGE,
      Call_task_status::DISCARD
    );
    
    $result = $this->mongo_db
      ->where('assignee_uid', $uid)
      ->where('survey_sid', $sid)
      ->whereIn('activity.code', array(Call_task_status::CANT_COMPLETE))
      ->whereNotIn('activity.code', array_merge($completed_statuses, array(Call_task_status::NO_REPLY)))
      ->get(self::COLLECTION);
    
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }

    // Get all call tasks with 4 or less no reply.
    $result = $this->mongo_db->command(array(
      'aggregate' => self::COLLECTION,
      'pipeline' => array(
        // 1 - Match only the correct call tasks and that have NO_REPLY.
        array(
          '$match' => array(
            'assignee_uid' => $uid,
            'survey_sid' => $sid,
            'activity.code' => array('$in' => array(Call_task_status::NO_REPLY), '$nin' => $completed_statuses)
          )
        ),
        // 2 - Unwind activity array.
        array('$unwind' => '$activity'),
        // 3 - Group by ctid and by no_reply code.
        // The group by no reply is needed to be able to count only the
        // no replies. Otherwise if a call task had 4 no-reply and a
        // can't complete would be counted as well.
        array(
          '$group' => array(
            '_id' => array('ctid' => '$ctid', 'activity_code' => '$activity.code'),
            'activity_code_count' => array('$sum' => 1)
          )
        ),
        // 4 - Math only the no reply with less then 4.
        array(
          '$match' => array(
            '_id.activity_code' => Call_task_status::NO_REPLY,
            'activity_code_count' => array('$lt' => Call_task_status::THRESHOLD_NO_REPLY)
          )
        ),
        // 5 - Project. Only the ctid is needed.
        array(
          '$project' => array(
            '_id' => 0,
            'ctid' => '$_id.ctid'
          )
        )
      )
    ));  

    foreach ($result['result'] as $r) {
      $call_tasks[] = $this->get($r['ctid']);
    }
    
    return $call_tasks;
  }

  /**
   * Returns all the Call tasks of a given operator for a given survey that
   * are reserved.
   * A call task is reserved when:
   * - It ws assigned to a user but there's no activity.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * @param int $uid
   *  The id of the user.
   * 
   * @return array of Call_task_entity
   */
  public function get_reserved($sid, $uid) {
    $result = $this->mongo_db
      ->where('assignee_uid', $uid)
      ->where('survey_sid', $sid)
      ->where('activity', array())
      ->get(self::COLLECTION);
    
    $call_tasks = array();
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
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