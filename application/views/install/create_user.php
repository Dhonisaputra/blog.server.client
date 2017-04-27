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
		<form onSubmit="create_user(event, this)">
			
			<div class="form-group">
				<label>Username</label>
				<input type="text" name="username" type="text" required>
			</div>
			<div class="form-group">
				<label>email</label>
				<input type="email" name="email" type="text" required>
			</div>
			<div class="form-group">
				<label>password</label>
				<input type="password" name="password" type="text" required>
			</div>
			<div class="form-group">
				<button class="btn btn-primary">Submit</button>
			</div>
		</form>
	</section>

	<script type="text/javascript">
		if(!sessionStorage.res){
			window.location.href = '<?php echo base_url("install/login") ?>'
		}
		if(!sessionStorage.setting_db){
			window.location.href = '<?php echo base_url("install/setting_database") ?>'
		}

		function create_user(e, ui)
		{
			e.preventDefault();
			var data = $(ui).serializeArray();
			$.post('<?php echo base_url("users/create_new_users") ?>', data)
			.done(function(res){
				console.log(res)
				window.location.href = '<?php echo base_url("install/done") ?>'
				
			})
			.fail(function(res){
				console.log(res)
				alert('connection failed!');
			})
		}
	</script>
</body>
</html>