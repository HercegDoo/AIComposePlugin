/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/settings.js":
/*!********************************!*\
  !*** ./assets/src/settings.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ \"./assets/src/utils.js\");\n/* harmony import */ var _settings_aiResponses_mobileDisplayInstructionsHandler__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./settings/aiResponses/mobileDisplayInstructionsHandler */ \"./assets/src/settings/aiResponses/mobileDisplayInstructionsHandler.js\");\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\n\n\nvar previousInstruction = null;\ndocument.addEventListener('DOMContentLoaded', function () {\n  rcmail.enable_command('addinstructiontemplate', true);\n  var table = document.querySelector('#responses-table tbody');\n  table.addEventListener('click', function (event) {\n    if (event.target.tagName === 'TD') {\n      unselectPreviousInstruction();\n      selectInstruction(event.target.parentElement);\n      rcmail.addinstructiontemplate(event.target.parentElement.id);\n    }\n  });\n  var createButton = document.getElementById('create-button');\n  createButton.addEventListener('click', function () {\n    unselectPreviousInstruction();\n  });\n  var deleteButton = document.getElementById('delete-button');\n  deleteButton.addEventListener('click', function () {\n    var id;\n    var tdElements = document.querySelectorAll('#responses-table tbody tr td');\n    tdElements.forEach(function (tdElement) {\n      if (tdElement.parentElement.classList.contains('selected')) {\n        id = tdElement.parentElement.id;\n      }\n    });\n    displayPopup(id);\n  });\n});\nrcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);\nrcmail.register_command('addinstructiontemplate', rcube_webmail.prototype.addinstructiontemplate);\nrcmail.register_command('deleteinstruction', rcube_webmail.prototype.deleteinstruction);\nrcube_webmail.prototype.updateinstructionlist = function (id, title) {\n  var found = false;\n  var tdElements = document.querySelectorAll('#responses-table tbody tr td');\n  tdElements.forEach(function (tdElement) {\n    if (tdElement.parentElement.id === id) {\n      tdElement.textContent = title;\n      found = true;\n    }\n  });\n  if (!found) {\n    var tbody = document.querySelector('#responses-table tbody');\n    var trow = document.createElement('tr');\n    trow.id = id;\n    var td = document.createElement('td');\n    td.textContent = title;\n    td.className = \"name\";\n    trow.append(td);\n    selectInstruction(trow);\n    tbody.append(trow);\n  }\n};\nrcube_webmail.prototype.addinstructiontemplate = function () {\n  var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;\n  var win;\n  if (id) {\n    rcmail.enable_command('deleteinstruction', true);\n  } else {\n    rcmail.enable_command('deleteinstruction', false);\n  }\n  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {\n    rcmail.location_href({\n      _action: \"plugin.AIComposePlugin_AddInstruction\",\n      _id: id,\n      _framed: 1\n    }, win, true);\n    (0,_settings_aiResponses_mobileDisplayInstructionsHandler__WEBPACK_IMPORTED_MODULE_1__.mobileDisplayInstructionsHandle)(id);\n  }\n};\nrcube_webmail.prototype.deleteinstruction = function (id) {\n  var win;\n  var tbody = document.querySelector('#responses-table tbody');\n  var instructionToRemove = document.getElementById(id);\n  if (tbody.contains(instructionToRemove)) {\n    tbody.removeChild(instructionToRemove);\n  }\n};\nfunction unselectPreviousInstruction() {\n  if (previousInstruction) {\n    previousInstruction.classList.remove(\"selected\");\n    previousInstruction.classList.remove(\"focused\");\n  }\n}\nfunction selectInstruction(instruction) {\n  instruction.classList.add(\"selected\");\n  instruction.classList.add(\"focused\");\n  previousInstruction = instruction;\n}\nfunction displayPopup(id) {\n  var content = (0,_utils__WEBPACK_IMPORTED_MODULE_0__.translation)('ai_predefined_popup_body');\n  var title = (0,_utils__WEBPACK_IMPORTED_MODULE_0__.translation)('ai_predefined_popup_title');\n  var buttons = _defineProperty(_defineProperty({}, (0,_utils__WEBPACK_IMPORTED_MODULE_0__.translation)('ai_predefined_delete'), function () {\n    popup.remove();\n    deleteInstructionPostRequest(id);\n  }), (0,_utils__WEBPACK_IMPORTED_MODULE_0__.translation)('ai_predefined_cancel'), function () {\n    popup.remove();\n  });\n  var options = {\n    width: 500,\n    height: 45,\n    modal: true,\n    resizable: true,\n    button_classes: ['mainaction delete btn btn-primary btn-danger', 'cancel btn btn-secondary']\n  };\n  var popup = rcmail.show_popup_dialog(content, title, buttons, options);\n}\nfunction deleteInstructionPostRequest(id) {\n  rcmail.http_post(\"plugin.AIComposePlugin_DeleteInstruction\", {\n    _id: \"\".concat(id)\n  }, true).done(function (data) {\n    rcmail.show_contentframe(false);\n    rcmail.enable_command('deleteinstruction', false);\n  });\n}\n\n//# sourceURL=webpack://aicomposeplugin/./assets/src/settings.js?");

/***/ }),

/***/ "./assets/src/settings/aiResponses/mobileDisplayInstructionsHandler.js":
/*!*****************************************************************************!*\
  !*** ./assets/src/settings/aiResponses/mobileDisplayInstructionsHandler.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   mobileDisplayInstructionsHandle: () => (/* binding */ mobileDisplayInstructionsHandle)\n/* harmony export */ });\nvar state = {\n  elementIndex: -1\n};\nvar eventListenersAdded = false;\nvar rowsArray = [];\nvar prevButton, nextButton, saveButton, backButton;\nfunction nextButtonHandler() {\n  if (state.elementIndex < rowsArray.length - 1) {\n    state.elementIndex++;\n    rcmail.command('addinstructiontemplate', rowsArray[state.elementIndex].element.id);\n  }\n  prevButton.classList.toggle('disabled', state.elementIndex === 0);\n  nextButton.classList.toggle('disabled', state.elementIndex === rowsArray.length - 1);\n}\nfunction previousButtonHandler() {\n  if (state.elementIndex > 0) {\n    state.elementIndex--;\n    rcmail.command('addinstructiontemplate', rowsArray[state.elementIndex].element.id);\n  }\n}\nfunction saveButtonHandler() {\n  rowsArray.forEach(function (instruction) {\n    instruction.element.classList.remove('selected', 'focused');\n  });\n}\nfunction backButtonHandler() {\n  saveButtonHandler();\n  state.elementIndex = -1;\n}\nfunction mobileDisplayInstructionsHandle(id) {\n  var targetDiv = document.querySelector('#layout-content .footer.menu.toolbar.content-frame-navigation.hide-nav-buttons');\n  prevButton = targetDiv.querySelector('.prev');\n  nextButton = targetDiv.querySelector('.next');\n  saveButton = document.querySelector('a#create-button-clone.create.button');\n  backButton = document.querySelector('a.button.icon.back-list-button');\n  if (window.innerWidth < 769) {\n    var _saveButton2;\n    targetDiv.classList.remove('hidden');\n\n    // Uklanjanje starih event listener-a\n    if (eventListenersAdded) {\n      var _saveButton;\n      prevButton.removeEventListener('click', previousButtonHandler);\n      nextButton.removeEventListener('click', nextButtonHandler);\n      (_saveButton = saveButton) === null || _saveButton === void 0 || _saveButton.removeEventListener('click', saveButtonHandler);\n      backButton.removeEventListener('click', backButtonHandler);\n    }\n    var tbody = document.querySelector('#responses-table tbody');\n    var rows = tbody.querySelectorAll(\"tr\");\n    rowsArray = Array.from(rows).map(function (row, index) {\n      // Ažuriranje globalnog rowsArray\n      return {\n        index: index,\n        element: row\n      };\n    });\n    state.elementIndex = rowsArray.findIndex(function (instruction) {\n      return instruction.element.id === id;\n    });\n    prevButton.classList.toggle('disabled', state.elementIndex === 0);\n    nextButton.classList.toggle('disabled', state.elementIndex === rowsArray.length - 1);\n    if (state.elementIndex >= 0) {\n      prevButton.style.display = \"block\";\n      nextButton.style.display = \"block\";\n    } else {\n      prevButton.style.display = \"none\";\n      nextButton.style.display = \"none\";\n    }\n    prevButton.addEventListener('click', previousButtonHandler);\n    nextButton.addEventListener('click', nextButtonHandler);\n    (_saveButton2 = saveButton) === null || _saveButton2 === void 0 || _saveButton2.addEventListener('click', saveButtonHandler);\n    backButton.addEventListener('click', backButtonHandler);\n    eventListenersAdded = true;\n  } else {\n    targetDiv.classList.add('hidden');\n  }\n}\n\n//# sourceURL=webpack://aicomposeplugin/./assets/src/settings/aiResponses/mobileDisplayInstructionsHandler.js?");

/***/ }),

/***/ "./assets/src/utils.js":
/*!*****************************!*\
  !*** ./assets/src/utils.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   capitalize: () => (/* binding */ capitalize),\n/* harmony export */   containsHtmlCharacters: () => (/* binding */ containsHtmlCharacters),\n/* harmony export */   formatText: () => (/* binding */ formatText),\n/* harmony export */   getFormattedMail: () => (/* binding */ getFormattedMail),\n/* harmony export */   translation: () => (/* binding */ translation)\n/* harmony export */ });\nfunction translation(key) {\n  return rcmail.gettext(\"AIComposePlugin.\" + key);\n}\nfunction capitalize(str) {\n  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();\n}\nfunction getFormattedMail(mail) {\n  if (containsHtmlCharacters(mail)) {\n    var tempDiv = document.createElement(\"div\");\n    tempDiv.innerHTML = mail;\n    mail = tempDiv.textContent.replace(/<br\\s*\\/?>/gi, \"\").replace(/\\s+/g, \" \").replace(/\\n/g, '').replace(/\\s{2,}/g, '').trim();\n  }\n  return mail;\n}\nfunction containsHtmlCharacters(inputString) {\n  var htmlCharacterRegex = /[<>&\"']/;\n  return htmlCharacterRegex.test(inputString);\n}\nfunction formatText(text) {\n  return text.replace(/\\\\n/g, \"\\n\").replace(/\\s+/g, \" \").trim();\n}\n\n//# sourceURL=webpack://aicomposeplugin/./assets/src/utils.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/src/settings.js");
/******/ 	
/******/ })()
;