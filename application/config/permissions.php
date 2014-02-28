<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
|
| All application permissions.
| Roles:
| - ALL (This is a false role. The permission will be available to everyone.)
| - anonymous (This is a role assigned by the system.)
| - authenticated (This is a role assigned by the system.)
|
| - cc_operator
| - administrator
|
|
|
|
|
|
*/

// Some constants to define roles.
define('ROLE_ANONYMOUS', 'anonymous');
define('ROLE_LOGGED', 'authenticated');

$config['roles'] = array(
  'administrator' => 'Administrator',
  'cc_operator' => 'CC Operator'
);

$config['permissions'] = array(
  // Users.
  'edit own account' => array(ROLE_LOGGED),
  'edit any account' => array('administrator'),
  'create account' => array('administrator'),
  'view user list' => array('administrator'),
  
  // Surveys.
  'view survey list' => array(ROLE_LOGGED),
  'view survey page' => array(ROLE_LOGGED),
  'edit any survey' => array('administrator'),
  'create survey' => array('administrator'),
  'delete any survey' => array('administrator'),
  'download survey files' => array(ROLE_LOGGED),
);
