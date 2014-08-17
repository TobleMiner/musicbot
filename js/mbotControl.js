window.onload = function()
{
	player = new Player();
	tb_time = new Trackbar("player-time-slider");
	b_time.onchange 
}

function toArray(obj) 
{
	var array = [];
	for (var i = obj.length >>> 0; i--;)
		array[i] = obj[i];
	return array;
}

Player = function() { };

Player.prototype.getData = function(action, callback)
{
	var request = new Array();
	request["action"] = action;
	sendRequest(request, function(xmlhttp) 
	{ 
		if(xmlhttp.readyState == 4)
		{
			var res = JSON.parse(xmlhttp.response);
			res.result = parseInt(res.result);
			if(res.result != 0)
			{
				log("API Error: " + errorCodes[res.result]);
				if(res.result == 6)
					window.location.reload();
			}
			if(callback) callback(res);
		}
	});
};

Player.prototype.setData = function(action, request, callback)
{
	request["action"] = action;
	request["needscontrol"] = true;
	sendRequest(request, function(xmlhttp) 
	{ 
		if(xmlhttp.readyState == 4)
		{
			var res = JSON.parse(xmlhttp.response);
			res.result = parseInt(res.result);
			if(res.result != 0)
			{
				log("API Error: " + errorCodes[res.result]);
				if(res.result == 6)
					window.location.reload();
			}
			if(callback) callback(res);
		}
	});
};

Player.prototype.getStatus = function(callback)
{
	this.getData("getstatus", callback);
};

Player.prototype.getVolume = function(callback)
{
	this.getData("getvolume", callback);
};

Player.prototype.getSongTitle = function(callback)
{
	this.getData("gettitle", callback);
};

Player.prototype.isPlaying = function(callback)
{
	this.getData("isplaying", callback);
};

Player.prototype.isPaused = function(callback)
{
	this.getData("ispaused", callback);
};

Player.prototype.getVolumeLimits = function(callback)
{
	this.getData("getvolumelimits", callback);
};

Player.prototype.getAudioLength = function(callback)
{
	this.getData("getaudiolength", callback);
};

Player.prototype.getAudioPos = function(callback)
{
	this.getData("getaudiopos", callback); 
};

Player.prototype.play = function(uri, callback)
{
	var data = new Array();
	data["uri"] = uri;
	this.setData("play", data, callback);
};

Player.prototype.pause = function(callback)
{
	this.setData("pause", new Array(), callback);
};

Player.prototype.stop = function(callback)
{
	this.setData("stop", new Array(), callback);
};

Player.prototype.setVolume = function(volume, callback)
{
	var data = new Array();
	data["volume"] = volume;
	this.setData("setvolume", data, callback);
};

Player.prototype.setAudioPos = function(pos, callback)
{
	var data = new Array();
	data["pos"] = pos;
	this.setData("setaudiopos", data, callback);
};

Trackbar = function(elem)
{
	if(typeof(elem) == typeof("str"))
		this.element = document.getElementById(elem)
	else
		this.element = elem;
	var trackbar = this;
	toArray(this.element.childNodes).forEach(function(elem)
	{
		if(elem.style)
		{
			trackbar.bar = elem;
		}
	});
	this.onchange = null;
	this.stickToMouse = false;
	this.element.onmousedown = function(event) { trackbar.onTrackbarMouseDown(event); };
	this.element.onmouseup = function(event) { trackbar.onTrackbarMouseUp(event); };
	this.element.onmousemove = function(event) { trackbar.onTrackbarMouseMove(event); };
	var parent = this.element.parentElement;
	var tparent;
	while(tparent = parent.parentElement)
		parent = tparent;
	parent.onmouseup = function(event) { trackbar.onTrackbarSuperparentMouseUp(event); };

};

Trackbar.prototype.onTrackbarMouseDown = function(event)
{
	this.stickToMouse = true;
	this.setFillPerc(event.offsetX / this.element.offsetWidth);
};

Trackbar.prototype.onTrackbarMouseUp = function(event)
{
	this.stickToMouse = false;
	if(this.onchange)
		this.onchange(this.fill);
};

Trackbar.prototype.onTrackbarMouseMove = function(event) 
{
	if(this.stickToMouse)
		this.setFillPerc(event.offsetX / this.element.offsetWidth);
};

Trackbar.prototype.onTrackbarSuperparentMouseUp = function(event) 
{
	this.stickToMouse = false;
	if(this.onchange)
		this.onchange(this.fill);
};

Trackbar.prototype.setFillPerc = function(perc) 
{
	this.bar.style.width = (perc * 100).toString() + "%";
	this.fill = perc;
};

