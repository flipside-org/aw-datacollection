<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Status restrictions
|--------------------------------------------------------------------------
|
| Status restriction for surveys
|
|
|
|
|
*/

// Local vars
$draft = Survey_entity::STATUS_DRAFT;
$open = Survey_entity::STATUS_OPEN;
$closed = Survey_entity::STATUS_CLOSED;
$canceled = Survey_entity::STATUS_CANCELED;

$config['status_restrictions'] = array(
  'view survey page' => array($draft, $open, $closed, $canceled),
  // Users that can only see surveys to which they are assigned have a
  // different status permission.
  'view assigned survey page' => array($open, $closed, $canceled),
  
  'enketo collect data' => array($open),
  'enketo testrun' => array($draft, $open, $closed, $canceled),
  
  'manage agents' => array($draft, $open),
  
  'delete any survey' => array($draft),
);
