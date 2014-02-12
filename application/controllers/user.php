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
  }
  
  /**
   * Login form.
   * Route:
   * /login
   */
	public function user_login() {	  
    if (is_logged()) {
      die('The user is already logged. Redirect to the profile page.');
    }

    $this->form_validation->set_rules('signin_username', 'Username', 'trim|required|xss_clean');
    $this->form_validation->set_rules('signin_password', 'Password', 'trim|required|xss_clean|callback__check_login_data');

    if ($this->form_validation->run() == FALSE) {      
  		$this->load->view('base/html_start');
      $this->load->view('navigation');
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
    if (is_logged()) {
      $this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('users/user_profile', array('user' => get_logged_user()));
      $this->load->view('base/html_end');
    }
    else {
      redirect('login');
    }
  }
  
  /**
   * Logout.
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
      if (FALSE) {
        // Admin can edit everything.
        
      }
      elseif (get_logged_user()->uid == $user->uid) {
        // Editing own account.
        $this->_edit_own_account();
      }
      else {
        // Editing other user account.
        // Only admins can do that.
        show_error("You're not allowed to edit other user's accounts.", 403, 'Operation not allowed');
      }
    }
    else {
      redirect('login');
    }
  }
  
  private function _edit_own_account() {
    $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
    $this->form_validation->set_rules('user_password', 'Password', 'trim|required|xss_clean|callback__check_user_password');
    $this->form_validation->set_rules('user_new_password', 'New Password', 'trim');
    $this->form_validation->set_rules('user_new_password_confirm', 'New Password Confirm', 'trim|callback__check_confirm_password');
    
    $user = get_logged_user();
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('users/user_form_edit_self', array('user' => $user));
      $this->load->view('base/html_end');
    }
    else {
      $user->name = $this->input->post('user_name');
      $user->set_password($this->input->post('user_new_password'));
      
      $this->user_model->save($user);
      // TODO: Savin own profile. Handle success, error.
      redirect('user');
    }
  }
  
  public function user_recover_password() {
    $this->form_validation->set_rules('user_email', 'Email', 'trim|required|xss_clean|valid_email|callback__check_email_exists');
    
    $user = get_logged_user();
    
    if ($this->form_validation->run() == FALSE) {
      $this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('users/user_recover_password');
      $this->load->view('base/html_end');
    }
    else {
      die();
    }
  }
  
  /**
   * Checks if the login data is valid.
   * Form validation callback.
   */
  public function _check_login_data($password) {
    // Username.
    $username = $this->input->post('signin_username');
    // Get user.
    $user = $this->user_model->get_by_username($username);
    
    if ($user && $user->check_password($password)) {
      // Set session data here since we already loaded the user.
      $data = array(
        'is_logged' => TRUE,
        'user_uid' => $user->uid
      );
      $this->session->set_userdata($data);
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_check_login_data', 'Invalid username or password.');
      return FALSE;
    }
  }

  /**
   * Checks if the password matches the logged user's
   * Form validation callback.
   */
  public function _check_user_password($password) {
    if (get_logged_user()->check_password($password)) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_check_user_password', 'The current password is not correct.');
      return FALSE;
    }
  }

  /**
   * Checks if the new password and new password confirm match.
   * Form validation callback.
   */
  public function _check_confirm_password($new_password_confirm) {
    $new_password = $this->input->post('user_new_password');
    
    if ($new_password == $new_password_confirm) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_check_confirm_password', 'The New Password Confirmation does not match.');
      return FALSE;
    }
  }

  /**
   * Checks if the user with the given email exists.
   * Used for password recovery
   * Form validation callback.
   */
  public function _check_email_exists($email) {
    $user = $this->user_model->get_by_email($email);
    
    if ($user !== FALSE) {
      return TRUE;
    }
    else {
      $this->form_validation->set_message('_check_email_exists', 'The is no user with the given email.');
      return FALSE;
    }
  }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */