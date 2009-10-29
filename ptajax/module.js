
function onIFrameLoad (iFrame, name)
{
	var content = iFrame.contentWindow.document.body.innerHTML
	document.getElementById (name).innerHTML = content;
}

function insertModule (name, src)
{
	document.writeln ("<div id='" + name + "'>Loading...</div>")
	document.writeln ("<iframe id='iFrame_" + name
			+ "' onload='onIFrameLoad(this,\"" + name
			+ "\")'></iframe>");

	loadModule (name, src);
}

function loadModule (name, src)
{
	if (src == null)
		src = "modules.php?id=" + name

	var iFrame = document.getElementById ("iFrame_" + name)
	iFrame.src = src
}
