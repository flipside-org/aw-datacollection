<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
    $collection = $CI->config->item('aw_mongo_counter_collection');
    
    $result = $CI->mongo_db->command(array(
      'findAndModify' => $collection,
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
    $collection = $CI->config->item('aw_mongo_counter_collection');
    
    $result = $CI->mongo_db
      ->set(array('counter' => 0 ))
      ->where(array('_id' => $counter))
      ->update($collection);
  }
}

// ------------------------------------------------------------------------

/* End of file mongo_counter.php */
/* Location: ./application/helpers/mongo_counter.php */