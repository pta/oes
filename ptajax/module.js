
function onIFrameLoad (iFrame, name)
{
	var ifrmDoc = iFrame.contentWindow.document;

	for (var i = 0; i < ifrmDoc.forms.length; ++i)
		ifrmDoc.forms[i].target = 'ifrm_' + name;

	document.getElementById (name).innerHTML = ifrmDoc.body.innerHTML;
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
