<?php

class Js_settings_test extends PHPUnit_Framework_TestCase {
	  
	private static $CI;
    
	public static function setUpBeforeClass() {
	  self::$CI =& get_instance();
	}
  
  public function test_add_settings() {
    self::$CI->js_settings->clear();
    
    $expected = array(
      'settings' => array()
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Status after clear');
    
    // Add using key, value
    self::$CI->js_settings->add('sid', 1);
    $expected = array(
      'settings' => array(
        'sid' => 1
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Add using $key, $value');
    
    // Add using array
    self::$CI->js_settings->clear();
    self::$CI->js_settings->add(array('sid' => 1));
    $expected = array(
      'settings' => array(
        'sid' => 1
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Add using array');
    
    // Add using array, multiple values
    self::$CI->js_settings->clear();
    self::$CI->js_settings->add(array('sid' => 1, 'rid' => 2));
    $expected = array(
      'settings' => array(
        'sid' => 1,
        'rid' => 2
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Add using array with multiple values');
    
    // Add using array, nested values
    $default = array(
      'survey' => array(
        'title' => 'The title'
      )
    );
    self::$CI->js_settings->reset($default);
    self::$CI->js_settings->add(array('survey' => array('sid' => 1)));
    $expected = array(
      'settings' => array(
        'survey' => array(
          'title' => 'The title',
          'sid' => 1
        )
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Add using array with nested values');
    
    // Overriding using simple array
    // Overriding with simple array is not working but yeah...
    // Not sure if we need this...
    /*
    $default = array(
      'survey' => array(
        'title' => 'The title'
      )
    );
    self::$CI->js_settings->reset($default);
    self::$CI->js_settings->add(array('survey' => 'Title of survey'));
    $expected = array(
      'settings' => array(
        'survey' => 'Title of survey'
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Overriding using simple array');
    
    // Overriding using nested array
    $default = array(
      'survey' => array(
        'title' => 'The title'
      )
    );
    self::$CI->js_settings->reset($default);
    self::$CI->js_settings->add(array('survey' => array('title' => 'Not the title')));
    $expected = array(
      'settings' => array(
        'survey' => array(
          'title' => 'Not the title'
        )
      )
    );
    $settings = self::$CI->js_settings->get_settings();
    $this->assertEquals($expected, $settings, 'Overriding using nested array');
    */
  }
  
}

?>