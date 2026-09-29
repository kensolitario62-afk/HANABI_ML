<?php /* Shared look for the Messenger module (same design language as the Student module). */ ?>
<style>
:root{
    --ss-a:#8b0000; --ss-b:#c0392b; --ss-soft:rgba(192,57,43,.12);
    --ss-line:rgba(128,128,128,.25); --ss-muted:rgba(128,128,128,.9);
}
.ss-card{border:0;border-radius:14px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-card>.card-header{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;padding:16px 20px}
.ss-card>.card-header .card-title{font-weight:600;letter-spacing:.3px;margin:0}
.ss-card>.card-header i{margin-right:8px;opacity:.9}
.ss-card .card-body{padding:0}
.ss-btn{border-radius:30px!important;padding:8px 22px;font-weight:600;border:0!important;box-shadow:0 3px 10px rgba(0,0,0,.25)}
.ss-btn.main{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}
.ss-btn.light{background:rgba(255,255,255,.2);color:#fff;box-shadow:none;padding:6px 16px}
.ss-btn.light:hover{background:rgba(255,255,255,.32)}

/* ---------- messenger layout ---------- */
.ms-wrap{display:flex;height:calc(100vh - 220px);min-height:540px}
.ms-side{width:340px;flex:0 0 340px;border-right:1px solid var(--ss-line);display:flex;flex-direction:column;min-width:0}
.ms-main{flex:1;display:flex;flex-direction:column;min-width:0;position:relative}
.ms-search{padding:14px 14px 8px}
.ms-search .ss-input{position:relative}
.ms-search .ss-input>i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--ss-b);font-size:.9rem;pointer-events:none}
.ms-search .form-control{padding-left:36px;border-radius:30px;border:1px solid var(--ss-line);height:calc(2.2rem + 2px)}
.ms-search .form-control:focus{border-color:var(--ss-b);box-shadow:0 0 0 .2rem rgba(192,57,43,.22)}
.ms-tabs{display:flex;padding:0 14px 10px}
.ms-tabs a{flex:1;text-align:center;padding:6px 0;border-radius:30px;font-weight:600;font-size:.8rem;color:inherit;cursor:pointer;margin-right:6px}
.ms-tabs a:last-child{margin-right:0}
.ms-tabs a.active{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}
.ms-list{flex:1;overflow-y:auto}
.ms-item{display:flex;align-items:center;padding:11px 14px;cursor:pointer;border-left:4px solid transparent;transition:background .15s}
.ms-item:hover{background:var(--ss-soft)}
.ms-item.active{background:var(--ss-soft);border-left-color:var(--ss-b)}
.ms-av{position:relative;width:44px;height:44px;flex:0 0 44px;border-radius:50%;background:linear-gradient(135deg,var(--ss-a),var(--ss-b));color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;margin-right:12px;box-shadow:0 3px 10px rgba(0,0,0,.25)}
.ms-av.group{border-radius:14px}
.ms-av .dot{position:absolute;right:0;bottom:0;width:12px;height:12px;border-radius:50%;background:#28c76f;border:2px solid #fff;display:none}
.ms-av.on .dot{display:block}
.ms-info{flex:1;min-width:0}
.ms-info .top{display:flex;justify-content:space-between;align-items:baseline}
.ms-info b{font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ms-info time{font-size:.68rem;color:var(--ss-muted);margin-left:8px;white-space:nowrap}
.ms-info .bot{display:flex;justify-content:space-between;align-items:center}
.ms-info .pv{font-size:.8rem;color:var(--ss-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ms-badge{min-width:20px;height:20px;padding:0 6px;border-radius:30px;background:var(--ss-b);color:#fff;font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;margin-left:8px}
.ms-role{display:inline-block;font-size:.62rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--ss-b);background:var(--ss-soft);border-radius:30px;padding:1px 8px;margin-right:6px}

/* chat header */
.ms-head{display:flex;align-items:center;padding:12px 18px;background:var(--ss-soft);border-bottom:2px solid var(--ss-b)}
.ms-head .ms-av{width:42px;height:42px;flex-basis:42px}
.ms-head b{display:block;font-size:1rem}
.ms-head small{color:var(--ss-muted)}
.ms-back{display:none;margin-right:10px;cursor:pointer;font-size:1.2rem;color:var(--ss-b)}

/* thread */
.ms-thread{flex:1;overflow-y:auto;padding:18px 20px;display:flex;flex-direction:column}
.ms-day{align-self:center;margin:12px 0;padding:3px 14px;border-radius:30px;background:var(--ss-soft);color:var(--ss-muted);font-size:.7rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase}
.ms-row{display:flex;margin-bottom:6px;max-width:100%}
.ms-row.mine{justify-content:flex-end}
.ms-bub{position:relative;max-width:68%;padding:9px 14px;border-radius:16px;border:1px solid var(--ss-line);word-break:break-word;white-space:pre-wrap}
.ms-row.mine .ms-bub{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;border-bottom-right-radius:4px;box-shadow:0 3px 10px rgba(0,0,0,.2)}
.ms-row:not(.mine) .ms-bub{border-bottom-left-radius:4px;background:var(--ss-soft)}
.ms-bub .who{display:block;font-size:.7rem;font-weight:700;color:var(--ss-b);margin-bottom:2px}
.ms-bub .meta{display:block;text-align:right;font-size:.64rem;opacity:.75;margin-top:3px}
.ms-bub.gone{font-style:italic;opacity:.65;background:transparent!important;color:inherit!important;border:1px dashed var(--ss-line)!important;box-shadow:none!important}
.ms-bub img.pic{max-width:240px;max-height:220px;border-radius:10px;display:block;margin-bottom:4px}
.ms-bub a.file{display:flex;align-items:center;padding:8px 10px;border-radius:10px;background:rgba(0,0,0,.15);color:inherit;text-decoration:none;font-weight:600;font-size:.85rem;margin-bottom:4px}
.ms-row:not(.mine) .ms-bub a.file{background:var(--ss-soft)}
.ms-bub a.file i{margin-right:8px}
.ms-row{align-items:center}
.ms-acts{display:flex;align-items:center;opacity:0;transition:opacity .15s;margin:0 6px;flex:0 0 auto}
.ms-row:hover .ms-acts{opacity:1}
.ms-acts i{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;cursor:pointer;color:var(--ss-muted);font-size:.85rem;transition:background .15s,color .15s}
.ms-acts i:hover{background:var(--ss-soft);color:var(--ss-b)}
@media(hover:none){.ms-acts{opacity:1}}
.ms-quote{border-left:3px solid var(--ss-b);background:rgba(0,0,0,.14);border-radius:8px;padding:5px 10px;margin-bottom:6px;cursor:pointer;font-size:.8rem;white-space:normal}
.ms-row:not(.mine) .ms-quote{background:rgba(128,128,128,.16)}
.ms-quote b{display:block;font-size:.7rem;letter-spacing:.3px}
.ms-quote span{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;opacity:.85}
.ms-row.flash .ms-bub{animation:msflash 1.2s}
@keyframes msflash{0%,60%{box-shadow:0 0 0 3px var(--ss-b)}100%{box-shadow:none}}
.ms-rx{display:flex;flex-wrap:wrap;margin-top:4px}
.ms-rx:empty{display:none}
.ms-rx .chip{display:inline-flex;align-items:center;padding:1px 8px;margin:0 4px 2px 0;border-radius:30px;background:rgba(128,128,128,.25);font-size:.78rem;cursor:pointer;border:1px solid transparent;white-space:nowrap}
.ms-rx .chip b{margin-left:4px;font-size:.7rem}
.ms-rx .chip.me{border-color:var(--ss-b);background:var(--ss-soft)}
.ms-row.mine .ms-rx .chip.me{background:rgba(255,255,255,.3);border-color:#fff}
.ms-react-pop{display:none;position:fixed;z-index:1080;padding:6px 8px;border-radius:30px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.4)}
.ms-react-pop span{display:inline-block;width:36px;height:36px;line-height:36px;text-align:center;font-size:1.35rem;border-radius:50%;cursor:pointer;transition:transform .12s}
.ms-react-pop span:hover{transform:scale(1.3);background:var(--ss-soft)}
.ms-chip>span:first-child{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-right:10px}

/* composer */
.ms-composer{border-top:1px solid var(--ss-line);padding:12px 16px;position:relative}
.ms-chip{display:none;align-items:center;justify-content:space-between;padding:6px 12px;margin-bottom:8px;border-radius:10px;background:var(--ss-soft);border-left:4px solid var(--ss-b);font-size:.82rem;font-weight:600}
.ms-chip i.x{cursor:pointer;color:var(--ss-b)}
.ms-bar{display:flex;align-items:flex-end}
.ms-tool{width:40px;height:40px;flex:0 0 40px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:var(--ss-b);cursor:pointer;margin-right:6px;transition:background .15s}
.ms-tool:hover{background:var(--ss-soft)}
.ms-bar textarea{flex:1;resize:none;border-radius:22px;border:1px solid var(--ss-line);padding:9px 16px;max-height:120px;min-height:40px;background:transparent;color:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.ms-bar textarea:focus{border-color:var(--ss-b);box-shadow:0 0 0 .2rem rgba(192,57,43,.22)}
.ms-send{width:42px;height:42px;flex:0 0 42px;border:0;border-radius:50%;margin-left:8px;color:#fff;background:linear-gradient(120deg,var(--ss-a),var(--ss-b));box-shadow:0 3px 10px rgba(0,0,0,.25);transition:transform .15s}
.ms-send:hover{transform:translateY(-2px)}
.ms-emoji{display:none;position:absolute;bottom:64px;left:16px;width:264px;padding:8px;border-radius:14px;background:#fff;color:#222;box-shadow:0 10px 30px rgba(0,0,0,.35);z-index:5;flex-wrap:wrap}
.ms-emoji span{width:36px;height:34px;text-align:center;line-height:34px;font-size:1.2rem;cursor:pointer;border-radius:8px}
.ms-emoji span:hover{background:var(--ss-soft)}

.ms-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:var(--ss-muted);padding:20px}
.ms-empty i{font-size:3.2rem;margin-bottom:12px;color:var(--ss-b)}
.ms-empty.small{flex:none;padding:30px 14px}
.ms-empty.small i{font-size:2rem}

/* ---------- modal (same as Student module) ---------- */
.ss-modal .modal-content{border:0;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.45)}
.ss-modal .modal-header{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;padding:18px 24px;align-items:center}
.ss-modal .modal-title{display:flex;align-items:center;font-weight:600;font-size:1.25rem}
.ss-modal .ss-badge{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.1rem}
.ss-modal .modal-header .close{color:#fff;opacity:.85;text-shadow:none;font-size:1.8rem}
.ss-modal .modal-body{padding:22px 26px 10px}
.ss-modal .modal-footer{border-top:1px solid var(--ss-line);padding:14px 26px}
.ss-modal .modal-footer .btn{border-radius:30px;padding:8px 26px;font-weight:600;border:0}
.ss-modal .modal-footer .btn-default{background:rgba(128,128,128,.2);color:inherit}
.ss-modal .modal-footer .btn-primary{background:linear-gradient(120deg,var(--ss-a),var(--ss-b))}
.ss-group{display:flex;align-items:center;font-size:.72rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ss-b);margin:6px 0 12px}
.ss-group:after{content:"";flex:1;height:1px;background:var(--ss-line);margin-left:12px}
.ss-group i{margin-right:8px}
.ss-modal label{font-weight:600;font-size:.82rem;margin-bottom:5px}
.ss-input{position:relative}
.ss-input>i{position:absolute;left:13px;top:18px;transform:translateY(-50%);color:var(--ss-b);font-size:.9rem;pointer-events:none;z-index:2}
.ss-input>.form-control{padding-left:36px;height:calc(2.35rem + 2px);border-radius:10px;border:1px solid var(--ss-line)}
.ss-input>.form-control:focus{border-color:var(--ss-b);box-shadow:0 0 0 .2rem rgba(192,57,43,.22)}
.ms-pick{max-height:260px;overflow-y:auto;border:1px solid var(--ss-line);border-radius:12px}
.ms-pick label{display:flex;align-items:center;margin:0;padding:8px 12px;border-bottom:1px dashed var(--ss-line);cursor:pointer;font-weight:600}
.ms-pick label:last-child{border:0}
.ms-pick label:hover{background:var(--ss-soft)}
.ms-pick input{margin-right:12px}
.ms-pick small{margin-left:auto;color:var(--ss-muted)}

@media(max-width:767px){
    .ms-side{width:100%;flex-basis:100%;border:0}
    .ms-main{display:none}
    .ms-wrap.chat .ms-side{display:none}
    .ms-wrap.chat .ms-main{display:flex}
    .ms-back{display:block}
    .ms-bub{max-width:85%}
}
</style>