<?php
require_once(APPPATH.'libraries/profiling/Pengguna.php');
$auth = new Pengguna;
if(file_exists(BASEPATH.'certificate/server.cert'))
{
	$cert = file_get_contents(BASEPATH.'certificate/server.cert');
	$cert = $auth->decrypt($cert, '@cert', '@blog', true);
	$server = json_decode($cert['decrypted_text'],true);
}else
{
	$server = array();
}
