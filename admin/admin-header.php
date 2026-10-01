<?php
// Admin Header Component - shared CSS and JS for all admin pages
// Include this after sidebar.php
?>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --bg:#08090e;--bg2:#0f1117;--bg3:#191c28;--bg4:#21253a;
    --border:rgba(255,255,255,0.06);
    --purple:#7c5cfc;--purple-lt:#a78bfa;--purple-glow:rgba(124,92,252,.35);
    --teal:#14b8a6;--amber:#f59e0b;--red:#f43f5e;--green:#10b981;--blue:#3b82f6;--cyan:#06b6d4;
    --text:#f1f5f9;--muted:#64748b;
    --sidebar-w:250px;--radius:16px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter','Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;overflow-x:hidden;}
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-thumb{background:var(--bg3);border-radius:4px;}

/* Sidebar */
.sidebar{width:var(--sidebar-w);background:var(--bg2);height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;border-right:1px solid var(--border);z-index:100;}
.sidebar-logo{padding:24px 22px 22px;font-size:19px;font-weight:800;letter-spacing:-.5px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--border);}
.sidebar-logo span{color:var(--purple-lt);font-size:24px;}
.sidebar-nav{flex:1;padding:18px 14px;overflow-y:auto;}
.nav-label{font-size:10px;font-weight:700;letter-spacing:.1em;color:var(--muted);text-transform:uppercase;padding:12px 10px 6px;}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:12px;color:var(--muted);text-decoration:none;font-size:13.5px;font-weight:500;transition:all .25s cubic-bezier(.4,0,.2,1);margin-bottom:2px;position:relative;overflow:hidden;}
.nav-item::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(124,92,252,.15),rgba(124,92,252,.05));opacity:0;transition:opacity .25s;border-radius:12px;}
.nav-item:hover{color:var(--text);transform:translateX(4px);}
.nav-item:hover::before{opacity:1;}
.nav-item.active{background:linear-gradient(135deg,rgba(124,92,252,.2),rgba(124,92,252,.08));color:var(--purple-lt);box-shadow:0 0 20px rgba(124,92,252,.1);}
.nav-item.active i{color:var(--purple);}
.nav-item i{font-size:18px;flex-shrink:0;position:relative;z-index:1;}
.nav-item.danger{color:#f43f5e88;}
.nav-item.danger:hover{color:var(--red);transform:translateX(4px);}
.badge-nav{margin-left:auto;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;position:relative;z-index:1;}
.sidebar-footer{padding:16px;border-top:1px solid var(--border);}
.admin-card{display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg3);border-radius:12px;transition:all .3s;cursor:default;}
.admin-card:hover{background:var(--bg4);transform:scale(1.02);}
.admin-card i{font-size:36px;color:var(--purple-lt);}
.admin-card .info{flex:1;min-width:0;}
.admin-card .info strong{display:block;font-size:13px;font-weight:600;}
.admin-card .info small{font-size:11px;color:var(--muted);}
.admin-card .dot{width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;box-shadow:0 0 8px var(--green);animation:pulse-dot 2s infinite;}
@keyframes pulse-dot{0%,100%{box-shadow:0 0 6px var(--green);}50%{box-shadow:0 0 14px var(--green),0 0 24px rgba(16,185,129,.3);}}

/* Main */
.main{margin-left:var(--sidebar-w);flex:1;padding:28px 30px 40px;min-width:0;position:relative;}

/* Topbar */
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:26px;animation:fadeSlideDown .6s cubic-bezier(.4,0,.2,1) both;}
.topbar h1{font-size:24px;font-weight:800;letter-spacing:-.5px;background:linear-gradient(135deg,var(--text),var(--purple-lt));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.topbar p{font-size:13px;color:var(--muted);margin-top:3px;}
.topbar-right{display:flex;align-items:center;gap:12px;}

/* Animations */
@keyframes fadeSlideDown{from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeSlideUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeScaleIn{from{opacity:0;transform:scale(.92);}to{opacity:1;transform:scale(1);}}

/* Cards */
.admin-content-card{
    background:linear-gradient(145deg,var(--bg2),var(--bg3));
    border-radius:var(--radius);border:1px solid var(--border);
    padding:24px;transition:all .35s cubic-bezier(.4,0,.2,1);
    animation:fadeSlideUp .6s cubic-bezier(.4,0,.2,1) both;
}
.admin-content-card:hover{border-color:rgba(124,92,252,.2);}
.card-title{font-size:16px;font-weight:700;margin-bottom:4px;letter-spacing:-.02em;}
.card-sub{font-size:12px;color:var(--muted);margin-bottom:18px;font-weight:500;}

/* Tables */
.admin-table{width:100%;border-collapse:collapse;}
.admin-table th{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);padding:12px 14px;text-align:left;border-bottom:1px solid var(--border);background:rgba(0,0,0,.2);}
.admin-table td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle;transition:all .2s;}
.admin-table tr:last-child td{border-bottom:none;}
.admin-table tr{transition:all .25s;}
.admin-table tbody tr:hover td{background:rgba(124,92,252,.04);}

/* Status pills */
.pill{font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;display:inline-block;}
.pill-pending{background:rgba(245,158,11,.15);color:#fbbf24;}
.pill-processing{background:rgba(124,92,252,.15);color:var(--purple-lt);}
.pill-shipped{background:rgba(20,184,166,.15);color:#2dd4bf;}
.pill-completed{background:rgba(16,185,129,.15);color:#34d399;}
.pill-cancelled{background:rgba(244,63,94,.15);color:#fb7185;}
.pill-admin{background:rgba(244,63,94,.15);color:#fb7185;}
.pill-customer{background:rgba(124,92,252,.15);color:var(--purple-lt);}
.pill-in-stock{background:rgba(16,185,129,.15);color:#34d399;}
.pill-low-stock{background:rgba(245,158,11,.15);color:#fbbf24;}
.pill-out{background:rgba(244,63,94,.15);color:#fb7185;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .25s cubic-bezier(.4,0,.2,1);text-decoration:none;font-family:inherit;}
.btn:hover{transform:translateY(-2px);}
.btn-primary{background:linear-gradient(135deg,var(--purple),#6340e0);color:#fff;box-shadow:0 4px 15px rgba(124,92,252,.25);}
.btn-primary:hover{box-shadow:0 6px 25px rgba(124,92,252,.4);}
.btn-success{background:linear-gradient(135deg,var(--green),#059669);color:#fff;box-shadow:0 4px 15px rgba(16,185,129,.25);}
.btn-danger{background:linear-gradient(135deg,var(--red),#dc2626);color:#fff;box-shadow:0 4px 15px rgba(244,63,94,.25);}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--muted);}
.btn-outline:hover{border-color:var(--purple);color:var(--purple-lt);}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:8px;}
.btn-xs{padding:4px 8px;font-size:11px;border-radius:6px;}

/* Forms */
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;letter-spacing:.03em;}
.form-control{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:13px;font-family:inherit;transition:all .25s;}
.form-control:focus{outline:none;border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,92,252,.15);}
select.form-control{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%2364748b'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;}
textarea.form-control{resize:vertical;min-height:80px;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:28px;width:100%;max-width:500px;max-height:85vh;overflow-y:auto;animation:fadeScaleIn .3s cubic-bezier(.4,0,.2,1) both;box-shadow:0 20px 60px rgba(0,0,0,.5);}
.modal h2{font-size:18px;font-weight:700;margin-bottom:4px;}
.modal .modal-sub{font-size:12px;color:var(--muted);margin-bottom:20px;}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px;}

/* Search & Filter bar */
.toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;animation:fadeSlideDown .5s cubic-bezier(.4,0,.2,1) both;animation-delay:.2s;}
.search-box{flex:1;min-width:200px;position:relative;}
.search-box input{width:100%;padding:10px 14px 10px 38px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:13px;font-family:inherit;transition:all .25s;}
.search-box input:focus{outline:none;border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,92,252,.15);}
.search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:16px;}

/* Empty state */
.empty-state{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-state i{font-size:50px;margin-bottom:16px;color:var(--bg4);}
.empty-state h3{font-size:16px;margin-bottom:8px;color:var(--text);}

/* Toast notification */
.toast{position:fixed;top:20px;right:20px;padding:14px 20px;border-radius:12px;font-size:13px;font-weight:600;z-index:9999;animation:fadeSlideDown .4s,fadeSlideDown .4s 2.6s reverse both;pointer-events:none;}
.toast-success{background:rgba(16,185,129,.95);color:#fff;}
.toast-error{background:rgba(244,63,94,.95);color:#fff;}

/* Image thumbnail */
.img-thumb{width:40px;height:40px;border-radius:10px;object-fit:cover;background:var(--bg);border:1px solid var(--border);}

/* Responsive */
@media(max-width:768px){
    .sidebar{display:none;}
    .main{margin-left:0;padding:20px;}
    .toolbar{flex-direction:column;}
}
</style>
