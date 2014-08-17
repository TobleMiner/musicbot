<?php
	class BotInterface
	{
		static function show()
		{
			require_once(__DIR__."/../util/permission.php");
			require_once(__DIR__."/../ui/interfaceAdmin.php");
			require_once(__DIR__."/../ui/interfaceControl.php");
			$perms = new Permissions($_SESSION["userid"]);
			?>
<DOCTYPE html>
<html>
	<head>
		<title>tSYS Music Bot</title>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type="text/javascript" src="js/mbotCommon.js"></script>
	</head>
	<body>
		<div class="float-container">
			<div class="no-select" id="menue">
				<div id="menu-caption" class="menu-entry menu-dummy menu-selected">
					<span class="menu-entry">tSYS Music Bot</span>
				</div>
				<?php
					if($perms -> canControl)
					{
						BotInterfaceControl::showMenuEntry(array_key_exists("control", $_GET));
					}
					if($perms -> canAdmin)
					{
						BotInterfaceAdmin::showMenuEntry(array_key_exists("admin", $_GET));
					}
				?>
				<a href="?logout">
					<div class="menu-entry">
						<span class="menu-entry">Logout</span>
					</div>
				</a>
			</div>
				<?php
					if(array_key_exists("control", $_GET) && $perms -> canControl)
					{
						BotInterfaceControl::show();
					}
					if(array_key_exists("admin", $_GET) && $perms -> canAdmin)
					{
						BotInterfaceAdmin::show();
					}
				?>
		</div>
	</body>
</html>
		<?php
		}
	}
?>