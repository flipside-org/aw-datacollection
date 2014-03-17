<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// Load entity base class.
require_once ENTITIES_DIR . 'entity.php';

if ( ! function_exists('load_entity')) {
  /**
   * Loads given entity.
   * _entity.php should be ommited.
   * 
   * @param string $entity
   * @return int 
   */
  function load_entity($entity_name) {
    require_once ENTITIES_DIR . "{$entity_name}_entity.php";
  }
}

// ------------------------------------------------------------------------

/* End of file entities_loader_helper.php */
/* Location: ./application/helpers/entities_loader_helper.php */