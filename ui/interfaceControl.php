<?php
	class BotInterfaceControl
	{
		public static function showMenuEntry($selected)
		{
			?>
				<a href="?control">
					<div id="menu-control" class="menu-entry<?php if($selected) echo " menu-selected"?>">
						<span class="menu-entry">Control</span>
					</div>
				</a>
			<?php
		}

		public static function show()
		{
			?>
			<script type="text/javascript" src="js/mbotControl.js"></script>
			<div id="mbot-control" class="content">
				<div class="headline no-select">Music Bot</div>
				<div id="player-control" class="element grey float-container"> 
					<div id="player-buttons" class="float-left">
						<input type="button" class="button-tall" value="Play">
					</div>
					<div id="bubble-time-slider" class="bubble hidden"></div>
					<div id="player-time-slider" class="slider">
						<div id="player-time-slider-content" class="slider-content orange">	</div>
					</div>
					<div id="player-playtime">
						0:00:00 / 0:00:00
					</div>
				</div>
			</div>
			<?php
		}
	}
?>