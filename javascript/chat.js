const form = document.querySelector(".typing-area"),
incoming_id = form.querySelector(".incoming_id").value,
inputField = form.querySelector(".input-field"),
fileInput = form.querySelector('input[name="attachment"]'),
sendBtn = form.querySelector("button[type='submit']"),
chatBox = document.querySelector(".chat-box");

form.onsubmit = (e)=>{
    e.preventDefault();
}

inputField.focus();
function updateSendBtn(){
    if((inputField.value && inputField.value.trim() !== "") || (fileInput && fileInput.files.length > 0)){
        sendBtn.classList.add("active");
    }else{
        sendBtn.classList.remove("active");
    }
}
inputField.onkeyup = updateSendBtn;
fileInput && (fileInput.onchange = updateSendBtn);

sendBtn.onclick = ()=>{
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/insert-chat.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              inputField.value = "";
              if(fileInput){ fileInput.value = ""; }
              scrollToBottom();
              updateSendBtn();
          }
      }
    }
    let formData = new FormData(form);
    xhr.send(formData);
}
chatBox.onmouseenter = ()=>{
    chatBox.classList.add("active");
}

chatBox.onmouseleave = ()=>{
    chatBox.classList.remove("active");
}

setInterval(() =>{
        fetchChat();
}, 500);

// Fetch chat helper; if initial === true, force scroll to bottom after first render
let firstLoad = true;
function fetchChat(){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/get-chat.php", true);
    xhr.onload = ()=>{
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
                let data = xhr.response;
                chatBox.innerHTML = data;
                if(firstLoad){
                    scrollToBottom();
                    firstLoad = false;
                }else if(!chatBox.classList.contains("active")){
                    scrollToBottom();
                }
            }
        }
    }
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("incoming_id="+incoming_id);
}

// Trigger an immediate initial fetch so the view scrolls without waiting
fetchChat();

function scrollToBottom(){
    chatBox.scrollTop = chatBox.scrollHeight;
  }
  