<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Status restrictions
|--------------------------------------------------------------------------
|
| Status restriction for surveys.
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
  
  // There's no specific permission to view the call activity.
  // It uses the "enketo collect data" permission since a user needs to be
  // able to collect data to view a list of the data he/she collected.
  // However, the "enketo collect data" status restriction can't be used
  // because users can still see the call activity once the survey is closed.
  'view call activity' => array($open, $closed),
  
  'import respondents any survey' => array($draft, $open),
  // Even if the respondent passes the status restriction check, it won't
  // be deleted if there's activity. The user will be warned.
  'delete respondents any survey' => array($draft, $open, $closed, $canceled),
  
  'edit any survey metadata' => array($draft, $open),
  'edit any survey def file' => array($draft),
  
  'export csv data any survey' => array($closed),
);