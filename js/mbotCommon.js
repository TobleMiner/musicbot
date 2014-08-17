var errorCodes = new Array();
errorCodes[0] = "Not an error: Success";
errorCodes[1] = "Internal error";
errorCodes[2] = "Unknown action";
errorCodes[3] = "Operation not permitted";
errorCodes[4] = "Missing parameter(s)";
errorCodes[5] = "Invalid user ID";
errorCodes[6] = "Not a valid SESSION";
errorCodes[7] = "Invalid login credentials";
errorCodes[8] = "Login required";

var startTime = new Date().getTime();

function log(msg)
{
	console.log("[" + (new Date().getTime() - startTime) / 1000 + "] " + msg);
}

function sendRequest(data, callback)
{
	if (window.XMLHttpRequest)
	{
		var xmlhttp = new XMLHttpRequest();
	}
	else
	{
		var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(callback)
	{
		xmlhttp.onreadystatechange = function()
		{
			callback(xmlhttp);
		};
	}
	var encoded = "";
	var cnt = 0;
	for (var key in data) 
	{
		encoded += key + "=" + encodeURIComponent(data[key]) + "&";
		cnt ++;
	}
	if(cnt > 0)
		encoded = encoded.substring(0, encoded.length - 1)
	xmlhttp.open("POST","api.php",true);
	if(cnt > 0)
	{
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send(encoded);
	}
	else
	{
		xmlhttp.send();
	}
}