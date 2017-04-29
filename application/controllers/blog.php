<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blog extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('authentication');

	}

	public function ping()
	{
		echo json_encode($_SERVER);
		// print_r($_POST);
	}
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */