export const init = () => {
};

//console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

let panelTitle = document.getElementById('ws-title');
let panelDesc = document.getElementById('ws-description');
let panelCurl = document.getElementById('ws-curl');

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
