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

$config['permissions'] = array(
  'permission name' => array('administrator'),
  'another permission' => array('cc_operator', 'administrator'),
  'all permission' => array('ALL')
);
