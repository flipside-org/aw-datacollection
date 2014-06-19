<?php if (!defined('ENVIRONMENT') && ENVIRONMENT == 'demo') exit('No direct script access allowed');

  $minutes_between_resets = 30;
  $control_file = 'reset.demo.last';
  $key_file = 'reset.demo.key';

  // Get date of last reset.
  $last = @unserialize(file_get_contents($control_file));
  
  // Is valid date.
  if (!$last || !($last instanceof DateTime)) {
    // Set counter for the first time.
    $last = new DateTime();
    file_put_contents($control_file, serialize($last));
  }
  
  // Calculate when the next reset should happen.
  $last->add(new DateInterval('PT' . $minutes_between_resets . 'M'));
  $interval = $last->diff(new DateTime());

  // Is the reset date still in the future?
  if ($interval->invert === 1) {
    $seconds = $interval->s + $interval->i * 60 + $interval->h * 60 * 60;
    define('RESET_SECONDS_LEFT', $seconds);
  }
  else {
    $demo_key = trim(@file_get_contents($key_file));
    
    if (empty($demo_key)) {
      die('Missing key.');
    }
    
    // Check if a reset request is already being made by CURL.
    // Prevent additional requests from firing.
    if (!isset($_GET['reset_key']) ||  $_GET['reset_key'] != $demo_key) {
      $base_url = 'http://192.168.99.10/airwolf/';
      // Set reset time and perform request.
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $base_url . 'fixtures/all/?reset_key=' . $demo_key);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $res = curl_exec($ch);
      curl_close($ch);
      
      if ($res != 'Done') {
        print $res;
        exit;
      }
      
      file_put_contents($control_file, serialize(new DateTime()));
      header('Location: ' . $base_url);
      exit;
    }
  }
?>
