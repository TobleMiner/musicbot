<?php
	class AuthPage
	{
		public static function show()
		{
			?>
<!DOCTYPE html>
<html>
	<head>
		<title>Login - tSYS Music Bot</title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="css/musicbot.css">
	</head>
	<body class="login">
		<main>
			<div>
				<h1>tSYS Music Bot</h1>

				<form action="?control" method="POST">
					<input type="hidden" name="action" value="login">
					<label for="username">Username</label>
					<input type="text" name="username" id="username" placeholder="Username<?php // form_username ?>">
					<label for="password">Password</label>
					<input type="password" name="password" id="password" placeholder="Password<?php // form_password ?>">
					<button type="submit">Login<?php // form_login ?></button>
				</form>
			</div>
		</main>
	</body>
</html>
<?php
		}
	}
?>
