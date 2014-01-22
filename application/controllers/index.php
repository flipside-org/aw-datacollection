<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
  public function __construct() {
    parent::__construct();
  }

	public function index() {
		print 'welcome to Airwolf';
	}
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */