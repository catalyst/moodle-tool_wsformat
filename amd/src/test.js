import * as autocomplete from 'core/form-autocomplete'


export const init = () => {
    // window.alert("IT WORKSsssshhhhhkl");
    console.log("wow")
    console.log(autocomplete);
};

const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');
setTimeout(() => {
    const selection = document.querySelector('.form-autocomplete-selection')
    // selection.style.display = 'none';
}, 5000);

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', function () {
        const nameAttr = webserviceItem.getAttribute("data-name");
        console.log(nameAttr);

        if (placeholderText) {
            placeholderText.remove();
        }
        wsDescriptionPanel.style.display = "flex";

    });
});