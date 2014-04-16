<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User controller.
 */
class User extends CI_Controller {
  
  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();
    // Load form and form validation.
    $this->load->helper('form');
    $this->load->library('form_validation');
    $this->load->helper('password_hashing');
  }
  
  /**
   * Login form.
   * Route:
   * /login
   */
	public function user_login() {
    if (is_logged()) {
      // If the user is already logged redirect to the Home Page.
      redirect();
    }

    $this->form_validation->set_rules('signin_username', 'Username', 'trim|required|xss_clean');
    $this->form_validation->set_rules('signin_password', 'Password', 'trim|required|xss_clean|callback__cb_check_login_data');
    
    $this->form_validation->set_error_delimiters('<small class="error">', '</small>');

    if ($this->form_validation->run() == FALSE) {
  		$this->load->view('base/html_start');
      $this->load->view('login');
      $this->load->view('base/html_end');
    }
    else {
      // Redirect to home page.
      redirect();
    }
	}
  
  /**
   * Logout.
   * Route:
   * /logout
   */
  public function user_logout() {
    $this->session->sess_destroy();
    redirect('login');
  }
  
  /**
   * Logout.
   * Route:
   * /user
   */
  public function user_profile($uid = null) {
    // Profiles are disabled.
    redirect();
    
    // Viewing other user's profile is not a requirement.
    // Viewing the current user profile requires the user
    // to be logged in. It is not something to control through
    // a permission.
    if (is_logged()) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'users'));
      $this->load->view('users/user_profile', array('user' => current_user()));
      $this->load->view('base/html_end');
    }
    else {
      redirect('login');
    }
  }
  
  /**
   * Edit user by id.
   * Route:
   * /user/(:num)/edit
   */
  public function user_edit_by_id($uid) {
    if (is_logged()) {
      $user = $this->user_model->get($uid);
      
      if (!$user) {
        show_404();
      }
      
      //if (user is admin) {
      if (has_permission('edit any account')) {
        // Admin can edit everything.
        $this->_edit_other_account($user);
      }
      elseif (current_user()->uid == $user->uid && has_permission('edit own account')) {
        // Editing own account.
        $this->_edit_own_account();
      }
      else {
        // Editing other user account.
        // Only admins can do that.
        show_error("You're not allowed to edit accounts.", 403, 'Operation not allowed');
      }
    }
    else {
      redirect('login');
    }
  }
  
  /**
   * Used by user_edit_by_id
   * When non admin user is attempting to edit own account.
   */
  protected function _edit_own_account() {
    $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
    $this->form_validation->set_rules('user_password', 'Password', 'trim|xss_clean|callback__cb_required_if_set[user_new_password]|callback__cb_check_user_password');
    $this->form_validation->set_rules('user_new_password', 'New Password', 'trim|min_length[8]');
    $this->form_validation->set_rules('user_new_password_confirm', 'New Password Confirm', 'trim|callback__cb_required_if_set[user_new_password]|matches[user_new_password]');
    
    $this->form_validation->set_error_delimiters('<small class="error">', '</small>');
    
    $user = current_user();
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'users'));
      $this->load->view('users/user_form', array('user' => $user, 'action' => 'edit_own'));
      $this->load->view('base/html_end');
    }
    else {
      $user->name = $this->input->post('user_name');
      $pwd = $this->input->post('user_new_password');
      if ($pwd != '') {
        $user->set_password(hash_password($pwd));
      }
      
      if ($this->user_model->save($user)) {
        Status_msg::success('Profile successfully updated.');
      }
      else {
        Status_msg::error('Error updating profile. Try again.');
      }
      redirect();
    }
  }
  
  /**
   * Used by user_edit_by_id
   * When admin user is editing an account.
   */
  protected function _edit_other_account($user) {
    $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
    $this->form_validation->set_rules('user_new_password', 'New Password', 'trim|min_length[8]');
    $this->form_validation->set_rules('user_new_password_confirm', 'New Password Confirm', 'trim|callback__cb_required_if_set[user_new_password]|matches[user_new_password]');
    $this->form_validation->set_rules('user_roles', 'Roles', 'callback__cb_check_roles');
    $this->form_validation->set_rules('user_status', 'Status', 'callback__cb_check_status');
    
    $this->form_validation->set_error_delimiters('<small class="error">', '</small>');
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'users'));
      $this->load->view('users/user_form', array('user' => $user, 'action' => 'edit_other'));
      $this->load->view('base/html_end');
    }
    else {
      $user->name = $this->input->post('user_name');
      $pwd = $this->input->post('user_new_password');
      if ($pwd != '') {
        $user->set_password(hash_password($pwd));
      }
      $user
        ->set_status($this->input->post('user_status'))
        ->set_roles($this->input->post('user_roles'));
      
      // Save
      if ($this->user_model->save($user)) {
        Status_msg::success('User account successfully updated.');
      }
      else {
        Status_msg::error('Error updating user account. Try again.');
      }
      redirect('users');
    }
  }

  /**
   * Page to add new user.
   * Route
   * /user/add
   */
  public function user_add(){
    if (!has_permission('create account')) {
      show_403();
    }
    
    $this->_add_account();
  }
  /**
   * Used by user_add
   * When adding an account.
   */
  protected function _add_account() {
    $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
    $this->form_validation->set_rules('user_username', 'Username', 'trim|required|xss_clean|alpha_dash|callback__cb_check_unique[username]');
    $this->form_validation->set_rules('user_email', 'Email', 'trim|required|xss_clean|valid_email|callback__cb_check_unique[email]');
    $this->form_validation->set_rules('user_new_password', 'Password', 'trim|required|min_length[8]');
    $this->form_validation->set_rules('user_roles', 'Roles', 'callback__cb_check_roles');
    $this->form_validation->set_rules('user_status', 'Status', 'callback__cb_check_status');
    
    $this->form_validation->set_error_delimiters('<small class="error">', '</small>');
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('components/navigation', array('active_menu' => 'users'));
      $this->load->view('users/user_form', array('user' => NULL, 'action' => 'add'));
      $this->load->view('base/html_end');
    }
    else {
      // Some values can be set in the constructor.
      $userdata = array(
        'name' => $this->input->post('user_name'),
        'username' => $this->input->post('user_username'),
        'email' => $this->input->post('user_email'),
        'author' => current_user()->uid,
      );
      
      $user = User_entity::build($userdata);
      $user
        ->set_password(hash_password($this->input->post('user_new_password')))
        ->set_status($this->input->post('user_status'))
        ->set_roles($this->input->post('user_roles'));
      
      // Save
      $this->user_model->save($user);
      
      // Notify user?
      if ($this->input->post('user_notify') == 'notify') {
        $this->load->library('email');
        $this->email->from($this->config->item('aw_admin_email'), $this->config->item('aw_admin_name'));
        $this->email->to($user->email);
        
        // Load message data from config.
        $this->config->load('email_messages');
        $message_account_created = $this->config->item('message_account_created');
        // Replace placeholders.
        $placeholders = array(
          '{{username}}' => $user->username,
          '{{name}}' => $user->name,
          '{{password}}' => $this->input->post('user_new_password')
        );
        $message_account_created['subject'] = strtr($message_account_created['subject'], $placeholders);
        $message_account_created['message'] = strtr($message_account_created['message'], $placeholders);
        
        $this->email->subject($message_account_created['subject']);
        $this->email->message($message_account_created['message']);
        
        $this->email->send();
      }
      
      if ($this->user_model->save($user)) {
        Status_msg::success('User successfully created.');
      }
      else {
        Status_msg::error('Error creating user. Try again.');
      }
      redirect('users');
      
    }
  }
  
  /**
   * Recover password.
   * A link will be sent to the email with the recover data.
   * 
   * Route:
   * /user/recover
   */
  public function user_recover_password() {
    if (is_logged()) {
      // If the user is already logged redirect to the Home Page.
      // A logged user has no business here.
      redirect();
    }
    
    $this->form_validation->set_rules('user_email', 'Email', 'trim|required|xss_clean|valid_email|callback__cb_check_email_exists');
    
    $this->form_validation->set_error_delimiters('<small class="error">', '</small>');

    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('users/user_recover_password');
      $this->load->view('base/html_end');
    }
    else {
      $this->load->model('recover_password_model');
      $email = $this->input->post('user_email');
      $user = $this->user_model->get_by_email($email);
      
      $hash = $this->recover_password_model->generate($email);
      if ($hash) {
        // Send email.
        $this->load->library('email');
        $this->email->from($this->config->item('aw_admin_email'), $this->config->item('aw_admin_name'));
        $this->email->to($email);
        
        // Load message data from config.
        $this->config->load('email_messages');
        $message_pwd_recover = $this->config->item('message_pwd_recover');
        // Replace placeholders.
        $placeholders = array(
          '{{username}}' => $user->username,
          '{{name}}' => $user->name,
          '{{reset_link}}' => base_url('user/reset_password/' . $hash)
        );
        $message_pwd_recover['subject'] = strtr($message_pwd_recover['subject'], $placeholders);
        $message_pwd_recover['message'] = strtr($message_pwd_recover['message'], $placeholders);
        
        $this->email->subject($message_pwd_recover['subject']);
        $this->email->message($message_pwd_recover['message']);
        
        $this->email->send();
        
        Status_msg::success('Please check your email for next steps.', TRUE);
        redirect('login');
      }
      else {
        show_error("An error occurred while generating hash to recover password. Try again later.");
      }
      
    }
  }

  /**
   * Form to reset password. Only accessible through url sent to email.
   * 
   * Route:
   * /user/reset_password
   */
  public function user_reset_password($hash) {
    $this->load->model('recover_password_model');
    $user_email = $this->recover_password_model->validate($hash);
    
    if ($user_email) {
      $this->form_validation->set_rules('user_new_password', 'New Password', 'trim|required');
      $this->form_validation->set_rules('user_new_password_confirm', 'New Password Confirm', 'trim|required|callback__cb_check_confirm_password');
      
      $this->form_validation->set_error_delimiters('<small class="error">', '</small>');
      
      if ($this->form_validation->run() == FALSE) {
        $this->load->view('base/html_start');
        $this->load->view('users/user_reset_password');
        $this->load->view('base/html_end');
      }
      else {
        $user = $this->user_model->get_by_email($user_email);
        
        if ($user) {
          $user->set_password(hash_password($this->input->post('user_new_password')));
          
          if ($this->user_model->save($user)) {
            $this->recover_password_model->invalidate($hash);
            
            Status_msg::success('Password successfully changed. You can now login.', TRUE);
            redirect('login');
          }
          else {
            Status_msg::error("Error saving your new password. Try again later.");
          }
          
        }
        else {
          // This could happen if the email stored with the hash doesn't return a user.
          // Maybe the user was deleted before the link was clicked?
          // During normal usage this is improbable.
          show_error("An error occurred while getting user from the hash. Try again later.");
        }
        
      }
    }
    else {
      // Hash expired.
      show_error('Sorry, this link is no longer valid.', 404);
    }
  }

  /**
   * List with all the users.
   * 
   * Route:
   * /users
   */
  public function users_list() {
    if (!has_permission('view user list')) {
      show_403();
    }
    
    $users = $this->user_model->get_all();
    
    $this->load->view('base/html_start');
    $this->load->view('components/navigation', array('active_menu' => 'users'));
    $this->load->view('users/user_list', array('users' => $users));
    $this->load->view('base/html_end');
  }
  
  /**
   * Checks if the login data is valid.
   * Form validation callback.
   */
  public function _cb_check_login_data($password) {
    // Username.
    $username = $this->input->post('signin_username');
    // Get user.
    $user = $this->user_model->get_by_username($username);

    if ($user && validate_password($password, $user->password)) {
      if ($user->is_active()){
        // Set session data here since we already loaded the user.
        $data = array(
          'is_logged' => TRUE,
          'user_uid' => $user->uid
        );
        $this->session->set_userdata($data);
        return TRUE;
      }
      else {
        $this->form_validation->set_message('_cb_check_login_data', 'This account is no longer active. Please contact an Administrator.');
        return FALSE;
      }
    }
    else {
      $this->form_validation->set_message('_cb_check_login_data', 'Invalid username or password.');
      return FALSE;
    }
  }

  /**
   * Checks if the password matches the logged user's
   * Form validation callback.
   */
  public function _cb_check_user_password($password) {
    if (validate_password($password, current_user()->password)) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_cb_check_user_password', 'The current password is not correct.');
      return FALSE;
    }
  }

  /**
   * Checks if the new password and new password confirm match.
   * Form validation callback.
   */
  public function _cb_check_confirm_password($new_password_confirm) {
    $new_password = $this->input->post('user_new_password');
    
    if ($new_password == $new_password_confirm) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_cb_check_confirm_password', 'The New Password Confirmation does not match.');
      return FALSE;
    }
  }

  /**
   * Checks if the user with the given email exists.
   * Used for password recovery
   * Form validation callback.
   */
  public function _cb_check_email_exists($email) {
    $user = $this->user_model->get_by_email($email);
    
    if ($user !== FALSE) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_cb_check_email_exists', 'There is no user with the given email.');
      return FALSE;
    }
  }

  /**
   * Checks if the given roles are valid.
   * Form validation callback.
   */
  public function _cb_check_roles($roles) {
    // A user with no roles is allowed.
    if ($roles === NULL) {
      return TRUE;
    }
    
    $config_roles = $this->config->item('roles');
    foreach ($roles as $value) {
      if (!array_key_exists($value, $config_roles)) {
        $this->form_validation->set_message('_cb_check_roles', 'Invalid role.');
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Checks if the given roles are valid.
   * Form validation callback.
   */
  public function _cb_check_status($status) {    
    if (array_key_exists($status, User_entity::$statuses)) {
      return TRUE;
    }
    else  {
      $this->form_validation->set_message('_cb_check_status', 'Invalid status.');
      return FALSE;
    }
  }

  /**
   * Checks for uniqueness. Used for email and username.
   * Form validation callback.
   */
  public function _cb_check_unique($value, $field) {
    if ($this->user_model->check_unique($field, $value)) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_cb_check_unique', 'There is already a user with the chosen %s');
      return FALSE;
    }
  }

  /**
   * Checks for uniqueness. Used for email and username.
   * Form validation callback.
   */
  public function _cb_required_if_set($value, $field) {
    if ($this->input->post($field) != '') {
      if ($value) {
        return TRUE;
      }
      else {
        $this->form_validation->set_message('_cb_required_if_set', 'The %s field is required.');
        return FALSE;
      }
    }
    else {
      return TRUE;
    }
  }

}

/* End of file login.php */
/* Location: ./application/controllers/login.php */