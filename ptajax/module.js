
function onIFrameLoad (iFrame, name)
{
	var ifrmDoc = iFrame.contentWindow.document;

	for (var i = 0; i < ifrmDoc.forms.length; ++i)
		ifrmDoc.forms[i].target = 'ifrm_' + name;

	var content = ifrmDoc.body.innerHTML;

	document.getElementById (name).innerHTML = content;
}

function insertModule (name, src)
{
	var id = 'ifrm_' + name;

	document.writeln ("<div id='" + name + "'>Loading...</div>");
	document.writeln ("<iframe frameborder=0 id='" + id
			+ "' name='" + id
			+ "' onload='onIFrameLoad(this,\"" + name
			+ "\")'></iframe>");

	loadModule (name, src);
}

function loadModule (name, src)
{
	if (src == null)
		src = "modules.php?id=" + name;

	var iFrame = document.getElementById ("ifrm_" + name);
	iFrame.src = src;
}

function insertScriptModule (name, src)
{
	var id = 'ifrm_' + name;

	document.writeln ("<div id='" + name + "'>Loading...</div>");
	document.writeln ("<iframe frameborder=0 id='" + id
			+ "' name='" + id
			+ "' onload='onIFrameScriptLoad(this,\"" + name
			+ "\")'></iframe>");

	loadModule (name, src);
}

function onIFrameScriptLoad (iFrame, name)
{
	var ifrmDoc = iFrame.contentWindow.document;

	for (var i = 0; i < ifrmDoc.forms.length; ++i)
		ifrmDoc.forms[i].target = 'ifrm_' + name;

	var content = ifrmDoc.body.innerHTML;

	var start, end = 0;
	while ((start = content.indexOf ('<div class="script">', end)) >= 0)
	{
		end = content.indexOf ('</div>', start);
		var script = content.slice (start + 20, end);
		eval (script);
	}

	document.getElementById (name).innerHTML = content;
}
