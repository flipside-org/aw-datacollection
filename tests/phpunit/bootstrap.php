<?php

/*
 *---------------------------------------------------------------
 * OVERRIDE FUNCTIONS
 *---------------------------------------------------------------
 *
 * This will "override" later functions meant to be defined
 * in core\Common.php, so they throw erros instead of output strings
 */
 
function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
{
	throw new PHPUnit_Framework_Exception($message, $status_code);
}

function show_404($page = '', $log_error = TRUE)
{
	throw new PHPUnit_Framework_Exception($page, 404);
}

function redirect($uri = '', $method = 'location', $http_response_code = 302)
{
	throw new PHPUnit_Framework_Exception($uri);
}

define('ROOT_PATH', dirname(__FILE__) . '/../../');

/*
 *---------------------------------------------------------------
 * HELPER FUNCTIONS
 *---------------------------------------------------------------
 *
 * Some helper functions needed in the tests.
 */
function get_next(&$array) {
  $current = current($array);
  if (next($array) === FALSE) {
    reset($array);
  }
  return $current;
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP
 *---------------------------------------------------------------
 *
 * Bootstrap CodeIgniter from index.php as usual
 */
 
require_once ROOT_PATH . 'index.php';

// Switch db immediately.
get_instance()->mongo_db->switchDb('mongodb://localhost:27017/aw_datacollection_test');

