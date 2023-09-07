export const init = () => {
};

console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

let wsTitle = document.getElementById('ws-title');

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', (e) => {
        e.preventDefault();
        const name = webserviceItem.getAttribute("data-name");
        console.log(name);
        
        if (placeholderText) {
            placeholderText.remove();
        }

        wsDescriptionPanel.style.display = "flex";
        
        wsTitle.innerHTML= name;
       

        

        

    });
});
