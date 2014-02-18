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
 *   - All object's PUBLIC fields will be saved to mongodb. That's how you
 *     define which fields are saved. If you need an accessible field, set it as
 *     protected and use Getters and Setters.
 *   - Add new fileds to fixtures (Only during dev)
 * 
 * IMPORTANT: Only use public field for fields that need to be saved to mongodb
 */
class User_entity extends Entity {
  
  /********************************
   ********************************
   * Start of static fields and constants.
   */
  
  /**
   * User statuses.
   */
  const STATUS_ACTIVE  = 2;
  const STATUS_BLOCKED = 0;
  const STATUS_DELETED = 99;
  
  /**
   * User statuses labels.
   * Useful for printing.
   */
  static $statuses = array(
    self::STATUS_ACTIVE  => 'Active',
    self::STATUS_BLOCKED => 'Blocked',
    self::STATUS_DELETED => 'Deleted'
  );
  
  /********************************
   ********************************
   * Start of user fields.
   * The next variables hold actual user info that will
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
  public $author = NULL;
  
  /**
   * User status.
   * @var int
   * @access public
   */
  public $status = NULL;

  /**
   * End of survey fields.
   *******************************/
  
  protected $permissions = NULL;
  
  protected $logged_in = NULL;
  
  /**
   * Setting passed to the Survey entity.
   * Passing settings allows detachment form codeigniter easing
   * testing.
   * About dependency injection:
   * http://www.potstuck.com/2009/01/08/php-dependency-injection/
   * 
   * @access private
   */
  protected $settings = array(
    'permissions' => array()
  );
  
  
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
  
  /**
   * Builds the permissions for a user
   * @param array $perms.
   *   The permission array form the config file.
   * @return this
   */
  public function set_permissions_array($perms) {
    $this->settings['permissions'] = $perms;
    return $this;
  }
  
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
    $user->set_permissions_array($CI->config->item('permissions'));
    
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
   * Encodes the password and sets it.
   * @access public
   * @param string $pass
   * @return this
   */
  public function set_password($pass) {
    if (!empty($pass)) {
      $this->password = $this->_hash_password($pass);
    }
    return $this;
  }
   
  /**
   * Checks whether the given password matches the user's.
   * @access public
   * @param string $pass
   * @return boolean
   */
  public function check_password($pass) {
    return $this->password == $this->_hash_password($pass);
  }
  
  /**
   * Check if a user has a given permission.
   * @access public
   * @param string $perm
   *   The permission to check.
   * @return boolean
   */
  public function has_permission($perm) {
    // Only build permissions once.
    if ($this->permissions === NULL) {
      $this->_build_permissions();
    }
    return in_array($perm, $this->permissions);
  }
  
  /**
   * Returns all the user permissions.
   * @access public
   * @return array
   */
  public function all_permissions() {
    // Only build permissions once.
    if ($this->permissions === NULL) {
      $this->_build_permissions();
    }
    return $this->permissions;
  }
  
  /**
   * Sets the user as logged in or not.
   * @param Boolean $status
   *   Default to TRUE.
   * @return this.
   */
  public function set_logged($status = TRUE) {
    $this->logged_in = $status;
    return $this;
  }
  
  /**
   * Sets the user roles.
   * @param Array $roles
   * @return this.
   */
  public function set_roles($roles) {
    if (is_array($roles)) {
      $this->roles = array_unique($roles);
    }
    else {
      $this->roles = array();
    }
    return $this;
  }
  
  /**
   * Sets the user status. The value is converted to integer.
   * @return this.
   */
  public function set_status($status) {
    $this->status = (int) $status;
    return $this;
  }
  
  /**
   * Checks if the user is logged in.
   * @return boolean.
   */
  public function is_logged() {
    return $this->logged_in;
  }
  
  /**
   * Checks if the user is active.
   * @return boolean.
   */
  public function is_active() {
    return $this->status === User_entity::STATUS_ACTIVE;
  }
  
  /**
   * Returns the url to edit a user.
   * @access public
   * @return string
   */
  public function get_url_edit() {
    if ($this->uid == NULL) {
      throw new Exception("Trying to get link for a nonexistent user.");       
    }    
    return base_url('user/' . $this->uid . '/edit') ;
  }
  /**
   * End of public methods.
   *******************************/
   
  /********************************
   ********************************
   * Start of private and protected methods.
   */
   
   /**
   * Hashes the password.
   * @access private
   * @param string $pass
   * @return string
   */
   protected function _hash_password($pass) {
     return sha1($pass);
   }
   
   /**
    * Builds the user permissions. In the config files the permissions
    * are grouped by name. We loop through all the permissions to check
    * which are available to the user's roles.
    * @access private
    */
   protected function _build_permissions() {
     // ALL, anonymous and authenticated are not roles, but to ease search
     // temporarily treat them as such.
     $fake_roles = $this->roles;
     $fake_roles[] = 'ALL';
     $fake_roles[] = $this->is_logged() ? ROLE_LOGGED : ROLE_ANONYMOUS;
     
     $user_perms = array();
     foreach ($this->settings['permissions'] as $permission_name => $permission_affected_roles) {
       // The user has the permission if it is for all or
       // if the permission if for a role the user has.
       if (count(array_intersect($fake_roles, $permission_affected_roles)) ) {
         $user_perms[] = $permission_name;
       }
     }
     $this->permissions = $user_perms;
   }
   
  /**
   * End of private and protected methods.
   *******************************/
}

/* End of file user_entity.php */
/* Location: ./application/entities/user_entity.php */