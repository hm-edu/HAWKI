function update(event) {
    event.preventDefault();
  if (!window.visualViewport) {
      return;
  }
    window.scrollTo(0, 0);
    document.querySelector(".wrapper").style.height = window.visualViewport.height + "px";
}

function load(element, filename){
    let messagesElement = document.querySelector(".messages");
    fetch(`?page=${filename}`)
    .then((response) => {
        return response.text();
    })
    .then((html) => {
        messagesElement.innerHTML = html;
        return;
    }).then(()=>{
        CheckModals();
        if(filename == "feedback_loader.php"){
            voteHover();
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

//#region Scrolling Controls
//scrolls to the end of the panel.
//if new message is send, it forces the panel to scroll down.
//if the current message is continuing to expand force expand is false.
//(if the user is trying to read the upper parts it wont jump back down.)
let isScrolling = false;
function scrollToLast(forceScroll){
    const msgsPanel = document.querySelector('.messages');
    const documentHeight = msgsPanel.scrollHeight;
    const currentScroll = msgsPanel.scrollTop + msgsPanel.clientHeight;
    if (!isScrolling && (forceScroll || documentHeight - currentScroll < 200)) {
        const messagesElement = document.querySelector(".messages");

        messagesElement.scrollTo({
            top: messagesElement.scrollHeight,
            left: 0,
            behavior: "smooth",
        });
    }
}
//#endregion


document.querySelectorAll('details').forEach((D,_,A)=>{
    D.ontoggle =_=>{ if(D.open) A.forEach(d =>{ if(d!=D) d.open=false })}
})

let spPanelOpen = false;
document.addEventListener('click', function(event) {
    const isClickOnPanel = document.getElementById('system-prompt-panel').contains(event.target);
    const isClickOnBtn = document.getElementById('system-prompt-btn').contains(event.target);
    if (!isClickOnPanel && !isClickOnBtn) {
        ToggleSystemPrompt(false);
    }
});

function ToggleSystemPrompt(activation){
    const promptPanel = document.getElementById('system-prompt-panel');

    if(spPanelOpen && activation) activation = false;

    if(activation == true){
        const promptText = document.getElementById('system-prompt');
        const msg = document.querySelector('.messages').querySelector('.message');
        if(msg.getAttribute('data-role') !== 'system'){
            console.log('System Prompt not found!');
            return
        }
        const systemPrompt = msg.querySelector('.message-content').querySelector('.message-text').innerText;
        promptText.innerHTML = "&Prime;" + systemPrompt.trim() + "&rdquo;";
    
        promptPanel.style.display = 'block';
        requestAnimationFrame(() => {
            promptPanel.style.opacity = '1';
        });
    }
    else{
        promptPanel.style.opacity = '0';
        setTimeout(() => {
            promptPanel.style.display = 'none';
            document.getElementById('system-prompt-info').style.display = 'none'
        }, 300);
    }
    spPanelOpen = !spPanelOpen;
}

function toggleSystemPromptInfo(){
    const info = document.getElementById('system-prompt-info');
    if(info.style.display === 'none'){
        console.log('display none');

        info.style.display = 'block';
    }else{
        console.log('display block');

        info.style.display = 'none';
    }
}
