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
  
  /**
   * Trying to convert without setting a path for the XSLT libraries.
   * @expectedException Exception
   * @expectedExceptionMessage Enketo Xslt Lib path not set.
   */
  public function test_enketo_xslt_lib_path_not_set() {
    $xslt_transformer = new Xslt_transformer('resources/valid_survey/survey_1_xml.xml');
    $xslt_transformer->convert();
  }
  
  /**
   * Wrong path for XSLT libraries.
   * The convert should return false and the error should be in $errors
   */
  public function test_enketo_xslt_lib_path_wrong() {
    $xslt_transformer = new Xslt_transformer('resources/valid_survey/survey_1_xml.xml');
    $xslt_transformer->set_enketo_xslt_lib_location('just/an/invalid/path/');
    $this->assertFalse($xslt_transformer->convert());
    
    $errors = $xslt_transformer->get_errors();
    
    $this->assertCount(1, $errors);
    $this->assertContains('Failed to load Enketo Xslt Libs.', $errors);
  }
  
  /**
   * Wrong file to convert.
   * The file exists but its not an xml.
   */
  public function test_wrong_file() {
    $file_to_load = 'resources/valid_survey/survey_1_xls.xls';    
    $xslt_transformer = new Xslt_transformer($file_to_load);
    $xslt_transformer->set_enketo_xslt_lib_location('application/third_party/enketo-xslt/');
    $this->assertFalse($xslt_transformer->convert());
    
    $errors = $xslt_transformer->get_errors();
    
    $this->assertCount(1, $errors);
    $this->assertContains('Failed to load given xml file: ' . $file_to_load, $errors);
  }
  
  public function test_xslt_transformer() {
    $xslt_transformer = new Xslt_transformer('resources/valid_survey/survey_1_xml.xml');
    $xslt_transformer->set_enketo_xslt_lib_location('application/third_party/enketo-xslt/');
    $xslt_transformer->convert();
    
    $errors = $xslt_transformer->get_errors();
    $this->assertCount(0, $errors);
    $this->assertInstanceOf('SimpleXMLElement', $xslt_transformer->get_result());
  }
}

?>