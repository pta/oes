/**
 * @original: http://www.vonloesch.de/node/18
 */

Completer.all = new Array();
Completer.key = null;

function Completer (target, sugs)
{
	Completer.all.push (this);

	this.oldins = null;
	this.suggestions = sugs;
	this.pos = -1;
	this.words = new Array();
	this.input = null;
	this.visible = false;

	this.divTF = document.getElementById (target);

	this.divShadow = document.createElement ("div");
		this.divShadow.className = "autocomplete_shadow"
	this.divList = document.createElement ("div");
		this.divList.className = "autocomplete_list"

	this.divShadow.appendChild (this.divList);
	document.body.appendChild (this.divShadow);

	this.position();

	this.divTF.completer = this;

	//window.setInterval (lookAtAll, 100);
	this.setVisible (false);

	this.divTF.onblur = function (event)
	{
		event.target.completer.setVisible (false);
	}

	this.divTF.onfocus = function (event)
	{
		event.target.completer.oldins = -1;
		event.target.completer.lookUp();
	}

	this.divTF.onkeydown = function (event)	 //needed for Opera...
	{
		if (!event && window.event)
			event = window.event;

		if (event)
			Completer.key = event.keyCode;
		else
			Completer.key = event.which;
	}

	this.divTF.onkeyup = function (event)
	{
		var textfield = event.target;
		var completer = textfield.completer;

		completer.lookUp();

		if (Completer.key == 40) //Key down
		{
			if (completer.words.length > 0 && completer.pos < completer.words.length-1)
			{
				if (completer.pos >=0)
					completer.setColor (completer.pos, "#fff", "black");
				else
					completer.input = textfield.value;

				completer.setColor (++completer.pos, "gray", "white");
			}
		}
		else if (Completer.key == 38) //Key up
		{
			if (completer.words.length > 0 && completer.pos >= 0)
			{
				if (completer.pos >=1)
				{
					completer.setColor(completer.pos, "#fff", "black");
					completer.setColor(--completer.pos, "gray", "white");
				}
				else
				{
					completer.setColor(completer.pos, "#fff", "black");
					textfield.value = completer.input;
					textfield.focus();
					completer.pos--;
				}
			}
		}
		else if (Completer.key == 27) // Esc
		{
			textfield.value = completer.input;
			completer.setVisible (false);
			completer.pos = -1;
			completer.oldins = completer.input;
		}
		else if (Completer.key == 13) // Enter
		{
			textfield.value = completer.divList.childNodes[completer.pos].firstChild.nodeValue;
			completer.setVisible (false);
			completer.pos = -1;
			completer.oldins = completer.input;
		}
		else if (Completer.key == 8) // Backspace
		{
			completer.pos = -1;
			completer.oldins=-1;
		}
	}
}

Completer.prototype.position = function()
{
	this.divShadow.style.position = 'absolute';
	this.divShadow.style.top =  (this.divTF.getBottom() + 3) + "px";
	this.divShadow.style.left = (this.divTF.getLeft() + 2) + "px";
}

Completer.prototype.setVisible = function (visible)
{
	this.visible = visible;
	this.divShadow.style.visibility = visible?"visible":"hidden";
}

function mouseHandler()
{
	for (var i = 0; i < Completer.all.length; ++i)
		Completer.all[i].mouseHandler (this);
}

function mouseClick()
{
	for (var i = 0; i < Completer.all.length; ++i)
		Completer.all[i].mouseClick (this);
}

Completer.prototype.lookUp = function()
{
	var ins = this.divTF.value;

	this.words = this.getWord(ins);

	if (this.words.length > 0)
	{
		if (this.oldins != ins)
		{
			this.clearList();
			for (var i = 0; i < this.words.length; ++i)
				this.addWord (this.words[i]);
		}
		this.setVisible (true);
		this.input = this.divTF.value;
	}
	else
	{
		this.setVisible (false);
		this.pos = -1;
	}

	this.oldins = ins;
}

Completer.prototype.addWord = function (word)
{
	var sp = document.createElement("div");
	sp.appendChild(document.createTextNode(word));
	sp.onmouseover = mouseHandler;
	sp.onmouseout = mouseHandlerOut;
	sp.onclick = mouseClick;
	this.divList.appendChild(sp);
}

Completer.prototype.clearList = function()
{
	while (this.divList.hasChildNodes())
	{
		noten = this.divList.firstChild;
		this.divList.removeChild(noten);
		delete noten;
	}
	this.pos = -1;
}

Completer.prototype.getWord = function (beginning){
	words = new Array();
	for (var i=0;i<this.suggestions.length; ++i){
		var j = -1;
		var correct = 1;
		while (correct == 1 && ++j < beginning.length){
			if (this.suggestions[i].charAt(j) != beginning.charAt(j)) correct = 0;
		}
		if (correct == 1) words[words.length] = this.suggestions[i];
	}
	return words;
}

Completer.prototype.setColor = function (_posi, _color, _forg)
{
	this.divList.childNodes[_posi].style.background = _color;
	this.divList.childNodes[_posi].style.color = _forg;
}

Completer.prototype.mouseHandler = function (element)
{
	for (var i = 0; i < this.words.length; ++i)
		this.setColor (i, "white", "black");

	element.style.background = "gray";
	element.style.color= "white";
}

var mouseHandlerOut = function()
{
	this.style.background = "white";
	this.style.color = "black";
}

Completer.prototype.mouseClick = function (element)
{
	this.divTF.value = element.firstChild.nodeValue;
	this.setVisible (false);
	this.pos = -1;
	this.oldins = element.firstChild.nodeValue;
}

/******************************************************************************/
Element.prototype.getLeft = function()
{
	var curleft = 0;

	if (this.offsetParent)
	{
		var obj = this;

		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += this.x;

	return curleft;
}

Element.prototype.getBottom = function()
{
	var curtop = 0;

	if (this.offsetParent)
	{
		var obj = this;
		curtop += obj.offsetHeight;

		while (obj.offsetParent)
		{
			curtop += obj.offsetTop;
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
	{
		curtop += this.y;
		curtop += this.height;
	}

	return curtop;
}
