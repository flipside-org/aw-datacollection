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
| - moderator
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
define('ROLE_MODERATOR', 'moderator');
define('ROLE_CC_AGENT', 'cc_agent');

$config['roles'] = array(
  ROLE_ADMINISTRATOR => 'Administrator',
  ROLE_MODERATOR => 'Moderator',
  ROLE_CC_AGENT => 'CC Agent'
);

$config['permissions'] = array(
  // Users.
  'edit own account'  => array(ROLE_REGISTERED),
  'edit any account'  => array(ROLE_ADMINISTRATOR),
  'create account'    => array(ROLE_ADMINISTRATOR),
  'view user list'    => array(ROLE_ADMINISTRATOR),
  
  // Surveys.
  'view survey list'          => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'view any survey page'      => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'view assigned survey page' => array(ROLE_CC_AGENT),
  'edit any survey'           => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'create survey'             => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'delete any survey'         => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'download survey files'     => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT),
  'assign agents'             => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  
  // Although this permission exists should no be used.
  // It allows non assigned users to collect data which should not happen.
  'enketo collect data any'       => array(),
  'enketo collect data assigned'  => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT),
  'enketo testrun any'            => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR),
  'enketo testrun assigned'       => array(ROLE_CC_AGENT),
  
  'api request csrf token'    => array(ROLE_ADMINISTRATOR, ROLE_MODERATOR, ROLE_CC_AGENT),
);
