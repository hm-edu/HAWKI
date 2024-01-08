import {encodingForModel } from "js-tiktoken";

function requestMessages() {
    const tiktoken = encodingForModel(document.getElementById("GPT-Version").value);
    const messagesElement = document.querySelector(".messages");
    const messageElements = messagesElement.querySelectorAll(".message");

    let messages = [];
    var token_count = 0;

    var selectElement = document.getElementById("GPT-Version");
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    var token_limit = selectedOption.getAttribute("token_limit");
    
    for(var i = messageElements.length - 1; i >= 0; i--){
        token_count += tiktoken.encode(messageElements[i].querySelector(".message-text").innerHTML).length;
        if (token_count > token_limit) {
            break;
        }

        let messageObject = {};
        messageObject.role = messageElements[i].dataset.role;
        messageObject.content = messageElements[i].querySelector(".message-text").innerHTML;
        messages.unshift(messageObject);
    };

    return messages;
}

visualViewport.addEventListener("resize", update);
visualViewport.addEventListener("scroll", update);
addEventListener("scroll", update);
addEventListener("load", update);

function update(event) {
  event.preventDefault();
  if (!window.visualViewport) {
    return;
  }

  window.scrollTo(0, 0);
  document.querySelector(".wrapper").style.height =
    window.visualViewport.height + "px";
}

function load(element, filename){
    let messagesElement = document.querySelector(".messages");
    fetch(`views/${filename}`)
      .then((response) => {
        return response.text();
      })
      .then((html) => {
        messagesElement.innerHTML = html;
        return  
      }).then(()=>{
          /*
          let messages = document.querySelectorAll(".message-text");
          messages.forEach(message => {
              message.contentEditable = true;
          })
          */
          if(sessionStorage.getItem("truth")){
              document.querySelector("#truth")?.remove();
          }
      });
    
    document.querySelector(".menu-item.active")?.classList.remove("active");
    document.querySelector(".menu-item.open")?.classList.remove("open");
    document.querySelector(".submenu-item.active")?.classList.remove("active");
    element.classList.add("active");
    
    element.closest(".submenu")?.previousElementSibling.classList.add("open");
    element.closest(".submenu")?.previousElementSibling.classList.add("active");
    
    document.querySelector(".main").scrollIntoView({ behavior: "smooth", block: "end", inline: "nearest" });
    
    
    
    
}

function submenu(element){
    if(element.classList.contains('active')){
        element.classList.remove("active");
        element.nextElementSibling.classList.remove("active");
    }else{
        document.querySelector(".menu-item.active")?.classList.remove("active");
        document.querySelector(".submenu.active")?.classList.remove("active");
        document.querySelector(".menu-item.open")?.classList.remove("open");
        element.classList.add("active");
        element.nextElementSibling.classList.add("active");
    }
}

function handleKeydown(event){
    if(event.key == "Enter" && !event.shiftKey){
        event.preventDefault();
        request();
    } 
}

async function request(){
    const messageTemplate = document.querySelector('#message');
    const inputField = document.querySelector(".input-field");
    
    let message = {};
    message.role = "user";
    message.content = inputField.value.trim();
    inputField.value = "";
    addMessage(message);
    resize(inputField);
    
    document.querySelector('.limitations')?.remove();
    
    const requestObject = {};
    requestObject.model = document.getElementById("GPT-Version").value || 'gpt-3.5-turbo';
    requestObject.stream = true;
    requestObject.messages = [];

    requestObject.messages = requestMessages();

    console.log(requestObject)
    
    postData('stream-api.php', requestObject)
    .then(stream => processStream(stream))
    .catch(error => console.error('Error:', error));
}

async function postData(url = '', data = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return response.body;
}

async function processStream(stream) {
    const reader = stream.getReader();
    
    const messagesElement = document.querySelector(".messages");
    const messageTemplate = document.querySelector('#message');
    const messageElement = messageTemplate.content.cloneNode(true);
    
    messageElement.querySelector(".message-text").innerHTML = "";
    messageElement.querySelector(".message").dataset.role = "assistant";
    messagesElement.appendChild(messageElement);
    
    const messageText = messageElement.querySelector(".message-text");

    while (true) {
        const { done, value } = await reader.read();

        if (done) {
            console.log('Stream closed.');
            document.querySelector(".message:last-child").querySelector(".message-text").innerHTML = document.querySelector(".message:last-child").querySelector(".message-text").innerHTML.replace(/```([\s\S]+?)```/g, '<pre><code>$1</code></pre>').replace(/\*\*.*?\*\*/g, '');;
            //hljs.highlightAll();
            document.querySelector(".message:last-child").querySelector(".message-text").querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });

            document.querySelector(".message:last-child").querySelector(".message-text").innerHTML = linkify(document.querySelector(".message:last-child").querySelector(".message-text").innerHTML);
            break;
        }

        const decodedData = new TextDecoder().decode(value);
        //console.log(decodedData);
        let chunks = decodedData.split("data: ");
        chunks.forEach((chunk, index) => {
            if(chunk.indexOf('finish_reason":"stop"') > 0) return false;
            if(chunk.indexOf('DONE') > 0) return false;
            if(chunk.indexOf('role') > 0) return false;
            if(chunk.length === 0) return false;
            // First check if chunk is valid json.
            // Otherwise we do not see the correct error message.
            try {
                const json = JSON.parse(chunk);
                if ("choices" in json) {
                    // console.log(json["choices"]);
                    // normal response
                    document.querySelector(".message:last-child").dataset.content += json["choices"][0]["delta"].content;
                    document.querySelector(".message:last-child").querySelector(".message-text").innerHTML +=  escapeHTML(json["choices"][0]["delta"].content);
                } else {
                    if ("error" in json) {
                        if ("message" in json.error) {
                            // console.log(json.error.message);
                            document.querySelector(".message:last-child").querySelector(".message-text").innerHTML =
                                '<em>' + json.error.message + '</em>';
                        } else {
                            console.log(json.error);
                        }
                    } else {
                        console.log(json);
                    }
                }
            } catch(error) {
                console.log(chunk);
                console.error(error.message);
            }
        })

        scrollToLast();
    }
}

function escapeHTML(str) {
return str.replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
}


function addMessage(message){
    const messagesElement = document.querySelector(".messages");
    const messageTemplate = document.querySelector('#message');
    const inputField = document.querySelector(".input-field");
    const messageElement = messageTemplate.content.cloneNode(true);
    
    messageElement.querySelector(".message-text").innerHTML = escapeHTML(message.content);
    messageElement.querySelector(".message").dataset.role = message.role;
    messageElement.querySelector(".message").dataset.content = message.content;

    
    if(message.role == "assistant"){
        messageElement.querySelector(".message-icon").textContent = "AI";
    }else{
        messageElement.querySelector(".message-icon").textContent = 'ich';
        messageElement.querySelector(".message").classList.add("me");
    }
    
    messagesElement.appendChild(messageElement);
    
    scrollToLast();
    return messageElement;
}

function scrollToLast(){
    const messagesElement = document.querySelector(".messages");
    messagesElement.scrollTo({
      top: messagesElement.scrollHeight,
      left: 0,
      behavior: "smooth",
    });
}

function resize(element) {
    element.style.height = 'auto';
    element.style.height = element.scrollHeight + "px";
    element.scrollTop = element.scrollHeight;
    element.scrollTo(element.scrollTop, (element.scrollTop + element.scrollHeight));
}

function copyToInput(selector) {
    document.querySelector(".input-field").value = document.querySelector(selector).textContent.trim();
    resize(document.querySelector(".input-field"));
}

if(sessionStorage.getItem("data-protection")){
    document.querySelector("#data-protection").remove();
}

//if(localStorage.getItem("gpt4")){
//	document.querySelector("#gpt4").remove();
//}

function modalClick(element){
    sessionStorage.setItem(element.id, "true")
    element.remove();
}

async function voteHover(){
    let messages = document.querySelectorAll(".message");
      
      messages.forEach((message)=>{
          let voteButtons = message.querySelectorAll(".vote")
          
          voteButtons.forEach((voteButton)=>{
              if(localStorage.getItem(voteButton.dataset.id)){
                  voteButton.classList.remove("vote-hover");
              }else{
                  voteButton.classList.add("vote-hover");
              }
          })
          
      })
}

document.querySelectorAll('details').forEach((D,_,A)=>{
  D.ontoggle =_=>{ if(D.open) A.forEach(d =>{ if(d!=D) d.open=false })}
})

function linkify(htmlString) {
  const urlRegex = /((https?:\/\/|www\.)[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*))/g;
  return htmlString.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
}

window.modalClick = modalClick;
window.request = request;
window.load = load;
window.submenu = submenu;
window.resize = resize;
window.handleKeydown = handleKeydown;