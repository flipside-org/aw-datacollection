<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Call Task Entity
 * Base class for entities.
 */
class Call_task_entity extends Entity {
  /********************************
   ********************************
   * Start of Call Task fields.
   * The next variables hold actual survey info that will
   * go in the database.
   * Every field should be of public access.
   */
  
  /**
   * Mongo Id.
   * The mongo Id is immutable. It can not be set when updating documents
   * since it is not used to query for them. Mark it as protected so it 
   * isn't picked up in the model's save method.
   * @var int
   * @access public
   */
  protected $_id = NULL;
  
  /**
   * The call task id.
   * @var string
   */
  public $ctid = NULL;
  
  /**
   * Respondent number. 
   * @var string
   */
  public $number = NULL;
  
  /**
   * Survey to which this respondent is tied. 
   * @var int
   */
  public $survey_sid = NULL;
  
  /**
   * Operator to whom this call was assigned.
   * @var int
   */
  public $assignee_uid = NULL;
  
  /**
   * Activity list of this call.
   * It consists in a array of statuses.
   * @var array Call_task_status
   */
  public $activity = array();
  
  /**
   * Call task author.
   * Normally this will be a moderator or someone with permissions to
   * upload a list of respondents to a survey.
   * @var int
   */
  public $author = NULL;
  
  /**
   * Creation Date.
   * @var date
   * @access public
   */
  public $created = NULL;
  
  /**
   * Update Date.
   * @var date
   * @access public
   */
  public $updated = NULL;
  
  /**
   * Assigned Date.
   * When tha call task was assigned to a user.
   * @var date
   * @access public
   */
  public $assigned = NULL;
   
  /**
   * End of survey fields.
   *******************************/
  
  /**
   * Setting passed to the Call Task entity.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   * 
   * @access protected
   */
  protected $settings = array();

  /**
   * Call Task entity constructor
   * 
   * @param array
   *   User data to construct the Call Task.
   * 
   * @throws Exception
   *   If trying to set an invalid field.
   */
  function __construct($call_task) {
    // The call task's activity array consists on a series of call task status
    // objects and they need to be initialized.
    // To comply with dependency injection this should not be done, however
    // since call task status objects do not exist, nor they should, outside a
    // call task it's not a problem.
    $activity = isset($call_task['activity']) ? $call_task['activity'] : array();
    unset($call_task['activity']);
    foreach ($activity as $activity_element) {
      $this->activity[] = new Call_task_status($activity_element);
    }
    
    // Data will come from the database or it will be sanitized before.
    // We can assume its safe to initialize like this.
    foreach ($call_task as $key => $value) {
      if (!property_exists($this, $key)) {
        // Trying to set a key that doesn't exist in the survey.
        throw new Exception("Invalid field for the call task: $key");
      }
      
      $this->{$key} = $value;
    }
  }
  
  /********************************
   ********************************
   * Start of methods to manage settings.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   */
   
  /**
   * Creates Call_task_entity injecting dependencies.
   * Input params must be the same as in the __construct
   * 
   * @access public
   * @static
   * 
   * @param array
   *   Call Task data to construct the Call Task.
   * 
   * @return Call_task_entity
   */
  public static function build($call_task_data) {
    $call_task = new Call_task_entity($call_task_data);
    
    return $call_task;
  }
  
  /**
   * End of setting methods.
   *******************************/
  
  /********************************
   ********************************
   * Start of Call Task's public methods.
   */
  
  /**
   * Check whether the call task is new or it exists.
   * @access public
   * @return boolean
   */
  public function is_new() {
    return $this->ctid == NULL;
  }
  
  /**
   * Check whether the call task is assigned.
   * @access public
   * @return boolean
   */
  public function is_assigned() {
    return $this->assignee_uid !== NULL;
  }
  
  /**
   * Add a new status to the call Task activity.
   * 
   * @param Call_task_status $new_status
   * 
   * @throws Exception
   *   if the call task is resolved and is not possible to add statuses.
   * 
   * @return $this
   *   To allow chaining.
   */
  public function add_status(Call_task_status $new_status) {
    // A new status can be added if the call task is not resolved.
    if ($this->is_resolved()) {
      throw new Exception("Is not possible to add a new status. The Call Task is resolved.");
    }
    else {
      $this->activity[] = $new_status;
    }
    return $this;
  }
  
  /**
   * Checks whether the call task is resolved.
   * A call task is resolved when:
   * - Has reached the THRESHOLD_NO_REPLY
   * - The last status in the activity array is a resolved one
   *   (see list in Call_task_status::$resolved_statuses)
   * 
   * @return boolean
   */
  public function is_resolved() {
    // Is empty?
    if (empty($this->activity)) {
      return FALSE;
    }
    
    // Check if the no reply hit the threshold.
    $no_reply_count = 0;
    foreach ($this->activity as $status) {
      if ($status->code == Call_task_status::NO_REPLY) {
        $no_reply_count++;
      }
    }
    if ($no_reply_count >= Call_task_status::THRESHOLD_NO_REPLY) {
      return TRUE;
    }
    
    // Check if the last status matches a resolved status.
    // Since they are added sequentially, a resolved status is
    // always the last one.
    $last_status = end($this->activity);
    // reset activity.
    reset($this->activity);
    if (in_array($last_status->code, Call_task_status::$resolved_statuses)) {
      return TRUE;
    }
    
    return FALSE;
  }
   
  /**
   * End of public methods.
   *******************************/
   
  /********************************
   ********************************
   * Start of private and protected methods.
   */
   
  /**
   * End of private and protected methods.
   *******************************/
}


/**
 * Call Task Status
 * Each Call Task has an activity made of statuses.
 * To ease management of such statuses this subclass was created.
 * It is declares in this file because it works in strict relation with the
 * Call Task Entity.
 */
class Call_task_status {
  
  /**
   * Resolved: Successful
   * @const
   */
  const SUCCESSFUL = 3;
  /**
   * Resolved: Invalid Number 
   * @const
   */
  const INVALID_NUMBER = 992;
  /**
   * Resolved: No Interest 
   * @const
   */
  const NO_INTEREST = 993;
  /**
   * Resolved: Number Change 
   * @const
   */
  const NUMBER_CHANGE = 994;
  /**
   * Resolved: Discard 
   * @const
   */
  const DISCARD = 996;
  
  /**
   * No Reply (can be resolved if the Threshold is met.)
   * @const
   */
  const NO_REPLY = 991;
  
  /**
   * Unresolved: Can't Complete
   * @const
   */
  const CANT_COMPLETE = 995;
  
  /**
   * Labels for the statuses.
   * @static
   */
  static $labels = array(
    self::SUCCESSFUL => 'Successful',
    self::NO_REPLY => 'No reply',
    self::INVALID_NUMBER => 'Invalid number',
    self::NO_INTEREST => 'No interest',
    self::NUMBER_CHANGE => 'Number changed',
    self::CANT_COMPLETE => "Can't complete",
    self::DISCARD => 'Discarded',
  );
  
  /**
   * Threshold value for No Reply.
   * When the number of no replies reach this value is considered resolved.
   * @const
   */
  const THRESHOLD_NO_REPLY = 5;
  
  /**
   * List of statuses considered resolved.
   * @static
   */
  static $resolved_statuses = array(
    self::SUCCESSFUL,
    self::INVALID_NUMBER,
    self::NO_INTEREST,
    self::NUMBER_CHANGE,
    self::DISCARD
  );
  
  /**
   * Call task status code.
   * @var int
   */
  public $code = NULL;
  
  /**
   * Call task status message.
   * Although is option is recommended with certain statuses.
   * @var string
   */
  public $message = NULL;
  
  /**
   * Call task status creation date.
   * @var MongoDate
   */
  public $created = NULL;
  
  /**
   * Call task status author.
   * @var int
   */
  public $author = NULL;
  
  /**
   * Call task status constructor
   * 
   * @param array
   *   Call task status data to construct it.
   * 
   * @throws Exception
   *   If trying to set an invalid field.
   */
  function __construct($call_task_status) {
    // Data will come from the database or it will be sanitized before.
    // We can assume its safe to initialize like this.
    foreach ($call_task_status as $key => $value) {
      if (!property_exists($this, $key)) {
        // Trying to set a key that doesn't exist in the survey.
        throw new Exception("Invalid field for the call task status: $key");
      }
      
      $this->{$key} = $value;
    }
  }
  
  /**
   * Eases the creation of Call_task_status by setting some properties.
   * 
   * @access public
   * @static
   * 
   * @param int $code
   *   Call Task Status code
   * @param string $msg
   *   Call Task Status Message.
   * 
   * @throws Exception
   *   If the given code is not valid.
   * 
   * @return Call_task_entity
   */
  static function create($code, $msg) {
    $ct = new Call_task_status(array());
    
    $ct->set_code($code);
    $ct->message = $msg;
    $ct->created = Mongo_db::date();
    $ct->author = current_user()->uid;
    
    return $ct;
  }
  
  /**
   * Validates the given Call Task Status code and sets it.
   * 
   * @param int $code
   *   The Call Task Status code.
   * 
   * @throws Exception
   *   If the given code is not valid.
   * 
   * @return $this
   *   To allow chaining
   */
  public function set_code($code) {
    if (!array_key_exists($code, Call_task_status::$labels)) {
      throw new Exception("Invalid code for a call task status: $code");
    }
    $this->code = $code;
    return $this;
  }
}
/* End of file call_task_entity.php */
/* Location: ./application/entities/call_task_entity.php */