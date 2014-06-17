<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File survey1.fix.php
 * Fixtures for survey1
 * 
 * Since the id comes from autoincrement, the loading order is important. 
 */
  
  $survey = new Survey_entity(array(
    // Sid will be 1 (auto increment)
    'title' => 'Household survey',
    'client' => 'For a better world',
    'status' => Survey_entity::STATUS_DRAFT,
    'introduction' => 'The text the agent has to read. To be submitted by John.',
    'description' => 'A household survey in the Amazons among 400 indigenous families.'
  ));
  $this->survey_model->save($survey);