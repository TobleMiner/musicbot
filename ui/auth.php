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
					<input type="text" name="username" placeholder="Username<?php // form_username ?>">
					<input type="password" name="password" placeholder="Password<?php // form_password ?>">
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
