<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mongo collection to store counters.
 * TODO: Possibly move to config file.
 */
define('MONGO_COUNTER_COLLECTION', 'counters');

/**
 * Increment Counter
 * Increments given counter by one and returns new value.
 * Atomic operation. Guaranteed to be unique.
 * 
 * @param string $counter
 * @return int 
 */
if ( ! function_exists('increment_counter')) {
  function increment_counter($counter) {
    $CI =& get_instance();
    
    $result = $CI->mongo_db->command(array(
      'findAndModify' => MONGO_COUNTER_COLLECTION,
      'query' => array('_id' => $counter),
      'update' => array('$inc' => array('counter' => 1)),
      'upsert' => TRUE,
      'new' => TRUE
    ));
    
    return $result['value']['counter'];
  }
}

/**
 * Reset Counter
 * Resets the counter value to 0.
 * 
 * @param string $counter
 */
if ( ! function_exists('reset_counter')) {
  function reset_counter($counter) {
    $CI =& get_instance();
    
    $result = $CI->mongo_db
      ->set(array('counter' => 0 ))
      ->where(array('_id' => $counter))
      ->update(MONGO_COUNTER_COLLECTION);
  }
}

// ------------------------------------------------------------------------

/* End of file mongo_counter.php */
/* Location: ./application/helpers/mongo_counter.php */