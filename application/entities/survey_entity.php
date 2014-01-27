<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Survey entity.
 * The survey entity serves as base to manage surveys.
 * This works in close proximity with Survey_model although
 * this doesn't depend on it.
 * 
 * Adding new fields to a survey:
 *   - Handle constructor data in the constructor function.
 *     Data comes in directly from a mongo query.
 *   - Define how that field should be saved in mongodb.
 *     That should be done in the save function of Survey_model.
 *   - Add new fileds to fixtures (Only during dev)
 */
class Survey_entity extends Entity {
  
  /********************************
   ********************************
   * Start of survey fields.
   * The next variables hold actual survey info that will
   * go in the database.
   * Every field should be of public access.
   */
  
  /**
   * Mongo Id.
   * @var int
   * @access public
   */
  public $_id = NULL;
  
  /**
   * Creation Date.
   * @var date
   * @access public
   */
  public $created = NULL;
  
  /**
   * Survey Id.
   * @var int
   * @access public
   */
  public $sid = NULL;
  
  /**
   * Survey title.
   * @var string
   * @access public
   */
  public $title;
  
  /**
   * Survey status.
   * @var int
   * @access public
   */
  public $status;
  
  /**
   * Survey files.
   * This will be a sub document on mongo.
   * @var array
   * @access public
   */
  public $files = array(
    // Xls file name. The path to the file storage is in the config file.
    'xls' => NULL,
    // Xml file name. The path to the file storage is in the config file.
    'xml' => NULL,
    // Data about the last conversion occurred
    'last_conversion' => array(
      // Date. Always stored.
      'date' => NULL,
      // Warning. Only stored if the conversion is successful and there
      // are some warnings.
      'warnings' => NULL
    )
  );
  
  /**
   * End of survey fields.
   *******************************/
  
  /**
   * Setting passed to the Survey entity.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   * 
   * @access private
   */
  private $settings = array();
  
  /**
   * Allowed statuses of a survey.
   * 
   * @var array
   * @access public
   * @static
   */
  static $allowed_status = array(
    1 => 'Draft',
    2 => 'Open',
    3 => 'Closed',
    99 => 'Canceled'
  );
  
  /**
   * Survey entity constructor
   * 
   * @param array
   *   Survey data to construct the survey.
   * 
   * @throws Exception
   *   If trying to set an invalid field.
   */
  function __construct($survey) {
    // Data will come from the database or it will be sanitized before.
    // We can assume its safe to initialize like this.
    foreach ($survey as $key => $value) {
      if (!property_exists($this, $key)) {
        // Trying to set a key that doesn't exist in the survey.
        throw new Exception("Invalid field for the survey: $key");
      }
      
      $this->{$key} = $value;
    }
  }
  
  /********************************
   ********************************
   * Start of methods to manage settings.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   */
  
  /**
   * Sets the location for survey files.
   * 
   * @access public
   * @param string
   *   File location
   */
  public function set_file_location($loc) {
    $this->settings['file_loc'] = $loc;
  }
  
  /**
   * Creates Survey_entity injecting dependencies.
   * Input params must be the same as in the __construct
   * 
   * @access public
   * @static
   * 
   * @param array
   *   Survey data to construct the survey.
   * 
   * @return Survey_entity
   */
  public static function build($survey_data) {
    $survey = new Survey_entity($survey_data);    
    $CI = get_instance();
    
    // Inject dependencies.
    $survey->set_file_location($CI->config->item('aw_survey_files_location'));
    
    return $survey;
  }
  
  /**
   * End of setting methods.
   *******************************/
  
  /********************************
   ********************************
   * Start of survey's public methods.
   */
   
  /**
   * Check whether the survey is new or it exists.
   * @access public
   * @return boolean
   */
  public function is_new() {
    return $this->sid == NULL;
  }
  
  /**
   * Returns the url to a survey page.
   * @access public
   * @return string
   */
  public function get_url_view() {
    if ($this->sid == NULL) {
      throw new Exception("Trying to get link for a nonexistent survey.");       
    }    
    return base_url('survey/' . $this->sid);
  }
  
  /**
   * Returns the url to edit a survey.
   * @access public
   * @return string
   */
  public function get_url_edit() {
    if ($this->sid == NULL) {
      throw new Exception("Trying to get link for a nonexistent survey.");       
    }    
    return base_url('survey/' . $this->sid . '/edit') ;
  }
  
  /**
   * Returns the url to edit a survey.
   * 
   * @access public
   * @param array $file_data
   *   Uploaded file data as returned by the upload->do_upload() library
   * 
   * @return boolean
   *   Whether the file was successfully saved.
   * 
   * @throws Exception
   *   When trying to save a file for a survey that is not in the DB yet.
   */
  public function save_xls($file_data) {
    if ($this->sid == NULL) {
      throw new Exception('Trying to save a file for a non saved survey.');
    }
    
    // Filename pattern survey_[sid]_[ext].[ext]
    $xls_filename = sprintf('survey_%d_xls.xls', $this->sid);
    
    if(!rename($file_data['full_path'], $this->settings['file_loc'] . $xls_filename)) {
      return FALSE;
    }
     
    $this->files['xls'] = $xls_filename;
    return TRUE;
  }
  
  /**
   * Converts the survey xls file to xml using pyxform
   * 
   * @access public
   * 
   * @return StdClass
   *   Conversion result as returned from the pyxform.
   * 
   * @throws Exception
   *   When trying to save a file for a survey that is not in the DB yet.
   * @throws Exception
   *   If the survey has no xls file.
   */
  public function convert_xls_to_xml() {
    if ($this->sid == NULL) {
      throw new Exception('Trying to convert xls file for a non saved survey.');
    }
    elseif ($this->files['xls'] == NULL) {
      throw new Exception('Survey xls file not set.');
    }

    // Filename pattern survey_[sid]_[ext].[ext]
    $xml_filename = sprintf('survey_%d_xml.xml', $this->sid);
    // Xml file destination.
    $destination = $this->settings['file_loc'] . $xml_filename;
    // Xls file location.
    $source = $this->settings['file_loc'] . $this->files['xls'];
    
    // Convert!
    $conversion_result = xls2xform($source, $destination);
    
    // Store conversion date and warnings.
    switch ($conversion_result->code) {
      case 101:
        $this->files['last_conversion']['warnings'] = $conversion_result->warnings;
      case 100:
        $this->files['last_conversion']['date'] = time();
        $this->files['xml'] = $xml_filename;
      break;
    }
    
    return $conversion_result;
  }

  /**
   * End of public methods.
   *******************************/
   
  /********************************
   ********************************
   * Start of private and protected methods.
   */
   
  /**
   * End of private and protected methods.
   *******************************/
}

/* End of file survey.php */
/* Location: ./application/entities/survey.php */