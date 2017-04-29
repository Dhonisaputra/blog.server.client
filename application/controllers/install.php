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
		$this->load->library('curl');
		include(APPPATH.'config/server.php');
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		$this->auth = new Pengguna;

		$post = $this->input->post();				
		$connected = $this->authentication->set_db($post)->initialize();
		
		if($connected)
		{
			$text = '<?php'."\n";
			$text .= '$server["remote_server"] = "'.$post["remote_server"].'";'."\n";
			$text .= '$server["processing_server"] = "'.$post["processing_server"].'";'."\n";
			$text .= '$server["server_url"] = "'.base_url().'";'."\n";
			
			$text .='$server["hostname"] = "'.$post['hostname'].'";'."\n";
			$text .='$server["username"] = "'.$post['username'].'";'."\n";
			$text .='$server["password"] = "'.$post['password'].'";'."\n";
			$text .='$server["database"] = "'.$post['database'].'";'."\n";
			$text .='$server["blog_key"] = "'.$post['blog_key'].'";'."\n";

			$text = $this->auth->encrypt(json_encode($post), '@cert', '@blog', true);
			file_put_contents(BASEPATH.'certificate/server.cert', $text);

			$this->curl->simple_post('install/install_database');
		}else
		{
			header('http/1.0 500 Error on Configuration database');
		}
	}

	public function install_database()
	{
		// Temporary variable, used to store current query
		$templine = '';
		// Read in entire file
		$lines = file(base_url('locker/database/default.sql'));
		// Loop through each line
		foreach ($lines as $line)
		{
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
			    continue;

			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';')
			{
			    // Perform the query
			    $this->db->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
			    // Reset temp variable to empty
			    $templine = '';
			}
		}
		
	}

	public function done(){
		echo 'Installing done!';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */