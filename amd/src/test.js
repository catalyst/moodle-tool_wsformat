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

function testClick() {
    console.log('Button was clicked');
}
function copyCurlContent() {
    console.log("Test");
    alert("It worked");
    let curlContent = document.getElementById(data-curl).textContent;

    let textArea = document.createElement("textarea");
    textArea.value = curlContent;
    document.body.appendChild(textArea);
    textArea.select();

    document.execCommand("copy");
    document.body.removeChild(textArea);
    alert("It worked");
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
