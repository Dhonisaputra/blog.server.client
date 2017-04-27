<?php
/**
* 
*/
class Users_model extends CI_Model
{
	
	public function __construct()
	{
		parent::__construct();
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		$this->auth = new Pengguna;
	}
	
	private function create_table_blog_to_db($db)
	{
		$newDBConfig = $this->config_db($db);
		$connection = $this->load->database($newDBConfig, true);
		$connection->trans_start();
		$connection->query("
				CREATE TABLE posts ( id_post int(11) NOT NULL AUTO_INCREMENT, id_user int(11) DEFAULT NULL, title varchar(255) DEFAULT NULL, content longtext, posted_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, post_status enum('draft','publish') DEFAULT 'draft', avatar_post text, post_tag mediumtext, post_categories text,
				  counter_post int(11) DEFAULT '0', PRIMARY KEY (id_post) ); ");

		$connection->query(" CREATE TABLE post_categories ( id_post int(11) NOT NULL, id_category int(11) NOT NULL, PRIMARY KEY (id_post,id_category) ); ");
		$connection->query("
				CREATE TABLE post_files (
				  id_post_files int(11) NOT NULL AUTO_INCREMENT,
				  id_post int(11) DEFAULT NULL,
				  id_files int(11) DEFAULT NULL,
				  uploaded_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  uploaded_by int(11) DEFAULT NULL,
				  PRIMARY KEY (id_post_files)
				); ");
		$connection->query("
				CREATE TABLE master_files (
				  id_files int(11) NOT NULL AUTO_INCREMENT,
				  file_name text,
				  file_type varchar(10) DEFAULT NULL,
				  file_path varchar(200) DEFAULT NULL,
				  raw_name text,
				  original_name text,
				  client_name text,
				  file_ext varchar(10) DEFAULT NULL,
				  file_size int(200) DEFAULT NULL,
				  PRIMARY KEY (id_files)
				);
				");
		$connection->query("

				CREATE TABLE categories (
				  id_category int(11) NOT NULL AUTO_INCREMENT,
				  name varchar(200) NOT NULL,
				  description text,
				  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  created_by int(11) NOT NULL,
				  PRIMARY KEY (id_category)
				);
			");
		$connection->trans_complete();

		// echo $connection->last_query();

	}
	public function new_users($data)
	{
		$this->db->insert('users', $data);
		$data_result = $this->db;
		$data['id_user'] = $data_result->insert_id();
		$data['user_key'] = uniqid().$data['id_user'];
		$this->update_users(
			array('user_key' => $data['user_key'] ),
			array('id_user' => $data['id_user'] )
		);
		return $data_result;
	}

	public function get_users($select='*',$where = array())
	{
		$this->db->select($select);
		$this->db->from('users');
		if(isset($where) && (is_array($where) || is_string($where)) )
		{
			$this->db->where($where);
		}
		return $this->db->get();
	}
	public function update_users($update, $where){

		$this->db->where($where);
		$this->db->update('users', $update); 
	}


	public function extract_users_key($users_key)
	{
		$auth = substr($users_key, -1);
		return array('id_user' => $auth, 'user_key' => $users_key);
	}

	public function decrypt_app_key($extracted_users_key, $app_key_encrypted)
	{

		$dec = $this->auth->decrypt($app_key_encrypted, $extracted_users_key['id_user'], $extracted_users_key['user_key'], true);
		if($dec['status_code'] !== 200)
		{
			return false;
		}
		return $dec['decrypted_text'];
	}

	public function decrypt_auth($app_key_A, $users_key, $auth_encrypted)
	{
		$dec = $this->auth->decrypt($auth_encrypted, $app_key_A, $users_key, true);
		if($dec['status_code'] !== 200)
		{
			return false;
		}
		$dec = explode('*', $dec['decrypted_text']);
		return array(
				'prefix' => $dec[0],
				'source' => $dec[1],
				'email' => $dec[2],
			);
	}

	public function set_credential($body, $source='panel')
	{
		$auth_raw 	= 'users*'.$source.'*'.$body['email'];
		$encAuth 	= $this->auth->encrypt($auth_raw, $body['key_A'], $body['user_key'], true);
		$encAppKey 	= $this->auth->encrypt($body['key_A'], $body['id_user'], $body['user_key'], true);
		$returndata = array(
			'code' => 200,
			'auth' => $encAuth,
			'app_key' => $encAppKey,
			'auth_unique' => $body['user_key'],
			'user_key' => $body['user_key'],
		);
		return $returndata;
	}

	public function get_credential($body, $source="panel")
	{
		$users_data = $this->extract_users_key($body['user_key']);
		
		$decAppKey = isset($body['app_key'])? $this->decrypt_app_key($users_data, $body['app_key']) : null;
		$decAuth = isset($body['auth'])? $this->decrypt_auth($decAppKey, $body['user_key'], $body['auth']) : null;

		$returndata['id_user'] = $users_data['id_user'];
		$returndata['user_key'] = $users_data['user_key'];
		$returndata['source'] = isset($decAuth)? $decAuth['source'] : '';
		$returndata['email'] = isset($decAuth)? $decAuth['email'] : '';
		$returndata['app_key'] = $decAppKey? $decAppKey : '';
		$returndata['is_auth'] = $decAuth ? true : false;
		$returndata['need_auth'] = $source == 'panel' ? true : false;

		return $returndata;
	}
}