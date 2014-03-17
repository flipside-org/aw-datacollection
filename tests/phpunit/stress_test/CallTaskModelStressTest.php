<?php

class CallTaskModelStressTest extends PHPUnit_Framework_TestCase {

  private static $CI;

  public static function setUpBeforeClass() {
    self::$CI = &get_instance();
    self::$CI->load->model('call_task_model');

    //self::_fix_data();
    self::_fix_data_random();
  }
  
  public static function _fix_data_random() {
    $date = Mongo_db::date();
    
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    for ($total_ct = 1; $total_ct <= 100000; $total_ct++) {
      $resp = array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => (string)(1000000000000 + $total_ct),
        'created' => $date,
        'updated' => $date,
        'assigned' => NULL,
        'author' => 1,
        'assignee_uid' => NULL,
        'survey_sid' => rand(1, 5),
        'activity' => array()
      );
      
      // 4 in 5 to be assigned 
      if (rand(1,5) <= 4) {
        $assignee = rand(1, 3);
        $resp['assigned'] = $date;
        $resp['assignee_uid'] = $assignee;
        $status = array(
          'message' => NULL,
          'author' => $assignee,
          'created' => $date
        );
        
        // 3 in 5 to be resolved
        if (rand(1,5) <= 3) {
          // Resolved.
          // 1 in 5 to be 5 no reply 
          if (rand(1,5) == 1) {
            // 5 no reply.
            $status['code'] = Call_task_status::NO_REPLY;
            for ($i = 0; $i < Call_task_status::THRESHOLD_NO_REPLY; $i++) {
              $resp['activity'][] = $status;
            }
          }
          else {
            // Resolved with successful.
            for ($i = 0; $i < rand(1,4); $i++) {
              $status['code'] = Call_task_status::NO_REPLY;
              $resp['activity'][] = $status;
            }
            $status['code'] = Call_task_status::SUCCESSFUL;
            $resp['activity'][] = $status;
          }
        }
        else {
          // Unresolved.
          for ($i = 0; $i < rand(1,4); $i++) {
            $status['code'] = Call_task_status::NO_REPLY;
            $resp['activity'][] = $status;
          }
  
          $status['code'] = Call_task_status::CANT_COMPLETE;
          $resp['activity'][] = $status;
        }
        
      }
      
      self::$CI->mongo_db->insert(Call_task_model::COLLECTION, $resp);
    }
    
    // Add index: ctid.
    self::$CI->mongo_db->addIndex(Call_task_model::COLLECTION, array('ctid' => 1));
    // Add index: survey_sid.
    self::$CI->mongo_db->addIndex(Call_task_model::COLLECTION, array('survey_sid' => 1));
  }

  public static function _fix_data() {
    $date = Mongo_db::date();
    // We need to generate a large amount of varied data but we need
    // to ensure that is always the same.
    $surveys = array(1, 2, 3, 4, 5);
    $no_reply_x5 = array(1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0); // once every 17
    $amount_no_reply = array(0, 0, 1, 0, 0, 1, 0, 2, 0, 0, 0, 2, 0, 3, 0, 0, 0, 1, 3, 0, 0, 0);
    $final_status = array(
      Call_task_status::SUCCESSFUL,
      Call_task_status::NO_CONSENT,
      Call_task_status::NUMBER_CHANGE,
      Call_task_status::DISCARD,
      Call_task_status::INVALID_NUMBER,
      Call_task_status::SUCCESSFUL,
      Call_task_status::SUCCESSFUL,
      Call_task_status::SUCCESSFUL,
      Call_task_status::DISCARD,
      Call_task_status::SUCCESSFUL,
      Call_task_status::SUCCESSFUL,
      Call_task_status::NO_CONSENT,
      Call_task_status::SUCCESSFUL,
      Call_task_status::SUCCESSFUL,
      Call_task_status::NO_CONSENT,
      Call_task_status::SUCCESSFUL,
    );
    
    // Clean db!
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
    
    //////////////////////////////////////////////////////////////
    // Not assigned:
    for ($total_ct = 1; $total_ct <= 10000; $total_ct++) {
      $resp = array(
        'ctid' => increment_counter('call_task_ctid'),
        'number' => (string)(1000000000000 + $total_ct),
        'created' => $date,
        'updated' => $date,
        'assigned' => NULL,
        'author' => 1,
        'assignee_uid' => NULL,
        'survey_sid' => get_next($surveys),
        'activity' => array()
      );
      
      self::$CI->mongo_db->insert(Call_task_model::COLLECTION, $resp);
    }
    
    //////////////////////////////////////////////////////////////
    // Assigned
    // 30000 per user (3 users)
    for ($assignee = 1; $assignee <= 3; $assignee ++) {
      $status = array(
        'message' => NULL,
        'author' => $assignee,
        'created' => $date
      );
      
      // Resolved 20000
      for ($total_ct = 1; $total_ct <= 20000; $total_ct++) {
        $resp = array(
          'ctid' => increment_counter('call_task_ctid'),
          'number' => (string)(1000000000000 + ($assignee * 100000) + $total_ct),
          'created' => $date,
          'updated' => $date,
          'assigned' => $date,
          'author' => 1,
          'assignee_uid' => $assignee,
          'survey_sid' => get_next($surveys),
        );
          
        // Some no-reply.
        if (get_next($no_reply_x5)) {
          $status['code'] = Call_task_status::NO_REPLY;
          for ($i = 0; $i < Call_task_status::THRESHOLD_NO_REPLY; $i++) {
            $resp['activity'][] = $status;
          }
        }
        else {
          for ($i = 0; $i < get_next($amount_no_reply); $i++) {
            $status['code'] = Call_task_status::NO_REPLY;
            $resp['activity'][] = $status;
          }
          $status['code'] = get_next($final_status);
          $resp['activity'][] = $status;
        }
        
        self::$CI->mongo_db->insert(Call_task_model::COLLECTION, $resp);
      }
      
      // Unresolved 10000
      for ($total_ct = 1; $total_ct <= 10000; $total_ct++) {
        $resp = array(
          'ctid' => increment_counter('call_task_ctid'),
          'number' => (string)(1000000000000 + ($assignee * 1000000) + $total_ct),
          'created' => $date,
          'updated' => $date,
          'assigned' => $date,
          'author' => 1,
          'assignee_uid' => $assignee,
          'survey_sid' => get_next($surveys),
        );
        
        for ($i = 0; $i < get_next($amount_no_reply); $i++) {
          $status['code'] = Call_task_status::NO_REPLY;
          $resp['activity'][] = $status;
        }

        $status['code'] = Call_task_status::CANT_COMPLETE;
        $resp['activity'][] = $status;
        
        self::$CI->mongo_db->insert(Call_task_model::COLLECTION, $resp);
      }
    }

    // Add index: ctid.
    self::$CI->mongo_db->addIndex(Call_task_model::COLLECTION, array('ctid' => 1));
    // Add index: survey_sid.
    self::$CI->mongo_db->addIndex(Call_task_model::COLLECTION, array('survey_sid' => 1));
  }

  public static function tearDownAfterClass() {
    // Clean up your mess.
    self::$CI->mongo_db->dropDb('aw_datacollection_test');
  }

  public function test_get_call_tasks() {
    $time = microtime(true);
    // Get only one call task.
    $get1 = self::$CI->call_task_model->get(50000);
    $this->assertLessThan(0.001, microtime(true) - $time);
  }

  public function test_get_all_call_tasks() {
    for ($s = 1; $s <= 5; $s++) {
      $time = microtime(true);
      // Get for survey 1.
      $all = self::$CI->call_task_model->get_all($s);
      $this->assertLessThan(2.000, microtime(true) - $time, "get_all for survey: $s");
      unset($all);
    }
  }

  public function test_get_available_call_tasks() {
    for ($s = 1; $s <= 5; $s++) {
      $time = microtime(true);
      $available = self::$CI->call_task_model->get_available($s);
      $this->assertLessThan(0.400, microtime(true) - $time, "get_available for survey: $s");
      unset($available);
    }
  }
  public function test_get_user_unresolved_call_tasks() {
    for ($s = 1; $s <= 5; $s++) {
      for ($u = 1; $u <= 3; $u++) {
        $time = microtime(true);
        $unresolved = self::$CI->call_task_model->get_unresolved($s, $u);
        $this->assertLessThan(1.000, microtime(true) - $time, "get_unresolved for survey: $s and user: $u");
        unset($unresolved);
      }
    }
  }

  public function test_get_user_resolved_call_tasks() {
    for ($s = 1; $s <= 5; $s++) {
      for ($u = 1; $u <= 3; $u++) {
        $time = microtime(true);
        // Get for survey 1.
        $resolved = self::$CI->call_task_model->get_unresolved($s, $u);
        $this->assertLessThan(1.000, microtime(true) - $time, "get_resolved for survey: $s and user: $u");
        unset($resolved);
      }
    }
  }

  public function test_assign_call_tasks() {
    for ($s = 1; $s <= 5; $s++) {
      for ($u = 1; $u <= 3; $u++) {
        $time = microtime(true);
        // Get for survey 1.
        $assigned = self::$CI->call_task_model->reserve($s, $u, 1);
        $this->assertLessThan(0.055, microtime(true) - $time, "reserve for survey: $s and user: $u");
        unset($assigned);
      }
    }
  }

}
?>