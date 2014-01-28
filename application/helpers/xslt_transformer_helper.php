<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TODO: Xslt_transformer. This needs a lot of work!
 *
 */
class Xslt_transformer {

  private $xml_file_path;

  private $enketo_xslt_lib_path;
  
  private $errors = array();
  
  private $conversion_result = NULL;

  function __construct($file_path) {
    if (!file_exists($file_path)) {
      throw new Exception("File not found. Full path: $file_path");
    }
    $this->xml_file_path = $file_path;    
  }
  
  public static function build($file_path) {
    $xslt_transformer = new Xslt_transformer($file_path);
    $CI = get_instance();
    
    $xslt_transformer->set_enketo_xslt_lib_location($CI->config->item('aw_enketo_xslt_lib'));
    
    return $xslt_transformer;
  }

  public function set_enketo_xslt_lib_location($location) {
    $this->enketo_xslt_lib_path = $location;
    
    return $this;
  }
  
  public function get_errors() {
    return $this->errors;
  }
  
  public function get_result() {
    return $this->conversion_result;
  }

  public function convert() {
    
    if ($this->enketo_xslt_lib_path == NULL) {
      throw new Exception('Enketo Xslt Lib path not set.');
    }
    
    // Use libxml error handler
    libxml_use_internal_errors(true);
    // Clear any previous errors.
    libxml_clear_errors();
    
    $form_xsl = new DOMDocument();
    $model_xsl = new DOMDocument();
    $xml_form = new DOMDocument();
    
    // Load the stylesheets for conversion.
    $load_form = $form_xsl->load($this->enketo_xslt_lib_path . 'openrosa2html5form_php5.xsl');
    $load_model = $model_xsl->load($this->enketo_xslt_lib_path . 'openrosa2xmlmodel.xsl');
    
    if (!$load_form || !$load_model) {
      $this->errors[] = 'Failed to load Enketo Xslt Libs.';
      return false;
    }
    
    // Load the document to convert.
    $load_xml_form = $xml_form->load($this->xml_file_path);
    if (!$load_xml_form) {
      $this->errors[] = 'Failed to load given xml file: ' . $this->xml_file_path;
      return false;
    }
    
    // get HTML Form transformation result
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($form_xsl);
    $form = simplexml_load_string($proc->transformToXML($xml_form));

    // get XML Model transformation result
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($model_xsl);
    $model = simplexml_load_string($proc->transformToXML($xml_form));

    // combine the results
    $this->conversion_result = new SimpleXMLElement('<root>' . $model->model->asXML() . $form->form->asXML() . '</root>');
    
    // output result
    //echo $result -> asXML();

    //empty errors
    libxml_clear_errors();
    //restore CI error handler
    set_error_handler('_exception_handler');
    
    return $this;
  }

}

// ------------------------------------------------------------------------

/* End of file xslt_transformer_helper.php */
/* Location: ./application/helpers/xslt_transformer_helper.php */
