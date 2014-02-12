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
	public function login() {	  
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
  public function logout() {
    $this->session->sess_destroy();
    redirect('login');
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
    
    if ($user && sha1($password) == $user->password) {
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
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */