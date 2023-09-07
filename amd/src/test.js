export const init = () => {
};

console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');

const wsTitle = document.getElementById('ws-title').innerHTML;

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', (e) => {
        e.preventDefault();
        // const name = webserviceItem.getAttribute("data-name");
        console.log("hello");
        
        // if (placeholderText) {
        //     placeholderText.remove();
        // }

        // wsDescriptionPanel.style.display = "flex";
        
        // wsTitle= "changed to hello!";
       

        

        

    });
});
