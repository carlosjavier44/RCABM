<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol']!=='admin') {
    header('Location: /RCABM/index.php?view=login'); exit;
}
$adminId  = $_SESSION['usuario']['id'];
$uid_sel  = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
$nombre_sel = null;
if ($uid_sel) {
    $s = $conn->prepare("SELECT nombre FROM usuarios WHERE id=?");
    $s->bind_param("i",$uid_sel); $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $nombre_sel = $r ? $r['nombre'] : "Usuario #$uid_sel";
}
?>

<style>
.admin-chat-wrap{display:flex;height:70vh;max-width:1000px;margin:2rem auto;padding:0 1.5rem;gap:1rem}
.chat-sidebar{width:240px;flex-shrink:0;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;display:flex;flex-direction:column}
.chat-sidebar-header{background:var(--ink);color:white;padding:1rem 1.2rem;font-family:var(--font-display);font-style:italic;font-size:1.05rem}
.chat-sidebar-list{flex:1;overflow-y:auto}
.chat-user-item{padding:0.85rem 1.1rem;border-bottom:1px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background var(--transition)}
.chat-user-item:hover,.chat-user-item.active{background:var(--cream)}
.chat-user-name{font-size:0.88rem;font-weight:500;color:var(--ink)}
.badge-unread{background:var(--rose);color:white;border-radius:99px;font-size:0.7rem;font-weight:700;padding:0.15rem 0.5rem;min-width:20px;text-align:center}
.chat-main{flex:1;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;display:flex;flex-direction:column}
.chat-main-header{background:var(--rose);color:white;padding:1rem 1.5rem;font-family:var(--font-display);font-style:italic;font-size:1.1rem}
.chat-main-empty{flex:1;display:flex;align-items:center;justify-content:center;color:var(--ink-soft)}
.chat-messages{flex:1;overflow-y:auto;padding:1.2rem;display:flex;flex-direction:column;gap:0.75rem}
.msg-row{display:flex;flex-direction:column}
.msg-row.me{align-items:flex-end}
.msg-row.other{align-items:flex-start}
.msg{max-width:70%;padding:0.65rem 1rem;border-radius:var(--radius-md);font-size:0.88rem;line-height:1.5}
.msg-admin{background:var(--rose);color:white;border-bottom-right-radius:4px}
.msg-user{background:var(--cream);color:var(--ink);border:1px solid var(--border);border-bottom-left-radius:4px}
.msg-time{font-size:0.7rem;opacity:0.7;display:block;margin-top:2px}
.tick{font-size:0.75em;margin-left:4px}
.tick.visto{color:#A8E6CF}
.tick.leido{color:rgba(255,255,255,0.6)}
.tick.enviado{color:rgba(255,255,255,0.4)}
.chat-input-area{display:flex;gap:0.5rem;padding:1rem;border-top:1px solid var(--border);background:var(--cream)}
.chat-input-area input{flex:1;padding:0.65rem 1rem;border:1.5px solid var(--border);border-radius:99px;font-family:var(--font-body);font-size:0.88rem;outline:none;background:white}
.chat-input-area input:focus{border-color:var(--rose)}
</style>

<div class="admin-chat-wrap">
  <div class="chat-sidebar">
    <div class="chat-sidebar-header"><i class="fas fa-users"></i> Conversaciones</div>
    <div class="chat-sidebar-list" id="lista-usuarios">
      <div style="padding:1rem;font-size:0.82rem;color:var(--ink-soft)">Cargando…</div>
    </div>
  </div>

  <div class="chat-main">
    <?php if($uid_sel): ?>
      <div class="chat-main-header"><i class="fas fa-user"></i> <?= htmlspecialchars($nombre_sel) ?></div>
      <div class="chat-messages" id="chat-box"></div>
      <div class="chat-input-area">
        <input type="text" id="mensaje" placeholder="Escribe tu respuesta…" autocomplete="off">
        <button class="btn-primary" id="btn-enviar" style="border-radius:99px;padding:0.6rem 1.2rem">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    <?php else: ?>
      <div class="chat-main-empty">
        <div style="text-align:center">
          <i class="fas fa-comments" style="font-size:2.5rem;color:var(--blush);display:block;margin-bottom:0.75rem"></i>
          Selecciona una conversación
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  const ADMIN   = <?= $adminId ?>;
  const UID_SEL = <?= $uid_sel ?? 'null' ?>;

  function esc(t){ return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function tick(m){
    if(m.emisor!=='Admin') return '';
    if(m.visto==1)  return '<span class="tick visto">✓✓</span>';
    if(m.leido==1)  return '<span class="tick leido">✓✓</span>';
    return '<span class="tick enviado">✓</span>';
  }

  async function cargarUsuarios(){
    const r = await fetch('/RCABM/app/controladores/controladorChatAdmin.php?listar_usuarios=1');
    const data = await r.json();
    const lista = document.getElementById('lista-usuarios');
    if(!lista) return;
    if(!data.length){ lista.innerHTML='<div style="padding:1rem;font-size:0.82rem;color:var(--ink-soft)">Sin conversaciones.</div>'; return; }
    lista.innerHTML = data.map(u=>`
      <div class="chat-user-item ${u.id==UID_SEL?'active':''}" onclick="window.location.href='?view=chat_admin&usuario_id=${u.id}'">
        <span class="chat-user-name">${u.nombre}</span>
        ${u.no_leidos>0?`<span class="badge-unread">${u.no_leidos}</span>`:''}
      </div>`).join('');
  }

  async function cargarMensajes(){
    if(!UID_SEL) return;
    const chatBox = document.getElementById('chat-box');
    if(!chatBox) return;
    const atBottom = chatBox.scrollHeight-chatBox.scrollTop<=chatBox.clientHeight+40;
    const r = await fetch(`/RCABM/app/controladores/controladorChatAdmin.php?usuario_id=${UID_SEL}`);
    const data = await r.json();
    if(!Array.isArray(data)) return;
    chatBox.innerHTML = data.map(m=>{
      const isMe = m.emisor==='Admin';
      const hora = new Date(m.fecha).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
      return `<div class="msg-row ${isMe?'me':'other'}">
        <div class="msg ${isMe?'msg-admin':'msg-user'}">
          ${esc(m.mensaje)}${tick(m)}
          <span class="msg-time">${hora}</span>
        </div></div>`;
    }).join('');
    if(atBottom) chatBox.scrollTop=chatBox.scrollHeight;
    cargarUsuarios();
  }

  async function enviar(){
    const input = document.getElementById('mensaje');
    const msg = input.value.trim();
    if(!msg||!UID_SEL) return;
    input.value='';
    await fetch('/RCABM/app/controladores/controladorChatAdmin.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`mensaje=${encodeURIComponent(msg)}&usuario_id=${UID_SEL}`
    });
    cargarMensajes();
  }

  const btn = document.getElementById('btn-enviar');
  const inp = document.getElementById('mensaje');
  if(btn) btn.addEventListener('click',enviar);
  if(inp) inp.addEventListener('keydown',e=>{ if(e.key==='Enter') enviar(); });

  cargarUsuarios();
  if(UID_SEL){ cargarMensajes(); setInterval(cargarMensajes,3000); }
  setInterval(cargarUsuarios,5000);
})();
</script>
