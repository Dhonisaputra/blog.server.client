<?php

class Article extends CI_Controller
{

	function __construct()
	{
		# code...
		/*$_POST = Array
		(
		    'credential' => Array
		        (
		            'source' => 'client',
		            'administrator' => Array
		                (
		                    'auth' => '4a99f7861c3eed6f1c54f68eab9b63717b2ec8',
		                    'owner_key' => '58f3425f81af11',
		                    'app_key' => '04565a242aa75828aecf8eb197832a4d8dc17a96bc3fbe499a470a9787502c85fcb1b7bb401598d3354564ce434aa8b397fc054a078fec976b3e4ebe8acf6dd6'
		                ),

		            'public' => Array
		                (
		                    'owner_key' => '58f3425f81af11',
		                ),

		        ),
		        'where' => array('posts.id_post' => 5)

		);*/
		parent::__construct();
		$this->load->model('model_post');
		$this->load->model('owner_model');
		$this->load->model('authentication');
		$this->authentication->must_ajax_call();
		$this->isAjax = $this->input->is_ajax_request();
		$post = $this->input->post();
		$this->authentication->do_authorize($post);
		require_once(APPPATH.'libraries/profiling/Pengguna.php');
		$this->auth = new Pengguna;
	}

	public function insert_article()
	{
		$post = $this->input->post();
		$this->model_post->insert_post(array(
				'id_user' 	=> $this->authentication->authorize['user_key'],
				'title' 	=> $post['article']['title'],
				'content' 	=> $post['article']['content'],
				'post_tag' 	=> $post['article']['tag'],
				'schedule_publish' => isset($post['article']['schedule_publish']) ? $post['article']['schedule_publish'] : null,
				'post_status' => $post['article']['post_status'],
				'published_time' => $post['article']['post_status'] == 'publish' ? date('Y-m-d H:i:s') : null,
			),
			$this->db
		);
		$id_post = $this->db->insert_id();
		$hash_raw = $this->authentication->authorize['user_key'].'*'.$id_post;
		$article_hash = $this->auth->encrypt($hash_raw, 'hashing', 'articles', true);
		$setcronjob = '';
		if($post['article']['set_schedule'] == true)
		{
			$str_time = strtotime($post['article']['schedule_publish']);
			$date = Date('d', $str_time);
			$month = Date('n', $str_time);
			$hour = Date('G', $str_time);
			$minute = Date('i', $str_time);
			$token_url = urlencode(base_url('blog/publish_article').'?token='.$article_hash.'&using_auth=0');
			$setcronjob = 'https://www.setcronjob.com/api/cron.add?token=cxo0l0ub0y5xhrsrchfoehxwvfqtjgo9&minute='.$minute.'&hour='.$hour.'&day='.$date.'&month='.$month.'&timezone=Asia/Jakarta&url='.$token_url;
			$cron = file_get_contents($setcronjob);
			$cron = json_decode($cron,true);
		
		}
		$this->model_post->update_post(
			array(
				'article_hash' => $article_hash,
				'cron_id' => isset($cron['data'])? $cron['data']['id'] : null
			), 
			array(
				'id_post' => $id_post
				)
			);


		if(count($post['article']['categories']) > 0 && isset($post['article']['categories']))
		{
			$post_categories = array();
			foreach ($post['article']['categories'] as $key => $value) {
				$post_categories[] = array('id_post' => $id_post, 'id_category' => $value);
			}
			$this->db->insert_batch('post_categories', $post_categories); 

		}
		echo json_encode(
			array('insertId' => $id_post, 'setcronjob' => $setcronjob  )
		);

	}
	public function update_articles()
	{
		$post = $this->input->post();
		// print_r($post); return false;
		$this->model_post->update_post($post['update']['article'], $post['where'], $this->db);
		$this->model_post->remove_post_categories($post['where'], $this->db);
		foreach ($post['update']['categories'] as $key => $value) {
			$post_categories[] = array('id_post' => $post['where']['id_post'], 'id_category' => $value);
		}
		$this->db->insert_batch('post_categories', $post_categories); 
	}

	public function get()
	{
		
		$post = $this->input->post();
		$post['where'] = isset($post['where'])? $post['where'] : '';
		$data = $this->model_post->get_post('posts.*, GROUP_CONCAT(categories.id_category) as group_category_id,  group_concat(categories.name) as group_category_name', $post['where'])->result_array();
		$rex = '/<img[^>]+src="([^">]+)/';
		foreach ($data as $key => $value) {
			// $cat = $this->model_post->get_post_categories(array('id_post' => $value['id_post']))->result_array();
			// $data[$key]['categories'] = $cat;
			$data[$key]['tag_item'] = explode(',', $value['post_tag']);
            $data[$key]['categories_id'] = explode(',', $value['group_category_id']);
            $data[$key]['categories_name'] = explode(',', $value['group_category_name']);

            preg_match_all($rex, $value['content'], $matches);
            if(isset($matches[1]))
            {
            	$data[$key]['images_item'] = $matches[1];
            }
            // get all image
		}
		echo json_encode($data);
	}

	public function update()
	{
		$post = $this->input->post();

		$this->model_post->update_post($post['data']['update'], $post['data']['where'], $this->db);
		$this->model_post->remove_post_categories($post['data']['where'], $this->db);
		foreach ($post['data']['categories'] as $key => $value) {
			$post_categories[] = array('id_post' => $post['data']['where']['id_post'], 'id_category' => $value);
		}
		$this->db->insert_batch('post_categories', $post_categories); 
	}

	public function update_file_post()
	{
		$this->load->model('files_model');
		$post = $this->input->post();
		$config['upload_path'] 		= 'locker/files/';
		$config['encrypt_name']		= TRUE;
		$config['allowed_types'] 	= '*';
		$file_contain 			 	= array('avatar_post', 'files_post');
		$files['avatar_post'] 		= array();
		$files['files_post'] 		= array();

		foreach ($file_contain as $key => $value) {
			# code...
			$i = 1;
			foreach ($_FILES as $fk => $fv) {
				if($fk == $value.'-'.$i)
				{
					$files['avatar_post'][$fk] = $fv;
				}			
			}
		}

		$res = $this->files_model->upload($config, $files['avatar_post'])[0];
		$this->model_post->update_post(array('avatar_post' => $res['file_name']), array('id_post' => $post['id_post']));
	}

	public function update_viewer()
	{
		$post = $this->input->post();
		$this->model_post->update_post($post['update'], $post['where'], $this->db);

	}

	public function get_categories()
	{
		$data = $this->model_post->get_categories('*', array(), $this->db)->result_array();
		echo json_encode($data);
	}

	public function publish_article()
	{
		$token = $_GET['token'];
		$decrypt = $this->auth->decrypt($token, 'hashing', 'articles', true);
		print_r($decrypt);
		if($decrypt['status_code'] == 200)
		{
			$xpl = explode('*', $decrypt['decrypted_text']);
			$prefix = $xpl[0];
			$user_key = $xpl[1];
			$id_post = $xpl[2];
			$data = $this->model_post->get_post('*', array('posts.id_post' => $id_post))->row_array();

			$this->model_post->update_post(
			array(
				'post_status' => 'publish',
				'published_time' => date('Y-m-d H:i:s'),
			), 
			array(
				'id_post' => $id_post
				)
			);
			$delcronjob = 'https://www.setcronjob.com/api/cron.delete?token=cxo0l0ub0y5xhrsrchfoehxwvfqtjgo9&id='.$data['cron_id'];
			file_get_contents($delcronjob);
		}
	}

	public function add_category()
	{
		$post = $this->input->post();
		$res = $this->model_post->insert_category($post['category']);
		echo json_encode(
				array('insertId' => $res->insert_id())
			);
	}

	public function delete_articles()
	{
		$post = $this->input->post();
		if(!isset($post['where']))
		{
			header('http/1.0 500 insufficient parameters');
			return false;
		}
		$this->model_post->remove_posts($post['where']);
	}

}