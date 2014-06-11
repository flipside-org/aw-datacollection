<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File survey2.fix.php
 * Fixtures for survey2
 * 
 * Since the id comes from autoincrement, the loading order is important. 
 */
 
  // Copy files for survey : Handlebars vs something else
  copy('resources/fixtures_data/survey_2_xls.xls', 'files/surveys/survey_2_xls.xls');
  copy('resources/fixtures_data/survey_2_xml.xml', 'files/surveys/survey_2_xml.xml');
  
  // Survey 2.
  $survey = new Survey_entity(array(
    // Sid will be 2 (auto increment)
    'title' => 'Handlebars - Applied knowledge',
    'client' => 'Flipside',
    'status' => Survey_entity::STATUS_OPEN,
    'goal' => 20,
    'introduction' => 'Hi, my name is ______ and I am calling on behalf of Handlebars. Handlebars is interested in learning more about you and your relation with it. You are being contacted because of your participation in the workshop held near your house. We would like to ask you some questions about your coffee consumption while using handlebars. Your participation is very important, because your responses will help us improve our framework and thus make it more usable. This survey will only take about 5 minutes of your time.',
    'description' => 'This survey will help us understand how handlebars is used and how it can be improved.',
    'files' => array(
      'xls' => "survey_2_xls.xls",
      'xml' => "survey_2_xml.xml",
      'last_conversion' => array(
        'date' => NULL,
        'warnings' => NULL,
      )
     )
  ));
  // Assign agent, user 3
  $survey->assign_agent(3);
  $this->survey_model->save($survey);
  
  //////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    // uid will be 1 (auto increment)
    'number' => "1000000000001",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 2, // Handlebars - Applied knowledge
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::NO_REPLY,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::NO_REPLY,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));

  $this->call_task_model->save($call_task);

  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    // uid will be 2 (auto increment)
    'number' => "1000000000002",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 2, // Handlebars - Applied knowledge
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::CANT_COMPLETE,
    'message' => 'Not to be done right now. Maybe later.',
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));

  $this->call_task_model->save($call_task);

  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    // uid will be 3 (auto increment)
    'number' => "1000000000003",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 2, // Handlebars - Applied knowledge
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::INVALID_NUMBER,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));

  $this->call_task_model->save($call_task);

  ///////////////////////////////////////////////////////
  
  // Add some respondents to be used for data collection.
  for($r = 0; $r < 100; $r++) {
    // Call task.
    $call_task = new Call_task_entity(array(
      // uid will be from 4 to 104 (auto increment)
      'number' => (string)(2000000000000 + $r),
      'assigned' => Mongo_db::date(),
      'author' => 1, // Admin
      ///'assignee_uid' => NULL // Not assigned
      'survey_sid' => 2, // Handlebars - Applied knowledge
    ));

    $this->call_task_model->save($call_task);
  }