export const init = () => {
    setUpCopyFunctionality();
};


//console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

let panelTitle = document.getElementById('ws-title');
let panelDesc = document.getElementById('ws-description');
let panelCurl = document.getElementById('ws-curl');


const setUpCopyFunctionality = () => {
    const copyButton = document.querySelector('button[onclick="copyCurlContent()"]');

    if (copyButton) {
        copyButton.removeAttribute('onclick');  
        copyButton.addEventListener('click', copyCurlContent);
    }
};

function copyCurlContent() {
    console.log("Helloooo this is Zachie");
    const textToCopy = document.getElementById('ws-curl').textContent.trim();
    const textarea = document.createElement('textarea');
    textarea.value = textToCopy;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert('Content copied to clipboard!');
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

        panelTitle.innerHTML= name;
        panelDesc.innerHTML = description;
        panelCurl.innerHTML = curl;

    });
});
