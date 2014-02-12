<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User entity.
 * The user entity serves as base to manage users.
 * This works in close proximity with User_model although
 * this doesn't depend on it.
 * 
 * Adding new fields to a user:
 *   - Handle constructor data in the constructor function.
 *     Data comes in directly from a mongo query.
 *   - Define how that field should be saved in mongodb.
 *     That should be done in the save function of User_model.
 *   - Add new fileds to fixtures (Only during dev)
 */
class User_entity extends Entity {
  
  /********************************
   ********************************
   * Start of user fields.
   * The next variables hold actual user info that will
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
   * Update Date.
   * @var date
   * @access public
   */
  public $updated = NULL;
  
  /**
   * User Id.
   * @var int
   * @access public
   */
  public $uid = NULL;

  /**
   * User email.
   * @var string
   * @access public
   */
  public $email = NULL;
  
  /**
   * User name.
   * @var string
   * @access public
   */
  public $name = NULL;
  
  /**
   * User username.
   * @var string
   * @access public
   */
  public $username = NULL;
  
  /**
   * User password.
   * @var string
   * @access public
   */
  public $password = NULL;
  
  /**
   * User roles.
   * @var array
   * @access public
   */
  public $roles = array();
  
  /**
   * User author.
   * Uid of who created this user.
   * @var int
   * @access public
   */
  public $author = null;
  
  /**
   * User status.
   * @var int
   * @access public
   */
  public $status = null;

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
   * User entity constructor
   * 
   * @param array
   *   User data to construct the user.
   * 
   * @throws Exception
   *   If trying to set an invalid field.
   */
  function __construct($user) {
    // Data will come from the database or it will be sanitized before.
    // We can assume its safe to initialize like this.
    foreach ($user as $key => $value) {
      if (!property_exists($this, $key)) {
        // Trying to set a key that doesn't exist in the survey.
        throw new Exception("Invalid field for the user: $key");
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
  
  // Nothing for now.
  
  /**
   * Creates User_entity injecting dependencies.
   * Input params must be the same as in the __construct
   * 
   * @access public
   * @static
   * 
   * @param array
   *   User data to construct the user.
   * 
   * @return User_entity
   */
  public static function build($user_data) {
    $user = new User_entity($user_data);    
    $CI = get_instance();
    
    // Inject dependencies.
    
    return $user;
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
    return $this->uid == NULL;
  }
   
  /**
   * Encodes the password and sets it
   * @access public
   */
  public function set_password($pass) {
    if (!empty($pass)) {
      $this->password = sha1($pass);
    }
  }
   
  /**
   * Checks whether the given password matches the user's
   * @access public
   * @return boolean
   */
  public function check_password($pass) {
    return $this->password == sha1($pass);
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

/* End of file user_entity.php */
/* Location: ./application/entities/user_entity.php */