var regexHidden = new RegExp("hidden", "g");
var regexMultiWhitespace = new RegExp("\\s{2,}", "g");

function hide(elem)
{
	if(typeof(elem) == typeof("str"))
		elem = document.getElementById(elem);
	var cssClasses = elem.getAttribute("class");
	cssClasses += " hidden";
	cssClasses = cssClasses.replace(regexMultiWhitespace, " ");
	elem.setAttribute("class", cssClasses);	
}

function isHidden(elem)
{
	if(typeof(elem) == typeof("str"))
		elem = document.getElementById(elem);
	return elem.getAttribute("class").indexOf("hidden") != -1;
}

function unhide(elem)
{
	if(typeof(elem) == typeof("str"))
		elem = document.getElementById(elem);
	var cssClasses = elem.getAttribute("class");
	cssClasses = cssClasses.replace(regexHidden, "");
	cssClasses = cssClasses.replace(regexMultiWhitespace, " ");
	elem.setAttribute("class", cssClasses);
}

function changeAdmin(event)
{
	var elem = event.target;
	var userid = elem.id.substring(elem.id.indexOf("_") + 1);
	var request = new Array();
	request["needsadmin"] = true;
	request["action"] = "setadmin";
	request["userid"] = userid;
	request["state"] = elem.checked.toString();
	var lastStatus = !elem.checked;
	sendRequest(request, function(xmlhttp) 
	{ 
		if(xmlhttp.readyState == 4)
		{
			var res = JSON.parse(xmlhttp.response);
			res.result = parseInt(res.result);
			if(res.result != 0)
			{
				log("API Error: " + errorCodes[res.result]);
				elem.checked = lastStatus;
				if(res.result == 6)
					window.location.reload();
			}
		}
	});
}

function changeControl(event)
{
	var elem = event.target;
	var userid = elem.id.substring(elem.id.indexOf("_") + 1);
	var request = new Array();
	request["needsadmin"] = true;
	request["action"] = "setcontrol";
	request["userid"] = userid;
	request["state"] = elem.checked.toString();
	var lastStatus = !elem.checked;
	sendRequest(request, function(xmlhttp) 
	{ 
		if(xmlhttp.readyState == 4)
		{
			var res = JSON.parse(xmlhttp.response);
			res.result = parseInt(res.result);
			if(res.result != 0)
			{
				log("API Error: " + errorCodes[res.result]);
				elem.checked = lastStatus;
				if(res.result == 6)
					window.location.reload();
			}
		}
	});
}

function passchange(event)
{
	var elem = event.target;
	var userid = elem.id.substring(elem.id.indexOf("_") + 1);
	var btn = document.getElementById("btsavepass_" + userid);
	if(isHidden(btn))
	{
		unhide(btn);
		elem.style.width = elem.offsetWidth - btn.offsetWidth
	}
}

function changepasswd(event)
{
	var elem = event.target;
	var userid = elem.id.substring(elem.id.indexOf("_") + 1);
	var input = document.getElementById("pass_" + userid);
	if(!isHidden(elem))
	{
		input.style.width = input.offsetWidth + elem.offsetWidth;
		hide(elem);
	}
	var request = new Array();
	request["needsadmin"] = true;
	request["action"] = "setpassword";
	request["userid"] = userid;
	request["password"] = input.value;
	sendRequest(request, function(xmlhttp) 
	{
		if(xmlhttp.readyState == 4)
		{
			var res = JSON.parse(xmlhttp.response);
			res.result = parseInt(res.result);
			if(res.result != 0)
			{
				log("API Error: " + errorCodes[res.result]);
				var btn = document.getElementById("btsavepass_" + userid);
				if(isHidden(btn))
				{
					unhide(btn);
					elem.style.width = elem.offsetWidth - btn.offsetWidth;
				}
				elem.checked = lastStatus;
				if(res.result == 6)
					window.location.reload();
			}
		}
	});
}

function addUser()
{
	var request = new Array();
	request["needsadmin"] = true;
	request["action"] = "adduser";
	request["username"] = document.getElementById("adduser_uname").value;
	request["password"] = document.getElementById("adduser_pass").value;
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
			else
			{
				window.location.reload();				
			}
		}
	});
}

function deleteUser(event)
{
	var elem = event.target;
	var userid = elem.id.substring(elem.id.indexOf("_") + 1);
	var request = new Array();
	request["needsadmin"] = true;
	request["action"] = "deluser";
	request["userid"] = userid;
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
			else
			{
				window.location.reload();				
			}
		}
	});
}