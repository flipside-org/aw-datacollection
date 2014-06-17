<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * File survey3.fix.php
 * Fixtures for survey3
 * 
 * Since the id comes from autoincrement, the loading order is important. 
 */
 
  // Copy files for survey : Meteor usage
  copy('resources/fixtures_data/survey_3_xls.xls', 'files/surveys/survey_3_xls.xls');
  copy('resources/fixtures_data/survey_3_xml.xml', 'files/surveys/survey_3_xml.xml');
  
  $survey = new Survey_entity(array(
      // Sid will be 3 (auto increment)
    'title' => 'Knowledge of HTML',
    'client' => 'Flipside',
    'goal' => 3,
    'status' => Survey_entity::STATUS_CLOSED,
    'introduction' => 'Hello, my name is ____ and I am calling on behalf of Flipside. We would like to ask you a few questions about HTML. It won\'t take more than 10 minutes.',
    'description' => 'This survey will focus on the understanding of HTML in the local community.',
    'files' => array(
      'xls' => "survey_3_xls.xls",
      'xml' => "survey_3_xml.xml",
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
    'number' => "3000000000001",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
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
    'number' => "3000000000002",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::INVALID_NUMBER,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));

  $this->call_task_model->save($call_task);

  // COMPLETE CALL TASKS
  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    'number' => "3000000000003",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::SUCCESSFUL,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));
  $this->call_task_model->save($call_task);
  
  $call_task_result = new Survey_result_entity(array(
    'author' => 3, // The Agent (The author is usually the assignee)
    'call_task_ctid' => $call_task->ctid,
    'survey_sid' => 3,
    'files' => array(
      'xml' => 'survey_result_1_' . $call_task->ctid . '_3.xml'
    )
  ));
  $this->survey_result_model->save($call_task_result);
  
  copy('resources/fixtures_data/survey_result_1_ctid_3.xml', 'files/survey_results/survey_result_1_' . $call_task->ctid . '_3.xml');

  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    'number' => "3000000000004",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::SUCCESSFUL,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));
  $this->call_task_model->save($call_task);
  
  $call_task_result = new Survey_result_entity(array(
    'author' => 3, // The Agent (The author is usually the assignee)
    'call_task_ctid' => $call_task->ctid,
    'survey_sid' => 3,
    'files' => array(
      'xml' => 'survey_result_2_' . $call_task->ctid . '_3.xml'
    )
  ));
  $this->survey_result_model->save($call_task_result);
  
  copy('resources/fixtures_data/survey_result_2_ctid_3.xml', 'files/survey_results/survey_result_2_' . $call_task->ctid . '_3.xml');

  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    'number' => "3000000000005",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::SUCCESSFUL,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));
  $this->call_task_model->save($call_task);
  
  $call_task_result = new Survey_result_entity(array(
    'author' => 3, // The Agent (The author is usually the assignee)
    'call_task_ctid' => $call_task->ctid,
    'survey_sid' => 3,
    'files' => array(
      'xml' => 'survey_result_3_' . $call_task->ctid . '_3.xml'
    )
  ));
  $this->survey_result_model->save($call_task_result);
  
  copy('resources/fixtures_data/survey_result_3_ctid_3.xml', 'files/survey_results/survey_result_3_' . $call_task->ctid . '_3.xml');

  ///////////////////////////////////////////////////////
  // Call task.
  $call_task = new Call_task_entity(array(
    'number' => "3000000000006",
    'assigned' => Mongo_db::date(),
    'author' => 1, // Admin
    'assignee_uid' => 3, // The Agent
    'survey_sid' => 3, // Knowledge of HTML
  ));

  $call_task->add_status(new Call_task_status(array(
    'code' => Call_task_status::SUCCESSFUL,
    'message' => NULL,
    'author' => 3, // The Agent (The author is usually the assignee)
    'created' => Mongo_db::date()
  )));
  $this->call_task_model->save($call_task);
  
  $call_task_result = new Survey_result_entity(array(
    'author' => 3, // The Agent (The author is usually the assignee)
    'call_task_ctid' => $call_task->ctid,
    'survey_sid' => 3,
    'files' => array(
      'xml' => 'survey_result_4_' . $call_task->ctid . '_3.xml'
    )
  ));
  $this->survey_result_model->save($call_task_result);
  
  copy('resources/fixtures_data/survey_result_4_ctid_3.xml', 'files/survey_results/survey_result_4_' . $call_task->ctid . '_3.xml');
  
  
  // NON USED
  ///////////////////////////////////////////////////////
  // Add some respondents to be used for data collection.
  for($r = 7; $r < 11; $r++) {
    // Call task.
    $call_task = new Call_task_entity(array(
      // uid will be from 4 to 104 (auto increment)
      'number' => (string)(3000000000000 + $r),
      'assigned' => Mongo_db::date(),
      'author' => 1, // Admin
      ///'assignee_uid' => NULL // Not assigned
      'survey_sid' => 3, // Knowledge of HTML
    ));

    $this->call_task_model->save($call_task);
  }