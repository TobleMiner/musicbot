<?php
	class AuthPage
	{
		public static function show()
		{
			?>
<DOCTYPE html>
<html>
	<head>
		<title>Login - tSYS Music Bot</title>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body>
		<div class="center-wrapper">
			<div class="center" id="login">
				<div id="auth-wrapper">
					<img id="logo" src="img/logo.png">
					<form id="login-form" method="post" action="?control">
						<input class="hidden" type="text" name="action" value="login">
						<input class="txtinput" type="text" name="username" placeholder="username">
						<input class="txtinput" type="password" name="password" placeholder="password">
						<input id="login" class="button" type="submit" value="LOGIN">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>		<?
		}
	}
?>