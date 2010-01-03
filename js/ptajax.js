
function Loader (afterLoad)
{
	this.div = [];
	if (afterLoad) this.afterLoad = afterLoad;

	this.load = function (src)
	{
		this.iframe.src = src ? src : this.iframe.src;
	}

	/**
	 * id = target div's id
	 * iId = source div's id inside loader this
	 *     = null: use id
	 *     = '*': load this document's body
	 */
	this.insert = function (id, iId, afterLoad)
	{
		if (!this.iframe) this.insertIframe();

		document.writeln ("<div id='" + id + "'></div>");
		var div = document.getElementById (id);

		if (iId) div.iId = iId;
		if (afterLoad) div.afterLoad = afterLoad;

		this.div [this.div.length] = div;
	}

	this.insertIframe = function()
	{
		var id = 'PTaJaX';

		while (document.getElementById (id))
			id += Math.floor(Math.random()*10);

		document.writeln('<iframe id=' + id + ' frameborder=0 onload="this.onSrcLoad()"></iframe>');
		var iframe = document.getElementById (id);

		iframe.loader = this;
		iframe.name = id;
		iframe.style.visibility = 'hidden';
		iframe.style.height = 0;
		iframe.style.width = 0;
		iframe.style.position = 'absolute';
		iframe.style.top = 0;
		iframe.style.left = 0;
		iframe.style.zIndex = -1;

		iframe.onSrcLoad = function()
		{
			var loader = this.loader;
			var iDoc = this.contentWindow.document;

			for (var i = 0; i < iDoc.forms.length; ++i)
				iDoc.forms[i].target = this.name;

			for (var i = 0; i < loader.div.length; ++i)
			{
				var div = loader.div[i];
				var iElement;

				if (!div.iId)
					iElement = iDoc.getElementById (div.id);
				else if (div.iId != '*')
					iElement = iDoc.getElementById (div.iId);
				else
					iElement = iDoc.body;

				if (iElement)
				{
					div.innerHTML = iElement.innerHTML;

					if (div.afterLoad) eval (div.afterLoad);
				}
			}

			if (loader.afterLoad) eval (loader.afterLoad);
		}

		this.iframe = iframe;
	}
}
