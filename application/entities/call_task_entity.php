<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Entity
 * Base class for entities.
 */
class Call_task_entity extends Entity {
  // Class is empty for now but can become useful.
  // Every entity should extend this class.
}


class Call_task_status {

  const SUCCESSFUL = 3;
  const NO_REPLY = 991;
  const INVALID_NUMBER = 992;
  const NO_INTEREST = 993;
  const NUMBER_CHANGE = 994;
  const CANT_COMPLETE = 995;
  const DISCARD = 996;
  
  static $labels = array(
    self::SUCCESSFUL => 'Successful',
    self::NO_REPLY => 'No reply',
    self::INVALID_NUMBER => 'Invalid number',
    self::NO_INTEREST => 'No interest',
    self::NUMBER_CHANGE => 'Number changed',
    self::CANT_COMPLETE => "Can't complete",
    self::DISCARD => 'Discarded',
  );
}
/* End of file entity.php */
/* Location: ./application/entities/call_task_entity.php */