/** @author Quentin Anciaux <quentin.anciaux-at-advalvas.be> */

/** Store a unique id for the tooltip */
var currentId = 0;

/**
 * Replace hfr smileys found in the page with their dynamic equivalent
 * @param elem - the current element
 */
function findHfrSmileys(elem, popup) {
  var childs = elem.childNodes;
  for (var i=0;i<childs.length;i++) {
    var node = childs[i];
    if (node.nodeType == 3) {
      // find horloge
      var exp = (/\[\:(tempo\/)?[a-zA-z\s0-9-]+\]/);
      var val = ""+node.nodeValue;
      var result = exp.exec(val);
      var array = new Array();
      var index = 0;
      while (result && result.length > 0) {
        var oldIndex = val.indexOf(result[0]);
        array[index++] = val.substring(0,oldIndex);
        array[index++] = result[0];
        val = val.substring(oldIndex+result[0].length);
        result = null;
        result = exp.exec(val);
      }
      array[index++] = val;
      for (var j=0;j<array.length;j++) {
        if (array[j].match(exp)) {
          var name = array[j].substring(2,array[j].length-1);
          var url = "//totoz.eu/img/"+name;
        //  if (popup == 1) {
            var espan = document.createElement("span");
            var content = document.createTextNode(array[j]);
            espan.appendChild(content);
            espan.url = url;
            espan.onmouseover = function (e) {
              createImageToolTip(this.url,this.currentId,e);
            }
            espan.onmouseout = function (e) {
              removeMe(this.currentId);
            }
            espan.currentId = ""+currentId;
            espan.className = "hfrsmiley";
            currentId++;
            node.parentNode.insertBefore(espan,node);
        /*  } else {
            var eimg = document.createElement("img");
            eimg.src = url;
            eimg.alt = array[j];
            eimg.title = array[j];
            eimg.style.verticalAlign = "top";
            eimg.style.backgroundColor = "transparent";
            eimg.className = "hfrsmiley";
            node.parentNode.insertBefore(eimg,node);
          } */
        } else {
          node.parentNode.insertBefore(document.createTextNode(array[j]),node);
        }
      }
      node.parentNode.removeChild(node);
    } else if (node.nodeType == 1 && node.className != "hfrsmiley") {
      findHfrSmileys(node);
    }
  }
}

/**
 * Remove the tooltip with the given id from the page
 * @param idToRemove the id of the tooltip
 */
function removeMe(
	idToRemove)
{
	try
	{
		var tagToRemove = document.getElementById("tooltip"+idToRemove);
		if (tagToRemove != null)
			document.getElementsByTagName("body")[0].removeChild(tagToRemove);
	}
	catch (Exception)
	{
	}
}

/**
 * Return the tooltip object with the given id
 * currentId the id of the tooltip
 */
function getTooltipObject(
	currentId)
{
	return document.getElementById("tooltip"+currentId);
}

function showTotoz(
	)
{
	alert (window.event);
	var newDiv = getTooltipObject(currentId);
	if (newDiv == null)
	{
		var image = document.createElement("img");
		image.setAttribute("style","opacity: 0.7");
		image.setAttribute("src",imgSource);
		newDiv = document.createElement("div");
		newDiv.appendChild(image);
		var x = 0;
		var y = 0;
		if (event != null)
		{
			x = event.clientX;
			y = event.clientY+10;
		}
		else
		{
			x = window.event.clientX;
			y = window.event.clientY+10;
		}
		if ((navigator.userAgent.toLowerCase().indexOf("opera") == -1)&&
		(navigator.userAgent.toLowerCase().indexOf("khtml") == -1)&&
		(navigator.userAgent.toLowerCase().indexOf("safari") == -1)&&
		(document.all))
		{
			if (document.documentElement && document.documentElement.scrollTop)
			{
				var temp_y = y + document.documentElement.scrollTop;
				newDiv.style.position = "absolute";
				newDiv.style.top = temp_y+"px";
				newDiv.style.left = x+"px";
			}
			else if (document.body.scrollTop)
			{
				var temp_y = y + document.body.scrollTop;
				newDiv.style.position = "absolute";
				newDiv.style.top = temp_y+"px";
				newDiv.style.left = x+"px";
			}
		}
		else
		{
			newDiv.setAttribute("style",
			"position: fixed;"+
			"top: "+y+"px; left: "+x+"px;");
		}
		newDiv.setAttribute("id","tooltip"+currentId);
		document.getElementsByTagName("body")[0].appendChild(newDiv);
	}
}


/**
 * create an image tooltip and insert it at mouse coordinates.
 * @param imgSource the url to the image
 * @param currentId id of the tooltip
 * @param event the browser event object
 */
function createImageToolTip(
	imgSource,
	currentId,
	event)
{
	var newDiv = getTooltipObject(currentId);
	if (newDiv == null)
	{
		var image = document.createElement("img");
		image.setAttribute("style","opacity: 0.7");
		image.setAttribute("src",imgSource);
		newDiv = document.createElement("div");
		newDiv.appendChild(image);
		var x = 0;
		var y = 0;
		if (event != null)
		{
			x = event.clientX;
			y = event.clientY+10;
		}
		else
		{
			x = window.event.clientX;
			y = window.event.clientY+10;
		}
		if ((navigator.userAgent.toLowerCase().indexOf("opera") == -1)&&
		(navigator.userAgent.toLowerCase().indexOf("khtml") == -1)&&
		(navigator.userAgent.toLowerCase().indexOf("safari") == -1)&&
		(document.all))
		{
			if (document.documentElement && document.documentElement.scrollTop)
			{
				var temp_y = y + document.documentElement.scrollTop;
				newDiv.style.position = "absolute";
				newDiv.style.top = temp_y+"px";
				newDiv.style.left = x+"px";
			}
			else if (document.body.scrollTop)
			{
				var temp_y = y + document.body.scrollTop;
				newDiv.style.position = "absolute";
				newDiv.style.top = temp_y+"px";
				newDiv.style.left = x+"px";
			}
		}
		else
		{
			newDiv.setAttribute("style",
			"position: fixed;"+
			"top: "+y+"px; left: "+x+"px;");
		}
		newDiv.setAttribute("id","tooltip"+currentId);
		document.getElementsByTagName("body")[0].appendChild(newDiv);
	}
}

/**
 * Return the index (order) of a time value
 * null if none.
 * @param timeValue - the time value to get the index.
 */
function getNorlogeIndex(timeValue) {
	var indexNorl = null;
	if (timeValue.length == 9) {
		var vtype = timeValue.substring(8,9);
		if (vtype == '¹')
			indexNorl = 1;
		else
		if (vtype == '²')
			indexNorl = 2;
		else
		if (vtype == '³')
			indexNorl = 3;
	} else
	if (timeValue.length >= 10) {
		indexNorl = parseInt(timeValue.substring(9));
	}
	return indexNorl;
}

/**
 * Highlight the norloges for the given span time value.
 * @param espan - the span time value to highlight
 */
function highLight(espan) {
	espan.className = "highlightedNorloge";
	if (espan.tolight != null) {
		var indexNorl = getNorlogeIndex(espan.timeValue);
		for (var i=0;i<espan.tolight.length;i++) {
			var elem = espan.tolight[i];
			if (elem == null) continue;
			if (elem.parentElem.className == "boardleftmsg" && 
			    espan.parentElem.className != "boardleftmsg") {
				if (indexNorl != null) {
					var velem = elem;
					while (velem.nextt != null)
						velem = velem.nextt;
					for (var j = 1;j<indexNorl;j++) {
						velem = velem.old;
						if (velem == null) break;
					}
					if (velem != elem) {
						espan.tolight[i] = null;
						continue;
					}
				}
				var boardrightmsg = elem.parentElem.nextElem;
				//boardrightmsg.style.backgroundColor = "#8B9BBA";
				boardrightmsg.firstChild.className = "highlightedNorloge";
			}
			if (elem != espan)
				{
				//elem.style.backgroundColor = "#8B9BBA";
				elem.className = "highlightedNorloge";
				}
		}
	}
}

/**
 * Unhighlight the norloges for the given span time value.
 * @param espan - the span time value to unhighlight
 */
function unHighLight(espan) {
	espan.className = "norloge";
	if (espan.tolight != null) {
		for (var i=0;i<espan.tolight.length;i++) {
			var elem = espan.tolight[i];
			if (elem == null) continue;
			if (elem.parentElem.className == "boardleftmsg") {
				var boardrightmsg = elem.parentElem.nextElem;
				if (boardrightmsg != null) {
					boardrightmsg.firstChild.className = "";
					//boardrightmsg.style.backgroundColor = "transparent";
				}
			}
			//elem.style.backgroundColor = "transparent";
			elem.className = "norloge";
		}
	}
}

/**
 * Return the next element with the specified class name or 
 * null if none found.
 */
function getNextWithClass(elem,className) {
	while (elem.nextSibling != null) {
		elem = elem.nextSibling;
		if (elem.className == className) {
			return elem;
		}
	}
	return null;
}

var xmlhttp = false;
var timeout = 5000;
var now = new Date();

if(window.XMLHttpRequest) {
	xmlhttp = new XMLHttpRequest();
} else if(window.ActiveXObject) {
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch(e) {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function init() {
  var re = new RegExp("#(\d+)$");
  if(m = re.exec(window.location)) {
    window.scrollTo(0, m[1]);
  }
}
