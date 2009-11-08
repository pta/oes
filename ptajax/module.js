
function onIFrameLoad (iFrame, name)
{
	var content = iFrame.contentWindow.document.body.innerHTML;
	document.getElementById (name).innerHTML = content;
}

function insertModule (name, src)
{
	document.writeln ("<div id='" + name + "'>Loading...</div>");
	document.writeln ("<iframe frameborder=0 id='iFrame_" + name
			+ "' onload='onIFrameLoad(this,\"" + name
			+ "\")'></iframe>");

	loadModule (name, src);
}

function loadModule (name, src)
{
	if (src == null)
		src = "modules.php?id=" + name;

	var iFrame = document.getElementById ("iFrame_" + name);
	iFrame.src = src;
}

function insertScriptModule (name, src)
{
	document.writeln ("<div id='" + name + "'>Loading...</div>");
	document.writeln ("<iframe frameborder=0 id='iFrame_" + name
			+ "' onload='onIFrameScriptLoad(this,\"" + name
			+ "\")'></iframe>");

	loadModule (name, src);
}

function onIFrameScriptLoad (iFrame, name)
{
	var content = iFrame.contentWindow.document.body.innerHTML;

	var start, end = 0;
	while ((start = content.indexOf ('<div class="script">', end)) >= 0)
	{
		end = content.indexOf ('</div>', start);
		var script = content.slice (start + 20, end);
		eval (script);
	}

	document.getElementById (name).innerHTML = content;
}
