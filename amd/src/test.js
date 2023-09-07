export const init = () => {
};

console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', function () {
        webserviceItem.getAttribute("data-name");

        if (placeholderText) {
            placeholderText.remove();
        }
        wsDescriptionPanel.style.display = "flex";

    });
});
