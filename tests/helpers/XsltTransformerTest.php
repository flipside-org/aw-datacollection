<?php

class XsltTransformerHelperTest extends PHPUnit_Framework_TestCase {
	  
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
   * Trying to get transformed sxe without setting a path for
   * the enketo xslt library.
   */
  public function test_enketo_xslt_lib_path_not_set() {
    $xslt_transformer = new Xslt_transformer('tests/test_resources/surveys/valid_survey.xml');
    $result = $xslt_transformer->get_transform_result_sxe();
    
    $this->assertFalse($result);
    
    $errors = $xslt_transformer->get_errors();    
    $this->assertCount(1, $errors['xsl_form_errors']);
    $this->assertCount(1, $errors['xsl_data_errors']);
  }
  
  /**
   * Converting a valid file.
   */
  public function test_xslt_transformer_valid() {
    $xslt_transformer = new Xslt_transformer('tests/test_resources/surveys/super_simple_survey.xml');
    $xslt_transformer->set_enketo_xslt_lib_location('./application/third_party/enketo-xslt/');
    $result = $xslt_transformer->get_transform_result_sxe();
    
    $errors = $xslt_transformer->get_errors();    
    // Every error group should be empty.
    foreach ($errors as $error_group) {
      $this->assertEmpty($error_group);
    }
    
    $this->assertInstanceOf('SimpleXMLElement', $result);
  }
}

?>