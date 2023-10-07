import { copyRequestClickHandler, exportSelectChangeHandler, webserviceItemClickHandler } from './eventHandlers';

export const init = () => {
};

// Select needed elements for event listeners.
const selectedWebservicesListElements = document.querySelectorAll('li[data-name]');
const requestCopyButton = document.querySelector('#curl-copy-button');
const exportSelectElement = document.getElementById('export-type');
const exportButtonElement = document.getElementById('export-button-id');

// Add event listeners
requestCopyButton.addEventListener('click', copyRequestClickHandler)
selectedWebservicesListElements.forEach(webserviceItemClickHandler);
exportSelectElement.addEventListener('change', exportSelectChangeHandler);

// Set initial href of export button. 
export const initalExportButtonHref = exportButtonElement.href;
exportButtonElement.href = `${initalExportButtonHref}&export-type=curl`;


