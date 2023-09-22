import Ajax, { call as fetchMany } from 'core/ajax';
import * as Toast from 'core/toast';

export const init = () => {
    setUpCopyFunctionality();
};


export const testFetch = () => {
    fetchMany([{

    }])
}

//console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

let panelTitle = document.getElementById('ws-title');
let panelDesc = document.getElementById('ws-description');
let panelCurl = document.getElementById('ws-curl');

const curlCopyButton = document.querySelector('#curl-copy-button');
curlCopyButton.addEventListener('click', copyCurlContent)

function copyCurlContent() {
    let curlContent = document.getElementById('ws-curl').innerText;
    // Source: https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText#examples.
    navigator.clipboard.writeText(curlContent)
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

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', (e) => {
        e.preventDefault();
        const name = webserviceItem.getAttribute("data-name");
        const description = webserviceItem.getAttribute("data-desc");
        const curl = webserviceItem.getAttribute("data-curl");

        if (placeholderText) {
            placeholderText.remove();
        }

        wsDescriptionPanel.style.display = "flex";

        panelTitle.innerHTML = name;
        panelDesc.innerHTML = description;
        panelCurl.innerHTML = curl;

    });
});

const selectElement = document.getElementById('export-type');
const exportButton = document.getElementById('export-button-id');
const initalHref = exportButton.href;
exportButton.href = `${initalHref}&export-type=curl`;

selectElement.addEventListener('change', (event) => {
    const exportType = event.target.value;
    exportButton.href = `${initalHref}&export-type=${exportType}`;
    console.log(exportButton.href)
});
