<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File survey1.fix.php
 * Fixtures for survey1
 * 
 * Since the id comes from autoincrement, the loading order is important. 
 */
  
  $survey = new Survey_entity(array(
    // Sid will be 1 (auto increment)
    'title' => 'Meteor usage',
    'client' => 'Flipside',
    'status' => Survey_entity::STATUS_DRAFT,
    'introduction' => 'The text the user has to read.',
    'description' => 'This survey will help us understand the reach of meteor.'
  ));
  $this->survey_model->save($survey);