<?php

	class BotInterfaceAdmin
	{
		public static function showMenuEntry($selected)
		{
			?>
			<a href="?admin">
				<div id="menu-admin" class="menu-entry<?php if ($selected) echo " menu-selected" ?>">
					<span class="menu-entry">Admin</span>
				</div>
			</a>
		<?php
		}

		public static function show()
		{
			?>
			<script type="text/javascript" src="js/mbotAdmin.js"></script>
			<div id="mbot-admin" class="content">
				<div class="headline no-select">Admin</div>
				<div id="user-wrapper">
					<div class="table-id no-select grey">
						Users
					</div>
					<div class="element grey">
						<div class="element-input-wrapper float-container">
							<div class="float-left">
								<input id="adduser_uname" type="text" class="table-input" placeholder="Username">
								<input id="adduser_pass" type="password" class="table-input" placeholder="Password">
							</div>
							<input type="button" class="button-table-big" value="Add user" onclick="addUser()">
						</div>
					</div>
					<?php
						require_once( __DIR__ . "/../util/user.php" );
						$users = User::getAllUsers();
					?>
					<table id="users" class="grey">
						<tr>
							<th> Username</th>
							<th> Password</th>
							<th> Is admin?</th>
							<th> Can control music bot?</th>
							<th> Delete</th>
						</tr>
						<?php
							foreach ($users as $user)
							{
								?>
								<tr>
									<td><span class="table-content"><?= $user->username; ?></span></td>
									<td>
										<div class="float-container table-pass-wrapper">
											<input id="pass_<?= $user->userid; ?>" type="password"
											       class="table-input float-left" placeholder="••••••••"
											       oninput="passchange(event)">
											<input id="btsavepass_<?= $user->userid; ?>" type="button"
											       class="button-table hidden" value="Save"
											       onclick="changepasswd(event)">
										</div>
									</td>
									<td>
										<input id="canAdmin_<?= $user->userid; ?>"
										       type="checkbox" <?= !$user->perms->canAdmin ?: "checked"; ?>
										       onchange="changeAdmin(event)"/>
									</td>
									<td>
										<input id="canControl_<?= $user->userid; ?>"
										       type="checkbox" <?= !$user->perms->canControl ?: "checked"; ?>
										       onchange="changeControl(event)"/>
									</td>
									<td>
										<input id="delUser_<?= $user->userid; ?>" type="button"
										       class="button-table-danger" value="X" onclick="deleteUser(event)"/>
									</td>
								</tr>
							<?php
							}
						?>
					</table>
				</div>
			</div>
		<?php
		}
	}

?>