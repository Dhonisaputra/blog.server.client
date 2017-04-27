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
		<form onSubmit="login_owner(event, this)">
			<div class="form-group">
				<label>Username</label>
				<input type="text" name="email" type="email" required>
			</div>
			<div class="form-group">
				<label>password</label>
				<input type="password" name="password" required>
			</div>
			<div class="form-group">
				<button class="btn btn-primary" type="submit"> Login </button>
			</div>
		</form>
	</section>

	<script type="text/javascript">
		function login_owner(e, ui)
		{
			e.preventDefault();
			var data = $(ui).serializeArray();
			console.log(data)
			$.post('<?php echo $processing_server ?>/owner/login', data)
			.done(function(res){
				var resParse = JSON.parse(res);
				if(resParse.status == 200)
				{
					sessionStorage.setItem('res', res);
					window.location.href = '<?php echo base_url("install/setting_database") ?>'
				}else
				{
					alert('error login code'+resParse.status);
					return false;
				}
			})
		}
	</script>
</body>
</html>