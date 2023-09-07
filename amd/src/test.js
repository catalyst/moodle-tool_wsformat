export const init = () => {
};

console.log("Helloooo this is Jackie");
const webserviceListItems = document.querySelectorAll('li[data-name]');
//const webservices = document.querySelectorAll("#formdata");
const placeholderText = document.querySelector('#placeholder-text');
const wsDescriptionPanel = document.querySelector('#ws-panel');


//console.log(webservices);
let panelTitle = document.getElementById('ws-title');

const webserviceListItemDescriptions = document.querySelectorAll('li[data-desc]');
let panelDescription = document.getElementById('ws-description');

webserviceListItems.forEach(function (webserviceItem) {

    webserviceItem.addEventListener('click', (e) => {
        e.preventDefault();
        const name = webserviceItem.getAttribute("data-name");
        const desc = webserviceItem.getAttribute("data-desc");

        console.log(desc);
        //new array to hold the index values
        const myArray = []
        //const index = webserviceListItems.textContent.toString().indexOf(webserviceItem.textContent);
        //console.log("Name: " + name + " Index: " + index);
        //console.log(name);

        //console.log(webserviceItem.textContent.toString());
        //console.log(webserviceItem.textContent);
       
        
        for (let i = 0; i < webserviceListItems.length; i++)
        {
            //console.log('Index: ' + i + 'Item:' + webserviceListItems[i]);
            //console.log('Index: ' + i);
            //console.log('Item:' + webserviceListItems[i].textContent.toString());
            myArray[i] = webserviceListItems[i].textContent;
        }

        const index = myArray.indexOf(webserviceItem.textContent);
        console.log("Name: " + name + " Index: " + index);
        
        if (placeholderText) {
            placeholderText.remove();
        }

        wsDescriptionPanel.style.display = "flex";
        
        panelTitle.innerHTML= name;

        //console.log(panelDescription.innerHTML.toString());
        //panelDescription.innerHTML = "{{formdata." + index + ".descriptions}}";
        //let myexpression = "{{formdata." + index + ".descriptions}}";
        // panelDescription.innerHTML = {{formdata.[index].descriptions}};

        
       

        

        

    });
});
