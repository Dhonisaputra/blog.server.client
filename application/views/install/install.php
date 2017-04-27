<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<script type="text/javascript" src="<?php echo base_url('locker/assets/jquery/dist/jquery.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo base_url('locker/assets/bootstrap/dist/js/bootstrap.min.js'); ?>"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('locker/assets/bootstrap/dist/css/bootstrap.min.css'); ?>">
</head>
<body>
	<section>
		<form onSubmit="setting_database(event, this)">
			
			<div class="form-group">
				<label>Hostname</label>
				<input type="text" name="hostname" type="text" required>
			</div>
			<div class="form-group">
				<label>username</label>
				<input type="text" name="username" type="text" required>
			</div>
			<div class="form-group">
				<label>userpassword</label>
				<input type="text" name="password" type="text" required>
			</div>
			<div class="form-group">
				<label>database name</label>
				<input type="text" name="database" type="text" required>
			</div>
			<div class="form-group">
				<label>Blog Key</label>
				<input type="text" name="blog_key" type="text" required>
			</div>
			<div class="form-group">
				<button class="btn btn-primary" type="submit"> Login </button>
			</div>
		</form>
	</section>

	<script type="text/javascript">
		if(!sessionStorage.res){
			window.location.href = '<?php echo base_url("install/login") ?>'
		}
		function setting_database(e, ui)
		{
			e.preventDefault();
			var data = $(ui).serializeArray();
			data.push({name: 'owner_id', value:JSON.parse(sessionStorage.res).owner_id})
			$.post('<?php echo base_url("install/process_save_settings_database") ?>', data)
			.done(function(res){
				console.log(res)
				sessionStorage.setting_db = true
				window.location.href = '<?php echo base_url("install/create_user") ?>'
				
			})
			.fail(function(res){
				console.log(res)
				alert('connection failed!');
			})
		}
	</script>
</body>
</html>