<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require_once ENTITIES_DIR . 'entity.php';

/**
 * Loads given entity.
 * _entity.php should be ommited.
 * 
 * @param string $entity
 * @return int 
 */
if ( ! function_exists('load_entity')) {
  function load_entity($entity_name) {
    require_once ENTITIES_DIR . "{$entity_name}_entity.php";
  }
}

// ------------------------------------------------------------------------

/* End of file mongo_counter.php */
/* Location: ./application/helpers/mongo_counter.php */