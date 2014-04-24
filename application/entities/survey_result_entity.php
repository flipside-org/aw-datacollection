<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Survey Result Entity
 */
class Survey_result_entity extends Entity {
  /********************************
   ********************************
   * Start of Survey Result fields.
   * The next variables hold actual Survey Result info that will
   * go in the database.
   * Every field should be of public access.
   */
  
  /**
   * Mongo Id.
   * The mongo Id is immutable. It can not be set when updating documents
   * since it is not used to query for them. Mark it as protected so it 
   * isn't picked up in the model's save method.
   * @var int
   * @access public
   */
  protected $_id = NULL;
  
  /**
   * The survey result id
   * @var int
   */
  public $srid = NULL;
  
  /**
   * The call task id to which this survey result belongs.
   * @var int
   */
  public $call_task_ctid = NULL;
  
  /**
   * Survey to which this survey result is tied. 
   * @var int
   */
  public $survey_sid = NULL;
  
  /**
   * File name where the data is stored.
   * @var array
   */
  public $files = array(
    'xml' => NULL
  );
  
  /**
   * Survey result author.
   * Normally this will be the same user which submitted data and
   * is registered in the successful status of the call task.
   * @var int
   */
  public $author = NULL;
  
  /**
   * Creation Date.
   * @var date
   * @access public
   */
  public $created = NULL;
  
  /**
   * Update Date.
   * @var date
   * @access public
   */
  public $updated = NULL;
   
  /**
   * End of Survey Result fields.
   *******************************/
  
  /**
   * Setting passed to the Survey Result entity.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   * 
   * @access protected
   */
  protected $settings = array(
    'file_loc' => NULL
  );

  /**
   * Survey Result entity constructor
   * 
   * @param array
   *   Data to construct the Survey Result.
   * 
   * @throws Exception
   *   If trying to set an invalid field.
   */
  function __construct($survey_result) {    
    // Data will come from the database or it will be sanitized before.
    // We can assume its safe to initialize like this.
    foreach ($survey_result as $key => $value) {
      if (!property_exists($this, $key)) {
        // Trying to set a key that doesn't exist in the survey.
        throw new Exception("Invalid field for the survey result: $key");
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
   * Creates Survey_result_entity injecting dependencies.
   * Input params must be the same as in the __construct
   * 
   * @access public
   * @static
   * 
   * @param array
   *   Data to construct the Survey Result.
   * 
   * @return Survey_result_entity
   */
  public static function build($survey_result_data) {
    $CI = get_instance();
    
    $survey_result = new Survey_result_entity($survey_result_data);
    $survey_result->set_file_location($CI->config->item('aw_survey_results_location'));
    
    return $survey_result;
  }
  
  /**
   * End of setting methods.
   *******************************/
  
  /********************************
   ********************************
   * Start of Survey Result's public methods.
   */
  
  /**
   * Check whether the survey result is new or it exists.
   * @access public
   * @return boolean
   */
  public function is_new() {
    return $this->srid == NULL;
  }
  
  /**
   * Saves the survey result file to disk
   *
   * @access public
   * @param array $file_data
   *   The data to be written to a file.
   *
   * @return boolean
   *   Whether the file was successfully saved.
   *
   * @throws Exception
   *   When trying to save a file for a survey result that is not in the DB yet.
   */
  public function save_xml($file_data) {
    if ($this->srid == NULL) {
      throw new Exception('Trying to save a file for a non saved survey result.');
    }

    // Filename pattern survey_result_[srid]_[ctid]_[sid].xml
    $filename = sprintf('survey_result_%d_%d_%d.xml', $this->srid, $this->call_task_ctid, $this->survey_sid);

    if(file_put_contents($this->settings['file_loc'] . $filename, $file_data)) {
      $this->files['xml'] = $filename;
      return TRUE;
    }
    return FALSE;
    
  }

  /**
   * Returns the full path to the survey result's xml file.
   *
   * @return mixed
   *   If the survey result has no file false is returned
   */
  public function get_xml_full_path() {
    return $this->files['xml'] !== NULL ? $this->settings['file_loc'] . $this->files['xml'] : false;
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
/* End of file Survey_result_entity.php */
/* Location: ./application/entities/Survey_result_entity.php */