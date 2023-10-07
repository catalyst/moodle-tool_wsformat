
import * as Toast from 'core/toast';
import { initalExportButtonHref } from './eventListeners';

// Select needed elements for event handlers.
const detailContainerElement = document.querySelector('#ws-panel');
const detailPlaceholderText = document.querySelector('#placeholder-text');
const detailTitleElement = document.getElementById('ws-title');
const detailDescriptionElement = document.getElementById('ws-description');
const detailRequestElement = document.getElementById('ws-curl');
const exportButtonElement = document.getElementById('export-button-id');

export const exportSelectChangeHandler = (event) => {
    const exportType = event.target.value;
    exportButtonElement.href = `${initalExportButtonHref}&export-type=${exportType}`;
    console.log(exportButtonElement.href)
}

export const webserviceItemClickHandler = (webservice) => {
    webservice.addEventListener('click', (e) => {
        e.preventDefault();

        const name = webservice.getAttribute("data-name");
        const description = webservice.getAttribute("data-desc");
        const curlString = webservice.getAttribute("data-curl");

        if (detailPlaceholderText) {
            detailPlaceholderText.remove();
        }

        detailContainerElement.style.display = "flex";

        detailTitleElement.innerHTML = name;
        detailDescriptionElement.innerHTML = description;
        detailRequestElement.innerHTML = curlString;

        document.getElementById("details-curl-button").addEventListener('click', (e) => {
            e.preventDefault();
            detailRequestElement.innerHTML = curlString;
        });

        document.getElementById("details-postman-button").addEventListener('click', (e) => {
            e.preventDefault();
            const postman = webservice.getAttribute("data-postman");
            detailRequestElement.innerHTML = postman;
        });
    })
}

export const copyRequestClickHandler = () => {
    const requestString = document.getElementById('ws-curl').innerText;

    // Source: https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText#examples.
    navigator.clipboard.writeText(requestString)
        .then(() => {
            // From the official Moodle Component Library:https://componentlibrary.moodle.com/admin/tool/componentlibrary/docspage.php/moodle/javascript/toast/ 
            Toast.add('cURL successfully copied to clipboard', {
                type: 'success'
            })
        })
        .catch(() => {
            Toast.add('cURL unable to be copied to clipboard', {
                type: 'danger'
            })
        })
}