<?php /* Shared look for the Schedule Day module (included by list.php and view.php). Mirrors the Schedule Time module's design. */ ?>
<style>
:root{
    --ss-a:#8b0000; --ss-b:#c0392b; --ss-soft:rgba(192,57,43,.12);
    --ss-line:rgba(128,128,128,.25); --ss-muted:rgba(128,128,128,.9);
}

/* ---------- list card ---------- */
.ss-card{border:0;border-radius:14px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-card>.card-header{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;padding:16px 20px}
.ss-card>.card-header .card-title{font-weight:600;letter-spacing:.3px;margin:0}
.ss-card>.card-header i{margin-right:8px;opacity:.9}
.ss-card .card-body{padding:20px}
#tblscheduleday thead th{background:var(--ss-soft);border-bottom:2px solid var(--ss-b);font-size:.78rem;letter-spacing:.6px;text-transform:uppercase;white-space:nowrap}
#tblscheduleday tbody tr{transition:background .15s}
#tblscheduleday tbody tr:hover{background:var(--ss-soft)!important}
#tblscheduleday .btn-xs{border-radius:8px;margin:1px 1px;box-shadow:0 2px 6px rgba(0,0,0,.2)}
.ss-actions .btn{border-radius:30px!important;padding:8px 22px;font-weight:600;border:0;box-shadow:0 3px 10px rgba(0,0,0,.25);transition:transform .15s}
.ss-actions .btn:hover{transform:translateY(-2px)}
.ss-actions .btn-add{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}

/* ---------- modals ---------- */
.ss-modal .modal-content{border:0;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.45)}
.ss-modal .modal-header{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;padding:18px 24px;align-items:center}
.ss-modal .modal-title{display:flex;align-items:center;font-weight:600;font-size:1.25rem}
.ss-modal .ss-badge{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.1rem}
.ss-modal .modal-header .close{color:#fff;opacity:.85;text-shadow:none;font-size:1.8rem}
.ss-modal .modal-header .close:hover{opacity:1}
.ss-modal .modal-body{padding:22px 26px 10px}
.ss-modal .modal-footer{border-top:1px solid var(--ss-line);padding:14px 26px}
.ss-modal .modal-footer .btn{border-radius:30px;padding:8px 26px;font-weight:600;border:0}
.ss-modal .modal-footer .btn-default{background:rgba(128,128,128,.2);color:inherit}
.ss-modal .modal-footer .btn-primary{background:linear-gradient(120deg,var(--ss-a),var(--ss-b))}
.ss-modal .modal-footer .btn-success{background:linear-gradient(120deg,#1e8e4e,#28c76f)}

.ss-group{display:flex;align-items:center;font-size:.72rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ss-b);margin:6px 0 12px}
.ss-group:after{content:"";flex:1;height:1px;background:var(--ss-line);margin-left:12px}
.ss-group i{margin-right:8px}
.ss-modal label{font-weight:600;font-size:.82rem;margin-bottom:5px}
.ss-input{position:relative}
.ss-input>i{position:absolute;left:13px;top:18px;transform:translateY(-50%);color:var(--ss-b);font-size:.9rem;pointer-events:none;z-index:2}
.ss-input>.form-control{padding-left:36px;height:calc(2.35rem + 2px);border-radius:10px;border:1px solid var(--ss-line);transition:border-color .15s,box-shadow .15s}
.ss-input>textarea.form-control{height:auto;min-height:80px}
.ss-input>.form-control:focus{border-color:var(--ss-b);box-shadow:0 0 0 .2rem rgba(192,57,43,.22)}
.ss-hint{display:flex;align-items:center;padding:10px 14px;border-radius:10px;background:var(--ss-soft);font-size:.82rem;margin-bottom:14px}
.ss-hint i{color:var(--ss-b);margin-right:10px}

/* ---------- view page ---------- */
.ss-hero{border:0;border-radius:16px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-hero .ss-cover{background:linear-gradient(135deg,var(--ss-a),var(--ss-b));padding:30px 20px 56px;text-align:center;color:#fff}
.ss-hero .ss-cover h3{font-size:1.25rem;font-weight:700;margin:12px 0 2px}
.ss-hero .ss-cover small{opacity:.85;letter-spacing:1px;text-transform:uppercase}
.ss-hero .ss-cover img{width:110px;height:110px;object-fit:cover;border-radius:50%;border:4px solid rgba(255,255,255,.85);box-shadow:0 6px 18px rgba(0,0,0,.35)}
.ss-hero .ss-body{margin-top:-28px;border-radius:22px 22px 0 0;padding:18px 20px;background:inherit;position:relative}
.ss-stat{display:flex;align-items:center;padding:10px 0;border-bottom:1px dashed var(--ss-line)}
.ss-stat:last-of-type{border:0}
.ss-stat i{width:36px;height:36px;border-radius:10px;background:var(--ss-soft);color:var(--ss-b);display:inline-flex;align-items:center;justify-content:center;margin-right:12px;flex:0 0 36px}
.ss-stat span{color:var(--ss-muted);font-size:.72rem;display:block;text-transform:uppercase;letter-spacing:.5px}
.ss-stat b{font-size:.95rem}
.ss-panel{border:0;border-radius:14px;box-shadow:0 6px 22px rgba(0,0,0,.15);overflow:hidden}
.ss-panel .card-header{border-bottom:2px solid var(--ss-b);background:var(--ss-soft)}
.ss-panel .card-title{font-weight:600}
.ss-panel .card-title i{color:var(--ss-b)}
.ss-pill{display:inline-block;padding:3px 12px;border-radius:30px;background:var(--ss-soft);color:var(--ss-b);font-weight:700;font-size:.8rem}
.ss-info{display:flex;flex-wrap:wrap;margin:0 -8px}
.ss-tile{flex:0 0 50%;max-width:50%;padding:8px}
.ss-tile.full{flex-basis:100%;max-width:100%}
.ss-tile>div{display:flex;align-items:center;height:100%;padding:12px 14px;border-radius:12px;border:1px solid var(--ss-line);border-left:4px solid var(--ss-b)}
.ss-tile i{font-size:1.1rem;color:var(--ss-b);width:30px;flex:0 0 30px}
.ss-tile span{display:block;font-size:.7rem;text-transform:uppercase;letter-spacing:.7px;color:var(--ss-muted)}
.ss-tile b{font-size:.95rem;word-break:break-word}
.ss-btn{border-radius:30px!important;padding:8px 22px;font-weight:600;border:0!important;box-shadow:0 3px 10px rgba(0,0,0,.25)}
.ss-btn.main{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}
.ss-empty{text-align:center;padding:18px;color:var(--ss-muted)}
.ss-empty i{font-size:2rem;display:block;margin-bottom:8px;color:var(--ss-b)}
@media(max-width:767px){.ss-tile{flex-basis:100%;max-width:100%}}

#tblscheduleday .badge{border-radius:30px;padding:5px 13px;font-size:.74rem;font-weight:700;letter-spacing:.3px}
.ss-sub{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:12px;background:var(--ss-soft);border-left:4px solid var(--ss-b);margin:18px 0 8px;font-weight:700}
.ss-sub small{color:var(--ss-muted);font-weight:600}
.ss-mini{width:100%;margin-bottom:6px}
.ss-mini th{font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--ss-muted);border-bottom:2px solid var(--ss-line)!important}
.ss-mini td,.ss-mini th{padding:.5rem .6rem;border-top:1px solid var(--ss-line)}
</style>