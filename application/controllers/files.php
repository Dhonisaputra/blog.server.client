<?php
/**
* 
*/
class Files extends CI_Controller
{
	
	function __construct()
	{
		# code...
		parent::__construct();
		$this->load->model('files_model');
	}

	public function download_file($file)
	{
		$this->load->helper('download');

		$file = $this->files_model->get_file($file)[0];
		$data = file_get_contents($file['file_path'].$file['file_name']); // Read the file's contents
		$name = $file['client_name'];
		force_download($name, $data);
	}

	public function ckeditor_upload_file()
	{
		// Required: anonymous function reference number as explained above.
		$funcNum = $_GET['CKEditorFuncNum'] ;
		// Optional: instance name (might be used to load a specific configuration file or anything else).
		$CKEditor = $_GET['CKEditor'] ;
		// Optional: might be used to provide localized messages.
		$langCode = $_GET['langCode'] ;
		// Optional: compare it with the value of `ckCsrfToken` sent in a cookie to protect your server side uploader against CSRF.
		// Available since CKEditor 4.5.6.
		$token = $_POST['ckCsrfToken'] ;

		$config['upload_path'] 		= 'locker/files/';
		$config['encrypt_name']		= TRUE;
		$config['allowed_types'] 	= '*';
		$response = $this->files_model->upload($config, $_FILES)[0];

		// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
		$url = base_url('locker/files/'.$response['file_name']);
		// Usually you will only assign something here if the file could not be uploaded.
		$message = '';

		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
	}
	public function upload_file()
	{
		$config['upload_path'] 		= 'locker/files/';
		$config['encrypt_name']		= TRUE;
		$config['allowed_types'] 	= '*';
		$response = $this->files_model->upload($config, $_FILES)[0];

		$return = array(
				'uploaded' 	=> isset($response['id_files'])? 1 : 0,
				'fileName' 	=> $response['file_name'],
				'url' 		=> base_url('locker/files/'.$response['file_name']),
				'id_files' 	=> $response['id_files']
			);

		echo json_encode($return);

	}
}