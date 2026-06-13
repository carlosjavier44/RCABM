<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: /RCABM/index.php?view=login'); exit;
}
$uid = $_SESSION['usuario']['id'];
?>

<section class="chat-section">
  <div class="chat-box">
    <div class="chat-header">
      <i class="fas fa-comment-dots"></i> Escríbenos — estamos aquí para ayudarte
    </div>
    <div class="chat-messages" id="chat-box"></div>
    <div class="chat-input-area">
      <input type="text" id="mensaje" placeholder="Escribe tu mensaje…" autocomplete="off">
      <button class="btn-primary" id="btn-enviar" style="border-radius:99px;padding:0.6rem 1.2rem">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </div>
</section>

<style>
.tick { font-size:0.75em; margin-left:4px; }
.tick.enviado { color:rgba(255,255,255,0.5); }
.tick.leido   { color:rgba(255,255,255,0.6); }
.tick.visto   { color:#A8E6CF; }
.msg-time { font-size:0.7rem; opacity:0.7; display:block; margin-top:2px; }
.msg-row { display:flex; flex-direction:column; }
.msg-row.me    { align-items:flex-end; }
.msg-row.other { align-items:flex-start; }
</style>

<script>
(function(){
  const ME      = <?= $uid ?>;
  const chatBox = document.getElementById('chat-box');
  const input   = document.getElementById('mensaje');
  const btnSend = document.getElementById('btn-enviar');

  function esc(t){ return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function tick(m){
    if(m.emisor_id!=ME) return '';
    if(m.visto==1)  return '<span class="tick visto">✓✓</span>';
    if(m.leido==1)  return '<span class="tick leido">✓✓</span>';
    return '<span class="tick enviado">✓</span>';
  }

  function render(msgs){
    const atBottom = chatBox.scrollHeight-chatBox.scrollTop <= chatBox.clientHeight+40;
    chatBox.innerHTML = msgs.map(m=>{
      const isMe = m.emisor_id==ME;
      const hora = new Date(m.fecha).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
      return `<div class="msg-row ${isMe?'me':'other'}">
        <div class="msg ${isMe?'msg-user':'msg-admin'}">
          ${esc(m.mensaje)}${tick(m)}
          <span class="msg-time">${hora}</span>
        </div></div>`;
    }).join('');
    if(atBottom) chatBox.scrollTop = chatBox.scrollHeight;
  }

  async function load(){
    try{
      const r = await fetch('/RCABM/app/controladores/controladorChat.php?action=obtener');
      const d = await r.json();
      if(Array.isArray(d)) render(d);
    }catch(e){}
  }

  async function send(){
    const msg = input.value.trim();
    if(!msg) return;
    input.value='';
    const fd = new FormData();
    fd.append('action','enviar');
    fd.append('mensaje',msg);
    await fetch('/RCABM/app/controladores/controladorChat.php',{method:'POST',body:fd});
    load();
  }

  btnSend.addEventListener('click',send);
  input.addEventListener('keydown',e=>{ if(e.key==='Enter') send(); });
  load();
  setInterval(load,3000);
})();
</script>
