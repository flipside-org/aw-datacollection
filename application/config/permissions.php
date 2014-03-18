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
| - cc_agent
| - administrator
|
|
|
|
|
|
*/

// ROLE_ANONYMOUS is a fake role. It is added to every non logged user.
define('ROLE_ANONYMOUS', 'anonymous');
// ROLE_REGISTERED is a fake role. It is added to every logged user.
define('ROLE_REGISTERED', 'authenticated');

// Some constants to define actual roles.
define('ROLE_ADMINISTRATOR', 'administrator');
define('ROLE_CC_AGENT', 'cc_agent');

$config['roles'] = array(
  ROLE_ADMINISTRATOR => 'Administrator',
  ROLE_CC_AGENT => 'CC Agent'
);

$config['permissions'] = array(
  // Users.
  'edit own account' => array(ROLE_REGISTERED),
  'edit any account' => array(ROLE_ADMINISTRATOR),
  'create account' => array(ROLE_ADMINISTRATOR),
  'view user list' => array(ROLE_ADMINISTRATOR),
  
  // Surveys.
  'view survey list' => array(ROLE_REGISTERED),
  'view survey page' => array(ROLE_REGISTERED),
  'edit any survey' => array(ROLE_ADMINISTRATOR),
  'create survey' => array(ROLE_ADMINISTRATOR),
  'delete any survey' => array(ROLE_ADMINISTRATOR),
  'download survey files' => array(ROLE_REGISTERED),
  'assign agents' => array(ROLE_ADMINISTRATOR),
  
  'collect data with enketo' => array(ROLE_REGISTERED),
  
  'api request csrf token' => array(ROLE_REGISTERED),
);
