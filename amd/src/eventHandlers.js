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
 * This module defines the event handlers to be added to the listeners in eventListners.
 *
 * @module     tool_wsformat/eventHandlers
 * @copyright  2023 Djarran Cotleanu, Zach Pregl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as Toast from 'core/toast';

// Select needed elements for event handlers.
const detailContainerElement = document.querySelector('#ws-panel');
const detailPlaceholderText = document.querySelector('#placeholder-text');
const detailTitleElement = document.getElementById('ws-title');
const detailDescriptionElement = document.getElementById('ws-description');
const detailRequestElement = document.getElementById('ws-curl');
const exportButtonElement = document.getElementById('export-button-id');

/**
 * The select change handler. Changed the type parameter of the export button's href.
 * Valid export type relates to the different formats that can be exported to.
 *
 * @param {HTMLSelectEvent} event Properties and methods for select element event
 */
export const exportSelectChangeHandler = (event) => {
    const url = exportButtonElement.href;
    let urlObj = new URL(url);

    const exportType = event.target.value;
    if (exportType === 'curl') {
        urlObj.searchParams.set('export-type', 'curl');
    } else {
        urlObj.searchParams.set('export-type', 'postman');
    }

    exportButtonElement.href = urlObj.href;
};

/**
 * Adds click handlers to each webservice list item.
 *
 * Removes the "Please select webservice" placeholder element.
 *
 * Assign values to placeholder title, description, and curl string elements
 * to the respective webservice selected.
 *
 * @param {Element} webservice
 */
export const webserviceItemClickHandler = (webservice) => {
    webservice.addEventListener('click', (e) => {
        e.preventDefault();

        const name = webservice.getAttribute('data-name');
        const description = webservice.getAttribute('data-desc');
        const curlString = webservice.getAttribute('data-curl');

        if (detailPlaceholderText) {
            detailPlaceholderText.remove();
        }

        detailContainerElement.style.display = 'flex';

        detailTitleElement.innerHTML = name;
        detailDescriptionElement.innerHTML = description;
        detailRequestElement.innerHTML = curlString;

        document.getElementById('details-curl-button').addEventListener('click', (e) => {
            e.preventDefault();
            detailRequestElement.innerHTML = curlString;
        });

        document.getElementById('details-postman-button').addEventListener('click', (e) => {
            e.preventDefault();
            const postman = webservice.getAttribute('data-postman');
            detailRequestElement.innerHTML = postman;
        });
    });
};

/**
 * Copies the cURL string of the selected webservice to the user's clipboard.
 *
 * Adds toast element upon successful copy.
 */
export const copyRequestClickHandler = () => {
    const requestString = document.getElementById('ws-curl').innerText;

    // Source: https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText#examples.
    navigator.clipboard.writeText(requestString)
        .then(() => {
            // From the official Moodle Component Library:
            // https://componentlibrary.moodle.com/admin/tool/componentlibrary/docspage.php/moodle/javascript/toast/
            Toast.add('cURL successfully copied to clipboard', {
                type: 'success'
            });
        })
        .catch(() => {
            Toast.add('cURL unable to be copied to clipboard', {
                type: 'danger'
            });
        });
};

/**
 * Reloads the page, clearing the selection of webservices.
 */
export const clearButtonClickHandler = () => {
    const currentUrl = window.location.href;
    window.location.href = currentUrl;
};
