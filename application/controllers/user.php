<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
    $this->load->helper('form');
    $this->load->library('form_validation');
    $this->load->model('user_model');
  }

	public function login() {
    
    $this->form_validation->set_rules('signin_username', 'Username', 'required');
    $this->form_validation->set_rules('signin_password', 'Password', 'required');
    
    if ($this->form_validation->run() == FALSE) {
  		$this->load->view('base/html_start');
      $this->load->view('navigation');
      $this->load->view('login');
      $this->load->view('base/html_end');
    }
    else {
      $username = $this->input->post('signin_username', TRUE);
      $password = $this->input->post('signin_password');
      $user = $this->user_model->get_by_username($username);
      
      if (sha1($password) == $user->password) {
        die('logged in');
      }
      else {
        die('wrong pwd');
      }
    }
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */