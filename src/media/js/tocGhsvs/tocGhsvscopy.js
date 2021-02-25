/*!
 * @preserve
 * Note: This file has been modified and renamed <since 2020> by V.V.Schlothauer <ghsvs.de> and no longer reflects the original work of its authors (PayPal). xxx

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
			attachElement: "",
			// GHSVS. Custom ID for outer div container.
			divId: "scrollToHeadlineMenu",
			// GHSVS. Custom container selector to search in.
			containerWithHeadings: "",
			// GHSVS. Custom CSS class for hiding if containerWithHeadings not exists.
			hideIfNothingFound: "",

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

		// merge custom options.
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

		init: function (appConfig)
		{
			let attachElement = null,
			key;

  		this.setUpConfig(appConfig);

			if (this.config.containerWithHeadings && this.config.attachElement)
			{
				console.log(this.config.containerWithHeadings + ' ' + this.config.attachElement);
				this.containerWithHeadings = document.querySelector(this.config.containerWithHeadings);
				attachElement = document.querySelector(this.config.attachElement);
			}

			if (
				this.containerWithHeadings === null
				|| attachElement === null
			){
				this.hideIfNothingFound();
				return;
			}

			this.getHeadings();
			if (Object.keys(this.headingElementsArr).length < 1)
			{
				this.hideIfNothingFound();
				return;
			}

			var divId = this.config.divId;
			// if the menu exists, recreate it
			if (document.getElementById(divId) !== null)
			{
				var existingMenu = document.getElementById(divId);
				existingMenu.parentNode.removeChild(existingMenu);
			}

			var div = document.createElement('div'),
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
			htmlStr = this.getdropdownHTML();
			this.dropdownHTML += htmlStr + '</ul>';

			if (htmlStr.length > 0)
			{
				div.className = this.config.divClass;
				attachElement.insertBefore(div, attachElement.firstChild);
				div.innerHTML = this.dropdownHTML;
			}
		},

		hideIfNothingFound: function()
		{
			if (
				this.config.hideIfNothingFound
				&& document.querySelector(this.config.hideIfNothingFound) !== null
			){
				this.addStyles(this.config.hideIfNothingFound + "{display:none !important;visibility: hidden !important");
			}
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
		getHeadings: function () {
			var targets = this.config.headings;
			if (typeof targets !== 'string' || targets.length === 0) return;
			// GHSVS. Absolute must for several instances of this script.
			this.headingElementsArr = [];
			// console.log(this.headingElementsArr);exit;
			// GHSVS Search in custom container.
			//var headings = document.querySelectorAll(targets),
			var headings = this.containerWithHeadings.querySelectorAll(targets);
			var	i,
				j,
				heading,
				role,
				id,
				name,
				isItVisible,
				prefix;
			// console.log(headings);
			for (i = 0, j = headings.length; i < j; i = i + 1)
			{
				// [object HTMLHeadingElement]
				heading = headings[i];
				// console.log('Heading is:');
				// console.log(heading);
				role = heading.getAttribute('role');
				if ((typeof role === 'string') && (role === 'presentation')) continue;

				// GHSVS. Changed ussage.
				isItVisible = this.isVisible(heading, this.config.forceIsItVisibleClasses);

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
				this.headingElementsArr[id] =
				{
					id: id,
					name: name,
					prefix: prefix,
					isItVisible: isItVisible
				};
				// console.log(id);
				// console.log(this.headingElementsArr[id]);
			}
			// console.log(this.headingElementsArr);exit;
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
//console.log(el.tagName + '::' + document.defaultView.getComputedStyle(el,null).getPropertyValue('visibility'));

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
		getdropdownHTML: function(){
			var key,
				val,
				htmlStr = '',
				headingClass = '',
				elementCnt = 1,
				indentChar = '',
				prefix = '',
				listEntryPrefix = '',
				isItVisible = true;

			//for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//James' workaround to get for JSON name/value pair appears to address the issue.
			for (key in this.headingElementsArr)
			{
				console.log(key);
				console.log(this.headingElementsArr[key]);
				if (this.headingElementsArr[key].name)
				{

					if (this.config.indentChar)
					{
						prefix = this.headingElementsArr[key].prefix;
						if (!prefix)
						{
							console.log('NO Prefix!');
							console.log(this.headingElementsArr[key]);
						}

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
		}
	};

	window.tocGhsvsInit = function(customConfig)
	{
		tocGhsvs.prototype.init(customConfig);
	};

}({}));
