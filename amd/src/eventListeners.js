// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This module defines the event listeners to be loaded on the index page.
 *
 * @module     tool_wsformat/eventListeners
 * @copyright  2023 Djarran Cotleanu, Zach Pregl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {
    clearButtonClickHandler, copyRequestClickHandler, exportSelectChangeHandler,
    webserviceItemClickHandler
} from './eventHandlers';

export const init = () => {
};

// Select needed elements for event listeners.
const selectedWebservicesListElements = document.querySelectorAll('li[data-name]');
const requestCopyButton = document.querySelector('#curl-copy-button');
const exportSelectElement = document.getElementById('export-type');
const clearButton = document.getElementById('id_ws_clear_button');

// Add event listeners
requestCopyButton.addEventListener('click', copyRequestClickHandler);
selectedWebservicesListElements.forEach(webserviceItemClickHandler);
exportSelectElement.addEventListener('change', exportSelectChangeHandler);
clearButton.addEventListener('click', clearButtonClickHandler);
