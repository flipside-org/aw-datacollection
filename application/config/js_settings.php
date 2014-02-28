<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Js_Settings
|--------------------------------------------------------------------------
|
| Configuration file for js_settings library.
| Available properties:
| - defaults: The default values to set upon initialization.
|
*/
$config['defaults'] = array();
$config['defaults']['base_url'] = base_url();
$config['defaults']['check_connection_url'] = base_url('assets/checkforconnection.php');
