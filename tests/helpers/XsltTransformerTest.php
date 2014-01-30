<?php

class Xslt_transformer_helper_test extends PHPUnit_Framework_TestCase {
	  
	private static $CI;
    
	public static function setUpBeforeClass() {
	  self::$CI =& get_instance();
    
    self::$CI->load->helper('xslt_transformer');
	}
	
  /**
   * Instantiation with invalid file.
   * @expectedException Exception
   * @expectedExceptionMessage File not found. Full path: foo.bar
   */
  public function test_construct_invalid_file(){
    $xslt_transformer = new Xslt_transformer('foo.bar');
  }
  
}

?>