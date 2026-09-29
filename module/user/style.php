<?php /* Shared look for the User module (included by list.php and myprofile_content.php). */ ?>
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
#tbluser thead th{background:var(--ss-soft);border-bottom:2px solid var(--ss-b);font-size:.78rem;letter-spacing:.6px;text-transform:uppercase;white-space:nowrap}
#tbluser tbody tr{transition:background .15s}
#tbluser tbody tr:hover{background:var(--ss-soft)!important}
#tbluser .btn-xs{border-radius:8px;margin:1px 1px;box-shadow:0 2px 6px rgba(0,0,0,.2)}
.ss-actions .btn{border-radius:30px!important;padding:8px 22px;font-weight:600;border:0;box-shadow:0 3px 10px rgba(0,0,0,.25);transition:transform .15s}
.ss-actions .btn:hover{transform:translateY(-2px)}
.ss-actions .btn-add{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}

/* name cell in the table */
.ss-user{display:flex;align-items:center}
.ss-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--ss-b);margin-right:10px;flex:0 0 34px;box-shadow:0 2px 6px rgba(0,0,0,.25)}
.ss-pill{display:inline-block;padding:3px 12px;border-radius:30px;background:var(--ss-soft);color:var(--ss-b);font-weight:700;font-size:.8rem}
#tbluser .badge{border-radius:30px;padding:4px 10px;font-size:.7rem;font-weight:700;margin-left:4px}

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
.ss-modal .modal-footer .btn-warning{background:linear-gradient(120deg,#d68910,#f5b041);color:#fff}

.ss-group{display:flex;align-items:center;font-size:.72rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ss-b);margin:6px 0 12px}
.ss-group:after{content:"";flex:1;height:1px;background:var(--ss-line);margin-left:12px}
.ss-group i{margin-right:8px}
.ss-modal label,.ss-panel label{font-weight:600;font-size:.82rem;margin-bottom:5px}
.ss-input{position:relative}
.ss-input>i.ico{position:absolute;left:13px;top:18px;transform:translateY(-50%);color:var(--ss-b);font-size:.9rem;pointer-events:none;z-index:2}
.ss-input>.form-control{padding-left:36px;height:calc(2.35rem + 2px);border-radius:10px;border:1px solid var(--ss-line);transition:border-color .15s,box-shadow .15s}
.ss-input>select.form-control{padding-left:36px}
.ss-input>.form-control:focus{border-color:var(--ss-b);box-shadow:0 0 0 .2rem rgba(192,57,43,.22)}
.ss-input>.form-control[readonly]{opacity:.7}
.ss-input .eye{position:absolute;right:6px;top:4px;width:34px;height:34px;border:0;background:transparent;color:var(--ss-muted);border-radius:50%;cursor:pointer;z-index:3}
.ss-input .eye:hover{color:var(--ss-b);background:var(--ss-soft)}
.ss-input.has-eye>.form-control{padding-right:44px}
.ss-hint{display:flex;align-items:center;padding:10px 14px;border-radius:10px;background:var(--ss-soft);font-size:.82rem;margin-bottom:14px}
.ss-hint i{color:var(--ss-b);margin-right:10px}

.ss-who{display:flex;align-items:center;padding:12px 16px;border-radius:12px;background:var(--ss-soft);border-left:4px solid var(--ss-b);margin-bottom:18px}
.ss-who>i{font-size:1.5rem;color:var(--ss-b);margin-right:14px}
.ss-who small{display:block;color:var(--ss-muted);text-transform:uppercase;letter-spacing:.6px;font-size:.68rem}
.ss-who strong{font-size:.98rem}

/* photo picker */
.ss-photo{text-align:center;padding:14px;border:2px dashed var(--ss-line);border-radius:14px;margin-bottom:20px}
.ss-photo img{width:96px;height:96px;border-radius:50%;object-fit:cover;border:4px solid var(--ss-b);box-shadow:0 4px 14px rgba(0,0,0,.3);margin-bottom:10px}
.ss-photo label{display:block}
.ss-photo .form-control-file{max-width:100%;font-size:.8rem}

/* status switch row */
.ss-switch{padding:10px 14px;border-radius:12px;border:1px solid var(--ss-line);border-left:4px solid var(--ss-b);margin-top:2px}

/* ---------- My Profile ---------- */
.ss-hero{border:0;border-radius:16px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-hero .ss-cover{background:linear-gradient(135deg,var(--ss-a),var(--ss-b));padding:30px 20px 56px;text-align:center;color:#fff}
.ss-hero .ss-cover h3{font-size:1.25rem;font-weight:700;margin:12px 0 2px}
.ss-hero .ss-cover small{opacity:.85;letter-spacing:1px;text-transform:uppercase}
.ss-hero .ss-cover img{width:120px;height:120px;object-fit:cover;border-radius:50%;border:4px solid rgba(255,255,255,.85);box-shadow:0 6px 18px rgba(0,0,0,.35)}
.ss-hero .ss-body{margin-top:-28px;border-radius:22px 22px 0 0;padding:18px 20px;background:inherit;position:relative}
.ss-stat{display:flex;align-items:center;padding:10px 0;border-bottom:1px dashed var(--ss-line)}
.ss-stat:last-of-type{border:0}
.ss-stat i{width:36px;height:36px;border-radius:10px;background:var(--ss-soft);color:var(--ss-b);display:inline-flex;align-items:center;justify-content:center;margin-right:12px;flex:0 0 36px}
.ss-stat span{color:var(--ss-muted);font-size:.72rem;display:block;text-transform:uppercase;letter-spacing:.5px}
.ss-stat b{font-size:.95rem;word-break:break-word}
.ss-panel{border:0;border-radius:14px;box-shadow:0 6px 22px rgba(0,0,0,.15);overflow:hidden;margin-bottom:20px}
.ss-panel .card-header{border-bottom:2px solid var(--ss-b);background:var(--ss-soft)}
.ss-panel .card-title{font-weight:600}
.ss-panel .card-title i{color:var(--ss-b);margin-right:6px}
.ss-panel .card-body{padding:20px 22px}
.ss-panel .card-footer{background:transparent;border-top:1px solid var(--ss-line);padding:14px 22px}
.ss-btn{border-radius:30px!important;padding:8px 22px;font-weight:600;border:0!important;box-shadow:0 3px 10px rgba(0,0,0,.25)}
.ss-btn.main{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff}
.ss-btn.warn{background:linear-gradient(120deg,#d68910,#f5b041);color:#fff}
.ss-btn:hover{color:#fff;transform:translateY(-1px)}
</style>