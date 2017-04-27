<?php

class Users extends CI_Controller
{

	function __construct()
	{
		# code...
		parent::__construct();
		$this->isAjax = $this->input->is_ajax_request();
		$this->load->model('users_model');
	}

	
	private function encrypt($data)
	{
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		$auth = new Pengguna;
		return $auth->create_account($data, array('password_hash' => 'password', 'exception' => 'email' ));
	}
	
	public function create_new_users()
	{
		if( !isset($_POST['username']) 	||
			!isset($_POST['email']) ||
			!isset($_POST['password'])
			){
			show_error('Error insuficient data.', '500');
			return false;
		}

		$post = $this->input->post();
		$e = $this->encrypt($post);
		$this->users_model->new_users(array(
				'username' 	=> $e['username'],
				'email' 	=> $e['email'],
				'password'	=> $e['password'],
				'key_A' 		=> $e['key_A'],
				'key_B' 		=> $e['key_B'],
				'userlevel' 	=> '0',
			)
		);
	}

	public function is_users_exist($email = '')
	{
		$post = $this->input->post();
		$users = $this->users_model->get_users('*', array('users_email' => $post['email']))->result_array();
		if(count($users) > 0)
		{
			header('http/1.0 500 user exist');
			return false;
		}
		return count($users) > 0? true : false;
	}
	public function login()
	{
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		/*$_POST = array(
				'name' => 'dhoni',
				'password' => '12345',
				'email' => 'dhoni.p.saputra@gmail.com',
			);*/
		$post = $this->input->post();
		$get = $this->input->get();
		if(!isset($post['email']) || !isset($post['password']))
		{
			echo json_encode(array('status'=>500, 'message'=> 'insuficient data!'));
			return false;
		}

		$auth = new Pengguna;
		$return = $this->users_model->get_users('*', array('email' => $post['email']))->result_array();
		if(count($return) > 0)
		{

			$return = $return[0];
			$verify = $auth->password_verify(array(
					'password' => $post['password'],
					'encrypted_password' => $return['password'],
					'key'=> array($return['key_A'], $return['key_B'])
				));
			if($verify)
			{
				if(!isset($get['dblServer']) || $get['dblServer'] == 1 )
				{

					echo json_encode(
					array(
						'status' 	=> 200, 
						'email' => $return['email'], 
						'key'	=> $return['user_key'], 
						'username' 	=> $return['username'], 
						'key_A' => $return['key_A'],
						'id_user' 	=> $return['id_user']
						)
					);
				}else
				{
					$auth = $this->users_model->set_credential($return);
					echo json_encode($auth);
				}
			}else
			{
				echo json_encode(array('status'=> 500,'message' => 'Wrong password!') );
			}
		}else
		{
			echo json_encode(array('status'=> 404,'message' => 'users not recognized!', 'post' => $_POST) );
		}
	}

	public function get_credential($data)
	{
		/*$data = array(
				'auth' => 'u8oEM8Rk6pBcwKvDkXB9jq1Rn9ryU9oBy0utFToqt4cJqJP7pJrbh/0OPf/SD9hWg/kTFQUJ2MI9/RAnOEUf3D80rrGCAQ/ZvtZF1IJfSPLlxVM=',
				'app_key' => 'EPk89DHISsGsoiya0PXCFAZBOm+f9hCivCw0FsWl10tCnNm+vZxKBlm/XsO+1pgZzXIVZ09KAv6DlGs9o/TNB8K1mSXe5/1szM4st+/mEfO8sRblvAzkizNMTipPgeU8Lxb+5W+29eknrje9YqVEZ9EdcmLgn3Gfdbq+8pTIRyI= ',
				'users_key' => '58f3425f81af11'
			);*/
		$cr = $this->users_model->get_credential($data);
		print_r($cr);
	}

}