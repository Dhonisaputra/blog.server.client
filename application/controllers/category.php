<?php

class Category extends CI_Controller
{

	function __construct()
	{
		# code...
		parent::__construct();
		$this->load->model('model_category');
		$this->isAjax = $this->input->is_ajax_request();
	}

	public function insert()
	{
		$post = $this->input->post();
		$this->model_category->insert_category($post);
	}

	public function get()
	{
		$post = $this->input->post();
		$data = $this->model_category->get_category('*', $post)->result_array();
		echo json_encode($data);
	}

}