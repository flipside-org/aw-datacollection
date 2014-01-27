<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
  }

	public function index() {
		$this->load->view('base/html_start');
    $this->load->view('navigation');
    $this->load->view('base/html_end');
	}
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */