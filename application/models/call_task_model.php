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
    $sid = (int) $sid;
    
    $result = $this->mongo_db
      ->where('survey_sid', $sid)
      ->orderBy(array('created' => 'desc'))
      ->get(self::COLLECTION);
    
    $call_tasks = array();
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    return $call_tasks;
  }
  
  /**
   * Returns all the Call tasks of a given survey paginated.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * @param int $page
   *  Current page.
   * @param int $items_per_page
   *  Items to show per page.
   * 
   * @return array of Call_task_entity
   */
  public function get_all_paginated($sid, $page = 1, $items_per_page = 50) {
    $sid = (int) $sid;
    $offset = ($page - 1) * $items_per_page;
    
    $result = $this->mongo_db
      ->where('survey_sid', $sid)
      ->orderBy(array('created' => 'desc'))
      ->offset($offset)
      ->limit($items_per_page)
      ->get(self::COLLECTION);
    
    $call_tasks = array();
    foreach ($result as $value) {
      $call_tasks[] = Call_task_entity::build($value);
    }
    
    return $call_tasks;
  }
  
  /**
   * Returns the call task count.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * 
   * @return array of Call_task_entity
   */
  public function get_total_count($sid) {
    $sid = (int) $sid;
    $result = $this->mongo_db
      ->where('survey_sid', $sid)
      ->count(self::COLLECTION);
    
    return $result;
  }
  
  /**
   * Returns all the Call tasks of a given survey that are not yet
   * assigned to an agent.
   *  
   * @param int $sid
   *  Since all the call tasks are bound to a survey its id is needed.
   * 
   * @return array of Call_task_entity
   */
  public function get_available($sid) {
    $sid = (int) $sid;
    
    $result = $this->mongo_db
      ->where('survey_sid', $sid)
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
   * Returns all the Call tasks of a given agent for a given survey that
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
    $sid = (int) $sid;
    $uid = (int) $uid;
    
    $call_tasks = array();
    
    // A call should only be resolved when the last status in the activity
    // array matches one of the $complete_statues. However, we are searching
    // for one of those statuses anywhere in the array. We assume this is ok
    // because it's up to the system to prevent adding new statuses to the
    // array after one of those is added. Therefore this is a safe assumption.
    $result = $this->mongo_db
      ->where('assignee_uid', $uid)
      ->where('survey_sid', $sid)
      ->whereIn('activity.code', Call_task_status::$resolved_statuses)
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
            'activity.code' => array('$in' => array(Call_task_status::NO_REPLY), '$nin' => Call_task_status::$resolved_statuses)
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
   * Returns all the Call tasks of a given agent for a given survey that
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
    $sid = (int) $sid;
    $uid = (int) $uid;
    
    $call_tasks = array();
    
    $result = $this->mongo_db
      ->where('assignee_uid', $uid)
      ->where('survey_sid', $sid)
      ->whereIn('activity.code', array(Call_task_status::CANT_COMPLETE))
      ->whereNotIn('activity.code', array_merge(Call_task_status::$resolved_statuses, array(Call_task_status::NO_REPLY)))
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
            'activity.code' => array('$in' => array(Call_task_status::NO_REPLY), '$nin' => Call_task_status::$resolved_statuses)
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
   * Returns all the Call tasks of a given agent for a given survey that
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
    $sid = (int) $sid;
    $uid = (int) $uid;
    
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
    $ctid = (int) $ctid;
    
    $result = $this->mongo_db
      ->where('ctid', $ctid)
      ->get(self::COLLECTION);
    
    if (!empty($result)) {
      return Call_task_entity::build($result[0]);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Assigns Call Tasks to user. (Reserves them)
   * 
   * Mongodb doesn't support limit on updates and the assignment of call tasks
   * needs to be an atomic operation. For this the findAndModify command is
   * being used.
   * This is not a scalable solution, although is a valid one since the amount
   * of call tasks to assign won't be that high.
   * 
   * @param int $sid
   *   The survey id
   * @param int $uid
   *   The user to who the call tasks are going to be assigned
   * @param int $amount
   *  The amount of call tasks to assign.
   * 
   * @return mixed
   *   Array of Call_task_entity or FALSE if no call tasks available
   */
  public function reserve($sid, $uid, $amount) {
    $sid = (int) $sid;
    $uid = (int) $uid;
    
    $call_tasks = array();
    for ($i = 0; $i < $amount; $i++) { 
      $result = $this->mongo_db->command(array(
        'findAndModify' => self::COLLECTION,
        'query' => array(
          'survey_sid' => (int) $sid,
          'assignee_uid' => NULL
        ),
        'update' => array(
          '$set' => array(
            'assigned' => Mongo_db::date(),
            'updated' => Mongo_db::date(),
            'assignee_uid' => (int) $uid
          )
        ),
        'new' => TRUE,
        'sort' => array('created' => -1),
      ));
      
      if (empty($result['value'])) {
        break;
      }
      
      $call_tasks[] = Call_task_entity::build($result['value']);
    }
    
    return empty($call_tasks)? FALSE : $call_tasks;
  }
  
  /**
   * Un-reserves tasks without activity that were assigned more than
   * 3 days ago.
   * 
   * @param int $sid
   *   Since all the call tasks are bound to a survey its id is needed.
   * 
   * @uses config item aw_enketo_call_tasks_reserve_exprire
   * 
   * @return bool
   *   Whether something was updated or not.
   */
  public function clean_expired_reserve($sid) {
    $sid = (int) $sid;
    
    $expire_time = $this->config->item('aw_enketo_call_tasks_reserve_exprire');    
    return $this->mongo_db
      ->where('survey_sid', $sid)
      ->where('activity', array())
      ->whereLte('assigned', Mongo_db::date(time() - $expire_time))
      ->set('assigned', NULL)
      ->set('assignee_uid', NULL)
      ->updateAll(self::COLLECTION);
  }

  /**
   * Saves a Call Task to the database.
   * If the call task is not saved yet, its id will be added to the 
   * call_task_entity.
   * @param Call_task_entity (by reference)
   * 
   * @return boolean
   *   Whether or not the save was successful.
   */
  public function save(Call_task_entity &$entity) {
    // Set update date:
    $entity->updated = Mongo_db::date();
    
    $prepared_data = array();
    foreach ($entity as $field_name => $field_value) {
      $prepared_data[$field_name] = $field_value;
    }
    
    if ($entity->is_new()) {
      $entity->ctid = increment_counter(self::COUNTER_COLLECTION);
      $prepared_data['ctid'] = $entity->ctid;
      // Set creation date:
      $prepared_data['created'] = Mongo_db::date();
      
      $result = $this->mongo_db->insert(self::COLLECTION, $prepared_data);
      
      return $result !== FALSE ? TRUE : FALSE;
      
    }
    else {
      $result = $this->mongo_db
        ->set($prepared_data)
        ->where('ctid', $entity->ctid)
        ->update(self::COLLECTION);
      
      return $result !== FALSE ? TRUE : FALSE;
    }
    
  }
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */