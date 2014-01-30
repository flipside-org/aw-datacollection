<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Xslt_transformer
 * This class is highly based on the form_model from enketo.
 */
class Xslt_transformer {
  
  /**
   * Path to the enketo xslt libraries.
   */
  private $file_path_to_jr2HTML5_XSL;
  private $file_path_to_jr2Data_XSL;
  
  /**
   * Path to the survey_xml file.
   * The file to convert
   */
  private $xml_file_path;
  
  /**
   * Errors occurred during loading and/or transform.
   */
  private $errors = array();
  
  /**
   * Class constructor.
   * Sets the survey xml file path.
   * 
   * @param $file_path
   *   The path to the file to convert.
   * 
   * @throws Exception
   *   If the file doesn't exist.
   */
  function __construct($file_path) {
    if (!file_exists($file_path)) {
      throw new Exception("File not found. Full path: $file_path");
    }
    $this->xml_file_path = $file_path;    
  }
  
  /**
   * Creates Xslt_transformer injecting dependencies.
   * Input params must be the same as in the __construct
   * 
   * @access public
   * @static
   * 
   * @param $file_path
   *   The path to the file to convert.
   * 
   * @return Xslt_transformer
   */
  public static function build($file_path) {
    $xslt_transformer = new Xslt_transformer($file_path);
    $CI = get_instance();
    
    $xslt_transformer->set_enketo_xslt_lib_location($CI->config->item('aw_enketo_xslt_lib'));
    
    return $xslt_transformer;
  }
  
  /**
   * Sets the location for enketo xslt library.
   * 
   * @access public
   * @param string
   *   File location
   * 
   * @return this
   *   To allow chaining.
   */
  public function set_enketo_xslt_lib_location($location) {
    $this->file_path_to_jr2HTML5_XSL = $location . 'openrosa2html5form_php5.xsl';
    $this->file_path_to_jr2Data_XSL = $location . 'openrosa2xmlmodel.xsl';
    return $this;
  }

  /**
   * Performs a xslt transformation on the form and model using
   * enketo xslt libraries.
   * 
   * @return mixed
   *   Returns a SimpleXMLElement with the converted form and model or
   *   FALSE if the process failed.
   */
  public function get_transform_result_sxe() {
      $result = FALSE;
      
      // Loaded needed files.
      $xsl_form = $this->_load_xml($this->file_path_to_jr2HTML5_XSL);
      $xsl_data = $this->_load_xml($this->file_path_to_jr2Data_XSL);
      $xml_survey = $this->_load_xml($this->xml_file_path);
      
      // Store any occurred errors.
      $this->errors['xsl_form_errors'] = $xsl_form['errors'];
      $this->errors['xsl_data_errors'] = $xsl_data['errors'];
      $this->errors['xml_survey_errors'] = $xml_survey['errors'];
      
      if ($xml_survey['doc'] && $xsl_form['doc'] && $xsl_data['doc']) {
        // Perform transformation to HTML5 form.
        $transformed_form = $this->_xslt_transform($xml_survey['doc'], $xsl_form['doc']);
        // Perform transformation to get instance.
        $transformed_data = $this->_xslt_transform($xml_survey['doc'], $xsl_data['doc']);
        
        // Store errors occurred during transformation.
        $this->errors['transformed_form_errors'] = $transformed_form['errors'];
        $this->errors['transformed_data_errors'] = $transformed_data['errors'];
        
        if ($transformed_form['sxe'] && $transformed_data['sxe']) {
          // Perform fixes.
          $this->_fix_meta($transformed_data['sxe']);
          
          // Get the model from transformed data.
          $model_as_str = $transformed_data['sxe']->model->asXML();
          // Remove jr: namespace (seems to cause issue with latest PHP libs)
          $model_as_str = str_replace(' jr:template=', ' template=', $model_as_str);
          
          // Get the form from transformed form.
          $form_as_str = $transformed_form['sxe']->form->asXML();
          
          // Create a sxe (SimpleXMLElemet) by merging the model and the form.
          $result = simplexml_load_string('<root>' . $model_as_str . $form_as_str . '</root>');
        }
      }   

      return $result;
  }

  /**
   * Performs a xslt transformation.
   * 
   * @param DOMDocument $xml
   *   The document to be converted.
   * @param DOMDocument $xsl
   *   The document to use as stylesheet for the conversion.
   * 
   * @return array
   *   ['sxe'] => The resulting SimpleXMLElement or FALSE if the transformation failed.
   *   ['errors'] => Occurred errors as returned by libxml_get_errors()
   */
  private function _xslt_transform($xml, $xsl) {
    $proc = new XSLTProcessor;
    if (!$proc->hasExsltSupport()) {
      throw new Exception("XSLT Processor at server has no EXSLT Support");
    } else {
      // Restore error handler to PHP to 'catch' libxml 'errors'
      restore_error_handler();
      libxml_use_internal_errors(true);
      //clear any previous errors
      libxml_clear_errors();
      
      // Import XSLT stylesheet
      $proc->importStyleSheet($xsl);
      
      // Transform
      $output = $proc->transformToXML($xml);
      
      // Get errors
      $errors = libxml_get_errors();
      // Empty errors
      libxml_clear_errors();
      // Restore CI error handler
      set_error_handler('_exception_handler');
      
      if($output) {
        return array('sxe' => simplexml_load_string($output), 'errors' => $errors);
      }
    }
    return array('sxe' => FALSE, 'errors' => $errors);
  }

  /**
   * Loads a given xml resource into a DOMDocument.
   * 
   * @param string $resource
   *   Path to the file to load.
   * 
   * @return array
   *   ['doc'] => The DOMDocument or FALSE if the loading failed.
   *   ['errors'] => Occurred errors as returned by libxml_get_errors()
   */
  private function _load_xml($resource) {
    $success = NULL;
    // Restore error handler to PHP to 'catch' libxml 'errors'.
    restore_error_handler();
    libxml_use_internal_errors(true);
    // Clear any previous errors.
    libxml_clear_errors();
    
    // Load the XML resource into a DOMDocument .
    $doc = new DOMDocument;
    $success = $doc->load($resource);
    $errors = libxml_get_errors();
    
    // Empty errors.
    libxml_clear_errors();
    // Restore CI error handler.
    set_error_handler('_exception_handler');

    if(!$success) {
        return array('doc' => FALSE, 'errors' => $errors);
    }       
    return array('doc' => $doc, 'errors' => $errors);              
  }
  
  /**
   * Fixes the meta tag in the model.
   * Adds <meta><instanceID/></meta> if it doesn't exist
   * NOTE: This comes straight from enketo
   * 
   * @param SimpleXMLElement &$data 
   */
  private function _fix_meta(&$data) {
    $meta = NULL;
    $dataroot = NULL;
    // Very awkward way to search for meta node
    foreach ($data->model->instance[0]->children() as $rootchild) {
        $dataroot = $rootchild;
        $meta = $dataroot->meta;
        break;
    }
    if (!$meta) {
        $meta = $dataroot->addChild('meta');
    }
    $instanceid = $meta->instanceID;
    if (!$instanceid) {
        $instanceid = $meta->addChild('instanceID');
    }
 }
  
  /**
   * TODO: Document Xslt_transformer::get_errors()
   * Check what to do with this!
   */
  public function get_errors() {
    return $this->errors;
  }

}

// ------------------------------------------------------------------------

/* End of file xslt_transformer_helper.php */
/* Location: ./application/helpers/xslt_transformer_helper.php */
