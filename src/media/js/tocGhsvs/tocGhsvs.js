/*!
 * @preserve
 * Note: This file has been modified and renamed <since 2020> by V.V.Schlothauer <ghsvs.de> and no longer reflects the original work of its authors (PayPal).

 * See original work:  https://github.com/paypal/skipto

* ========================================================================
* Copyright (c) <2019> PayPal

* All rights reserved.

* Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

* Neither the name of PayPal or any of its subsidiaries or affiliates nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
* ======================================================================== */


(function () {
	
	"use strict";
	var tocGhsvs = {};
	
	// GHSVS. if you need a cosntructor.
  //var tocGhsvs = function () {
    //this.init(options)

  //}

	tocGhsvs.prototype = {
		headingElementsArr:  [],
		// GHSVS. Not used.
		// landmarkElementsArr:  [],
		// idElementsArr:  [],
		dropdownHTML: null,
		// GHSVS. Custom this-holder for search area.
		containerWithHeadings: null,
		config: {
			// GHSVS. Not used.
			// buttonLabel:    'Skip To...',
			divTitle: '',
			divRole: 'complementary',
			divAriaLabel: '',
			ulRole: 'menu',
			ulAriaLabel:      'Scroll to headline...',
			// GHSVS. Not used.
			// landmarksLabel: 'Skip To',
			// headingsLabel:  'Page Outline',
			// GHSVS. Not used.
			// contentLabel: ' Content',
			// GHSVS. Not used.
			// main:      'main, [role="main"]',
			// landmarks: '[role="navigation"], [role="search"]',
			// sections:  'nav',
			headings:  "h1, h2, h3, h4, h5, h6",
			// GHSVS. Not used.
			// ids:       '#SkipToA1, #SkipToA2',
			// GHSVS. Not used.
			// accessKey: '0',
			// GHSVS. Not used.
			// wrap: "false",
			// GHSVS. Not used.
			// focusOnClick: "false",
			// GHSVS. Not used.
			// hashOnMenu: "true",
			enumerateElements: "false",
			// GHSVS. Not used.
			// visibility: "onFocus",
			// GHSVS. Not used.
			// customClass: "",
			attachElement: document.body,
			// GHSVS. Custom ID for outer div container.
			divId: "scrollToHeadlineMenu",
			// GHSVS. Custom container selector to search in.
			containerWithHeadings: "div.item-page",
			// GHSVS. Custom CSS class for hiding if containerWithHeadings not exists.
			hideIfNothingFound: ".HIDEIFNOTHINGFOUND",
			// GHSVS. Joomla specific. Extends hideIfNothingFound.
			moduleId: "",
			// GHSVS. Output length for <li> text.
			textLimit: 30,
			// GHSVS. Output length for id text/value.
			fragmentLimit: 30,
			// GHSVS. id for <ul>. Autamatically extended by moduleId if %s provided!!!!
			ulId: "tocGhsvsUL-%s",
			// GHSVS. class for <ul>.
			ulClass: "tocGhsvsClass toc-list-group list-unstyled",
			liClass: "toc-list-group-item",
			//indentChar: "&gt;"
			indentChar: "",
			//indentChar: ""
			divClass: "",
			// If a heading tag has one of these classes force
			// isItVisble value to true and set a link to the
			// unvisible headline.
			forceIsItVisibleClasses: []
		},

		setUpConfig: function (appConfig) {

			var localConfig = this.config,
				name,
				appConfigSettings = typeof appConfig.settings !== 'undefined'
					? appConfig.settings.TocGhsvs : {};
				
			for (name in appConfigSettings) {
				//overwrite values of our local config, based on the external config
				if (
					localConfig.hasOwnProperty(name)
					&& appConfigSettings[name] !== '[DEFAULT]'
				){
					localConfig[name] = appConfigSettings[name];
				}
			}
		},

		init: function (appConfig) {
			
  		this.setUpConfig(appConfig);
			// GHSVS.
			this.containerWithHeadings = document.querySelector(this.config.containerWithHeadings);
			if (this.containerWithHeadings === null && this.config.hideIfNothingFound)
			{
				var hideMe = this.config.hideIfNothingFound;
				this.addStyles(hideMe + "{display:none !important;");
				return;
			}

			// GHSVS. Use custom configuration id instead hard coded 'skipToMenu'.
			var divId = this.config.divId;
			// if the menu exists, recreate it
			if(document.getElementById(divId) !== null)
			{
				var existingMenu = document.getElementById(divId);
				existingMenu.parentNode.removeChild(existingMenu);
			}

			var div = document.createElement('div'),
			attachElement = (!this.config.attachElement.nodeType) ? document.querySelector(this.config.attachElement) : this.config.attachElement,
			htmlStr = '';

			div.setAttribute('id', divId);

			if (this.config.divRole)
			{
				div.setAttribute('role', this.config.divRole);
			}

			if(this.config.divTitle)
			{
				div.setAttribute('title', this.config.divTitle);
			}

			if(this.config.divAriaLabel)
			{
				div.setAttribute('aria-label', this.config.divAriaLabel);
			}
			
			// GHSVS. Implement CSS later or not!
			//Hier wird das CSS aus SkipTo.css während build reingepackt.
			// this.addStyles("@@cssContent");

			// GHSVS. Button not needed.
			this.dropdownHTML = '';

			/*this.dropdownHTML = '<a accesskey="'+ this.config.accessKey +'" tabindex="0" data-wrap="'+ this.config.wrap +'"class="dropMenu-toggle skipTo '+ this.config.visibility + ' '+ this.config.customClass +'" id="drop4" role="button" aria-haspopup="true" ';
			this.dropdownHTML += 'aria-expanded="false" data-toggle="dropMenu" data-target="menu1"';
			if (this.config.hashOnMenu === 'true') {
				this.dropdownHTML += ' href="#"';
			}
			this.dropdownHTML += '>' + this.config.buttonLabel + '<span class="caret"></span></a>';*/
			// GHSVS. ulId if provided.
			let ulId = '';
			if (this.config.ulId)
			{
				ulId = ' id="'
					+ this.config.ulId.replace('%s', this.config.moduleId)
					+ '"';
			}
			if (this.config.ulClass)
			{
				ulId += ' class="'
					+ this.config.ulClass
					+ '"';
			}
			if (this.config.ulRole)
			{
				ulId += ' role="'
					+ this.config.ulRole
					+ '"';
			}
			if (this.config.ulRole)
			{
				ulId += ' role="'
					+ this.config.ulRole
					+ '"';
			}
			if (this.config.ulAriaLabel)
			{
				ulId += ' aria-label="'
					+ this.config.ulAriaLabel
					+ '"';
			}
			this.dropdownHTML += '<ul' + ulId + '>';
			// GHSVS. Not used.
			// this.getLandMarks(this.config.main);
			// this.getLandMarks(this.config.landmarks);
			// this.getSections(this.config.sections);
			// GHSVS. Not used.
			// this.getIdElements();

			this.getHeadings();

			htmlStr = this.getdropdownHTML();
			this.dropdownHTML += htmlStr + '</ul>';

			if (htmlStr.length > 0)
			{
				div.className = this.config.divClass;
				attachElement.insertBefore(div, attachElement.firstChild);
				div.innerHTML = this.dropdownHTML;
				// GHSVS. Not needed.
				// this.addListeners();
			}
			// GHSVS. Not needed. JS removed.
			// window.skipToDropDownInit(this.config);
		},

		normalizeName: function (name) {
			if (typeof name === 'string') return name.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
			return "";
		},

		getTextContent: function (elem) {
			function getText(e, strings) {
				// If text node get the text and return
				if( e.nodeType === 3 ) { /*IE8 - Node.TEXT_NODE*/
					strings.push(e.data);
				} else {
					// if an element for through all the children elements looking for text
					if( e.nodeType === 1 ) { /*IE8 - Node.ELEMENT_NODE*/
					// check to see if IMG or AREA element and to use ALT content if defined
						var tagName = e.tagName.toLowerCase();
						if((tagName === 'img') || (tagName === 'area')) {
							if (e.alt) {
								strings.push(e.alt);
							}
						} else {
							var c = e.firstChild;
							while (c) {
								getText(c, strings);
								c = c.nextSibling;
							} // end loop
						}
					}
				}
			} // end function getStrings

			// Create return object
			var str = "",
			strings = [];
			getText(elem, strings);
			if (strings.length) str = strings.join(" ");
			// GHSVS. Use custom config var.
			//if (str.length > 30) str = str.substring(0,27) + "...";
			if (str.length > this.config.textLimit)
			{
				str = str.substring(0, (this.config.textLimit - 3)) + "...";
			}
			return str;
		},
		// GHSVS. Not used.
		/*getAccessibleName: function (elem) {
			var labelledbyIds = elem.getAttribute('aria-labelledby'),
			label = elem.getAttribute('aria-label'),
			title = elem.getAttribute('title'),
			name = "";
			
			if (labelledbyIds && labelledbyIds.length) {
				var str,
				strings = [],
				ids = labelledbyIds.split(' ');
				if (!ids.length) ids = [labelledbyIds];
				for (var i = 0, l = ids.length; i < l; i += 1) {
					var e = document.getElementById(ids[i]);
					if (e) str = this.getTextContent(e);
					if (str.length) strings.push(str);
				}
				name = strings.join(" ");
			}
			else {
				if (label && label.length) {
					name = label;
				}
				else {
					if (title && title.length) {
						name = title;
					}
				}
			}
			return name;
		},*/

		getHeadings: function () {
			var targets = this.config.headings;
			if (typeof targets !== 'string' || targets.length === 0) return;
			// GHSVS. Absolute must for several instances of this script.
			this.headingElementsArr = [];
			// GHSVS Search in custom container.
			//var headings = document.querySelectorAll(targets),
			var headings = this.containerWithHeadings.querySelectorAll(targets),
				i,
				j,
				heading,
				role,
				id,
				name,
				isItVisible,
				prefix;
			for (i = 0, j = headings.length; i < j; i = i + 1) {
				// [object HTMLHeadingElement]
				heading = headings[i];
				role = heading.getAttribute('role');
				if ((typeof role === 'string') && (role === 'presentation')) continue;

				// GHSVS. Changed ussage.
				isItVisible = this.isVisible(heading, this.config.forceIsItVisibleClasses);

				// if (this.isVisible(heading))
				{
					// GHSVS. "Avoid endless monsters if there are embedded spans and stuff".
					//id = heading.getAttribute('id') || heading.innerHTML.replace(/\s+/g, '_').toLowerCase().replace(/[&\/\\#,+()$~%.'"!:*?<>{}¹]/g, '') + '_' + i;

					if (heading.getAttribute('id'))
					{
						id = heading.getAttribute('id');
					}
					else
					{
						id = heading.innerText.replace(/\s+/g, '_').toLowerCase()
							.replace(/[&\/\\#,+()$~%.'"!:*?<>{}¹]/g, '');
					
						if (id.length > this.config.fragmentLimit)
						{
							id = id.substring(0, this.config.fragmentLimit);
						}

						// Pedantry
						id = id.replace(/_+$/g,"");
						id += '_' + i;
					}

					heading.tabIndex = "-1";
					heading.setAttribute('id', id);
					name = this.getTextContent(heading);

					// if (this.config.enumerateElements === 'false')
					{
						prefix = heading.tagName.toLowerCase();
						// name = heading.tagName.toLowerCase() + ": " + name;
					}
					
					//this.headingElementsArr[id] = heading.tagName.toLowerCase() + ": " + this.getTextContent(heading);
					//IE8 fix: Use JSON object to supply names to array values. This allows enumerating over the array without picking up prototype properties.
					this.headingElementsArr[id] = {
						id: id,
						name: name,
						prefix: prefix,
						isItVisible: isItVisible
					};
				}
			}
		},
		// GHSVS. Not used.
		isVisible: function(element, forceIsItVisibleClasses)
		{
			function isVisibleRec (el, forceIsItVisibleClasses)
			{
				
			var k, l;

				if (el.nodeType === 9) return true; // IE8 does not support Node.DOCUMENT_NODE

				//For IE8: Use standard means if available, otherwise use the IE methods
				var display = document.defaultView
					? document.defaultView.getComputedStyle(el,null).getPropertyValue('display')
					: el.currentStyle.display;
				var visibility = document.defaultView
					? document.defaultView.getComputedStyle(el,null).getPropertyValue('visibility')
					: el.currentStyle.visibility;
				//var computedStyle = window.getComputedStyle(el, null);
				//var display = computedStyle.getPropertyValue('display');
				//var visibility = computedStyle.getPropertyValue('visibility');
				var hidden = el.getAttribute('hidden');
				var ariaHidden = el.getAttribute('aria-hidden');
				var clientRect = el.getBoundingClientRect();
console.log(el.tagName + '::' + document.defaultView.getComputedStyle(el,null).getPropertyValue('visibility'));

				if (
					(display === 'none') ||
					(visibility === 'hidden') ||
					(hidden !== null) ||
					// GHSVS.
					// || (ariaHidden === 'true')
					(clientRect.height < 4) ||
					(clientRect.width < 4)
				) {
					for (k = 0, l = forceIsItVisibleClasses.length; k < l; k = k + 1)
					{
						if (el.classList.contains(forceIsItVisibleClasses[k]))
						{
							return true;
						}
					}
					return false;
				}
				
				return isVisibleRec(el.parentNode, forceIsItVisibleClasses);
			}
			
			return isVisibleRec(element, forceIsItVisibleClasses);
		},
		// GHSVS. Not used.
		/*getSections: function (targets) {
			if (typeof targets !== 'string' || targets.length === 0) return;
			// GHSVS Search in custom container.
			//var sections = document.querySelectorAll(targets),
			var sections = this.containerWithHeadings.querySelectorAll(targets),
				k,
				l,
				section,
				id1,
				role,
				val,
				name;

			for (k = 0, l = sections.length; k < l; k = k + 1) {
				section = sections[k];
				role = section.getAttribute(role);
				if ((typeof role === 'string') && (role === 'presentation')) continue;
				if (this.isVisible(section)) {
					id1 = section.getAttribute('id') || 'ui-skip-' + Math.floor((Math.random() * 100) + 1);
					section.tabIndex = "-1";
					section.setAttribute('id', id1);
					role = section.tagName.toLowerCase();

					val = (this.config.enumerateElements === 'false') ? this.normalizeName(role) + ": " : '';
					name = this.getAccessibleName(section);

					if (name && name.length) {
						val += name;
					}
					else {
						if (role === 'main') {
							val += this.config.contentLabel;
						}
					}
					this.landmarkElementsArr[id1] = val;
				}
			}
		},*/
		// GHSVS. Not used.
		/*getLandMarks: function (targets) {
			if (typeof targets !== 'string' || targets.length === 0) return;
			// GHSVS Search in custom container.
			//var landmarks = document.querySelectorAll(targets),
			var landmarks = this.containerWithHeadings.querySelectorAll(targets),
				k,
				l,
				landmark,
				id1,
				role,
				name,
				val;

			for (k = 0, l = landmarks.length; k < l; k = k + 1) {
				landmark = landmarks[k];
				role = landmark.getAttribute('role');
				if ((typeof role === 'string') && (role === 'presentation')) continue;
				if (this.isVisible(landmark)) {
					id1 = landmark.getAttribute('id') || 'ui-skip-' + Math.floor((Math.random() * 100) + 1);
					landmark.tabIndex = "-1";
					landmark.setAttribute('id', id1);
					if (!role) role = landmark.tagName.toLowerCase();
					name = this.getAccessibleName(landmark);

					if (role === 'banner') {
						role = 'header';
					} // banner landmark is the same as header element in HTML5

					if (role === 'contentinfo') {
						role = 'footer';
					} //contentinfo landmark is the same as footer element in HTML5

					if (role === 'navigation') {
						role = 'nav';
					} // navigation landmark is the same as nav element in HTML5

					val = (this.config.enumerateElements === 'false') ? this.normalizeName(role) + ": " : '';

					if (name && name.length) {
						val += name;
					}
					else {
						if (role === 'main') {
							val += this.config.contentLabel;
						}
					}
					this.landmarkElementsArr[id1] = val;
				}
			}
		},*/
		// GHSVS. Not used.
		/*getIdElements: function () {
			var i, els, el, id, val;

			if (typeof this.config.ids === 'object') {
				els = this.config.ids;
			} else if (typeof this.config.ids === 'string') {
				els = this.config.ids.split(',');
				els = els.map(function (el) {
					return {id: el.trim()};
				});
			} else {
				els = [];
			}

			for (i = 0; i < els.length; i = i + 1) {
				id = els[i].id.replace('#', '');
				el = document.getElementById(id);
				if (el === null) continue;

				val = els[i].description || el.innerHTML.replace(/<\/?[^>]+>/gi, '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, "");//for IE8
				if (val.length > 30) {
					val = val.replace(val, val.substr(0, 30) + '...');
				}

				if (this.config.enumerateElements === 'false') {
					val = "id: " + val;
				}
				this.idElementsArr[id] = val;
			}
		},*/

		getdropdownHTML: function(){
			var key,
				val,
				htmlStr = '',
				// GHSVS. Not used.
				// landmarkSep = true,
				// GHSVS. Not used.
				// headingSep = true,
				headingClass = '',
				elementCnt = 1,
				indentChar = '',
				prefix = '',
				listEntryPrefix = '',
				isItVisible = true;
			//IE8 fix: for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//Make sure that the key is not from the prototype.
			// GHSVS. Not used.
			/*for (key in this.landmarkElementsArr) {
				if (this.landmarkElementsArr.hasOwnProperty(key)){
					if (landmarkSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.landmarksLabel + '</li>';
						landmarkSep = false;
					}
					val = this.landmarkElementsArr[key];
					htmlStr += '<li role="presentation" style="list-style:none outside none"><a tabindex="-1" role="menuitem" href="#';
					htmlStr += key + '">';
					if (this.config.enumerateElements !== 'false') {
						htmlStr += elementCnt + ": ";
						elementCnt = elementCnt + 1;
					}
					htmlStr += val + '</a></li>';
				}
			}*/

			//IE8 fix: for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//Make sure that the key is not from the prototype.
			// GHSVS. Not used.
			/*for (key in this.idElementsArr) {
				if (this.idElementsArr.hasOwnProperty(key)){
					if (landmarkSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.landmarksLabel + '</li>';
						landmarkSep = false;
					}
					val = this.idElementsArr[key];
					htmlStr += '<li role="presentation" style="list-style:none outside none"><a tabindex="-1" role="menuitem" href="#';
					htmlStr += key + '">';
					if (this.config.enumerateElements !== 'false') {
						htmlStr += elementCnt + ": ";
						elementCnt = elementCnt + 1;
					}
					htmlStr += val + '</a></li>';
				}
			}*/
			//for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//James' workaround to get for JSON name/value pair appears to address the issue.
			for (key in this.headingElementsArr) {
				if (this.headingElementsArr[key].name){
					// GHSVS. Not needed.
					/*if (headingSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.headingsLabel + '</li>';
						headingSep = false;
					}*/
					//val = this.headingElementsArr[key].name;

					//headingClass = val.substring(0,2);
					// headingClass = this.headingElementsArr[key].prefix;
					
					if (this.config.indentChar)
					{
						prefix = this.headingElementsArr[key].prefix;
						indentChar = this.config.indentChar.repeat(parseInt(prefix.substring(1))) + ' ';
					}
					let liClass = ' class="' + this.config.liClass + ' po-'
						+ this.headingElementsArr[key].prefix + '"';

					htmlStr += '<li' + liClass + '>' + indentChar;

					if (this.headingElementsArr[key].isItVisible !== false)
					{
						htmlStr += '<a tabindex="-1"' + ' href="#' + key + '">';
					}

					if (this.config.enumerateElements !== 'false')
					{
						listEntryPrefix = elementCnt;
						elementCnt = elementCnt + 1;
					}
					else
					{
						listEntryPrefix = this.headingElementsArr[key].prefix;
					}

					
					htmlStr += '<span class="listEntryPrefix">'
						+ listEntryPrefix + ': </span>'
						+ this.headingElementsArr[key].name;

					if (this.headingElementsArr[key].isItVisible !== false)
					{
						htmlStr += '</a>';
					}
					
					htmlStr += '</li>'
				}
			}

			return htmlStr;
		},

		// GHSVS. Not used. See above. Call deactivated.
		addStyles: function (cssString) {
			var ss1 = document.createElement('style'),
				hh1 = document.getElementsByTagName('head')[0],
				tt1;

			ss1.setAttribute("type", "text/css");
			hh1.appendChild(ss1);

			if (ss1.styleSheet) {
				// IE
				ss1.styleSheet.cssText = cssString;
			} else {
				tt1 = document.createTextNode(cssString);
				ss1.appendChild(tt1);
			}
		},

		// GHSVS. Not used. See above. Call deactivated.
		/*addListeners: function () {
			if (this.config.focusOnClick === 'false') {
				window.addEventListener("hashchange", function () {
					var element = document.getElementById(location.hash.substring(1));
					if (element) {
						if (!/^(?:a|select|input|button|textarea)$/i.test(element.tagName)) {
							element.tabIndex = -1;
						}
						element.focus();
						element.scrollIntoView(true); //IE8 - Make sure to scroll to top
					}
				}, false);
			}
		}*/
	};

	window.tocGhsvsInit = function(customConfig)
	{
		tocGhsvs.prototype.init(customConfig);
	};

}({}));
/*@end @*/
