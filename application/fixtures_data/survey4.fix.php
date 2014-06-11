<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File survey4.fix.php
 * Fixtures for survey4
 * 
 * Since the id comes from autoincrement, the loading order is important. 
 */
 
  $survey = new Survey_entity(array(
    // Sid will be 4 (auto increment)
    'title' => 'Another survey',
    'client' => 'The world',
    'status' => Survey_entity::STATUS_CANCELED,
  ));
  $survey->assign_agent(3);
  $this->survey_model->save($survey);