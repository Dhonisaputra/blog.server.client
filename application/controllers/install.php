<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Install extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('authentication');

	}

	public function login()
	{
		include(APPPATH.'config/server.php');
		$this->load->view('install/login.php', $server);
	}
	public function setting_database()
	{
		include(APPPATH.'config/server.php');
		$this->load->view('install/install.php', $server);
	}
	public function create_user()
	{
		$this->load->view('install/create_user.php');
	}

	public function process_save_settings_database()
	{
		include(APPPATH.'config/server.php');
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		$this->auth = new Pengguna;

		$post = $this->input->post();
		$uniqid = uniqid();
		$owner_id = $post['owner_id'];
		
		$url_blog_key_auth = $server['processing_server'].'/blog/setting_db?token='.$post['blog_key'].'&id='.$owner_id.'&time='.$uniqid.'&u='.urlencode(base_url());
		$url_blog_key_auth = file_get_contents($url_blog_key_auth);
		$url_blog_key_auth = json_decode($url_blog_key_auth, true);
		
		$connected = $this->authentication->set_db($post)->initialize();
		if($connected && $url_blog_key_auth['code'] == 200)
		{
			$text = '<?php'."\n";
			$text .= '$server["processing_server"] = "'.$server["processing_server"].'";'."\n";
			$text .= '$server["server_url"] = "'.base_url().'";'."\n";
			
			$text .='$server["hostname"] = "'.$post['hostname'].'";'."\n";
			$text .='$server["username"] = "'.$post['username'].'";'."\n";
			$text .='$server["password"] = "'.$post['password'].'";'."\n";
			$text .='$server["database"] = "'.$post['database'].'";'."\n";
			$text .='$server["blog_key"] = "'.$post['blog_key'].'";'."\n";

			file_put_contents(APPPATH.'config/server.php', $text);
		}else
		{
			header('http/1.0 500 Error on Configuration database');
		}
	}

	public function done(){
		echo 'Installing done!';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */