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
define('ROLE_ADMINISTRATOR', 'administrator');
define('ROLE_CC_OPERATOR', 'cc_operator');

$config['roles'] = array(
  ROLE_ADMINISTRATOR => 'Administrator',
  ROLE_CC_OPERATOR => 'CC Operator'
);

$config['permissions'] = array(
  // Users.
  'edit own account' => array(ROLE_LOGGED),
  'edit any account' => array(ROLE_ADMINISTRATOR),
  'create account' => array(ROLE_ADMINISTRATOR),
  'view user list' => array(ROLE_ADMINISTRATOR),
  
  // Surveys.
  'view survey list' => array(ROLE_LOGGED),
  'view survey page' => array(ROLE_LOGGED),
  'edit any survey' => array(ROLE_ADMINISTRATOR),
  'create survey' => array(ROLE_ADMINISTRATOR),
  'delete any survey' => array(ROLE_ADMINISTRATOR),
  'download survey files' => array(ROLE_LOGGED),
);
