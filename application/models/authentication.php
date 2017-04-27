<?php

/**
* 
*/
class Authentication extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
		# code...
		$this->load->model('users_model');
		$this->isAjax = $this->input->is_ajax_request();
		$this->authorize = FALSE;
	}

	public function do_authorize($post)
	{

		if((!isset($_GET['using_auth']) || $_GET['using_auth'] != 0) && isset($post['credential']['administrator']))
		{
			$this->authorize 	= $this->users_model->get_credential($post['credential']['administrator'], $post['credential']['source']);
			if($this->authorize['need_auth'] == true && !$this->authorize['is_auth'])
			{
				header('HTTP/1.0 500 error on credential');
				return false;
			}
			
		}
		return $this->authorize;
	}

	public function set_db($db)
	{
		$db['database'] = $this->authorize !== FALSE? $this->authorize['prefix'].$this->authorize['owner_id'] : $db['database'];
		$newDBConfig = $this->config_db($db);
		return $this->load->database($newDBConfig, true);
	}

	public function config_db($dbconfig)
	{
		$db['hostname'] = 'localhost';
		$db['username'] = 'root';
		$db['password'] = 'toor';
		$db['database'] = '';
		$db['dbdriver'] = 'mysqli';
		$db['dbprefix'] = '';
		$db['pconnect'] = TRUE;
		$db['db_debug'] = TRUE;
		$db['cache_on'] = FALSE;
		$db['cachedir'] = '';
		$db['char_set'] = 'utf8';
		$db['dbcollat'] = 'utf8_general_ci';
		$db['swap_pre'] = '';
		$db['autoinit'] = TRUE;
		$db['stricton'] = FALSE;
		$db = array_merge($db, $dbconfig);
		return $db;
	}

	public function must_ajax_call()
	{
		if(!$this->isAjax)
		{
		}
	}

	
}