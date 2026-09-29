<?php



require_once("include/initialize.php");
  if(isset($_SESSION['UID'])){
    redirect("index.php");
    header("Location: index.php");
  }
  // The intro video plays only when arriving from intro.php (?intro=1), never after a login attempt.
  $playIntro = isset($_GET['intro']) && !isset($_POST['btnLogin']);
 ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SOL Solutions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#8b0000">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo  WEB_ROOT;?>plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="<?php echo  WEB_ROOT;?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo  WEB_ROOT;?>dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700,800" rel="stylesheet">

  <style>
    :root{--a:#8b0000;--b:#c0392b;--deep:#4a0000}
    @keyframes rise{from{opacity:0;transform:translateY(26px) scale(.97)}to{opacity:1;transform:none}}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-26px)}}
    @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}

    html,body{height:100%}
    body.login-page{
      font-family:'Source Sans Pro',-apple-system,'Segoe UI',sans-serif;
      background:linear-gradient(135deg,var(--deep) 0%,var(--a) 45%,var(--b) 100%);
      display:flex;align-items:center;justify-content:center;
      overflow:hidden;position:relative;min-height:100vh}

    /* floating background circles */
    .bg-orb{position:absolute;border-radius:50%;background:rgba(255,255,255,.07);animation:float 9s ease-in-out infinite}
    .bg-orb.o1{width:340px;height:340px;left:-90px;top:-70px}
    .bg-orb.o2{width:220px;height:220px;right:8%;top:12%;animation-delay:-3s}
    .bg-orb.o3{width:420px;height:420px;right:-140px;bottom:-140px;animation-delay:-5s}
    .bg-orb.o4{width:120px;height:120px;left:12%;bottom:10%;animation-delay:-1.5s}

    .login-box{width:400px;max-width:92vw;margin:0;position:relative;z-index:2;animation:rise .6s cubic-bezier(.2,.8,.2,1)}
    .login-box .card{border:0;border-radius:24px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.5)}

    .login-top{background:linear-gradient(135deg,var(--deep),var(--a) 55%,var(--b));padding:30px 20px 62px;text-align:center;color:#fff;position:relative}
    .login-top h1{font-size:1.5rem;font-weight:800;letter-spacing:1.5px;margin:12px 0 2px}
    .login-top small{opacity:.85;letter-spacing:1px;text-transform:uppercase;font-size:.72rem}
    .login-logo-wrap{width:96px;height:96px;margin:0 auto;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;
      box-shadow:0 8px 24px rgba(0,0,0,.4);border:4px solid rgba(255,255,255,.55)}
    .login-logo-wrap img{max-width:78%;max-height:78%;object-fit:contain}

    .login-card-body{border-radius:26px 26px 0 0;margin-top:-30px;padding:30px 32px 26px;background:#fff;position:relative}
    .login-box-msg{padding:0;margin:0 0 22px;text-align:center;color:#6b7280;font-size:.98rem}

    .field{position:relative;margin-bottom:16px}
    .field>i.lead-ico{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--b);z-index:3;pointer-events:none}
    .field .form-control{height:50px;padding-left:46px;border-radius:14px;border:2px solid #eceef2;background:#f7f8fa;font-size:1rem;transition:all .2s}
    .field .form-control:focus{background:#fff;border-color:var(--b);box-shadow:0 0 0 .22rem rgba(192,57,43,.18)}
    .field .toggle-pass{position:absolute;right:8px;top:50%;transform:translateY(-50%);z-index:3;border:0;background:transparent;color:#9aa0aa;
      width:38px;height:38px;border-radius:50%;cursor:pointer;transition:all .15s}
    .field .toggle-pass:hover{color:var(--b);background:rgba(192,57,43,.1)}
    .field.has-toggle .form-control{padding-right:50px}

    .btn-login{height:52px;border:0;border-radius:14px;font-weight:700;font-size:1.05rem;letter-spacing:.5px;color:#fff;
      background:linear-gradient(120deg,var(--a),var(--b));box-shadow:0 8px 22px rgba(192,57,43,.45);transition:transform .15s,box-shadow .15s}
    .btn-login:hover{color:#fff;transform:translateY(-2px);box-shadow:0 12px 28px rgba(192,57,43,.6)}
    .btn-login:active{transform:translateY(0)}
    .btn-login i{margin-right:8px}

    .divider{display:flex;align-items:center;margin:22px 0 14px;color:#a0a5ae;font-size:.78rem;text-transform:uppercase;letter-spacing:1px}
    .divider:before,.divider:after{content:"";flex:1;height:1px;background:#e6e8ec}
    .divider span{padding:0 12px}
    .portal-link{display:flex;align-items:center;justify-content:center;padding:12px;border-radius:14px;border:2px solid var(--b);
      color:var(--b);font-weight:700;transition:all .2s}
    .portal-link:hover{background:var(--b);color:#fff;text-decoration:none}
    .portal-link i{margin-right:8px}
    .foot{text-align:center;color:#a0a5ae;font-size:.76rem;margin-top:18px}
    .shake{animation:shake .35s}

    /* ---- intro video -> login transition ---- */
    #introSplash{position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,var(--deep) 0%,var(--a) 45%,var(--b) 100%);transition:opacity .8s ease}
    #introSplash video{width:100%;height:100%;object-fit:cover}
    #introSplash.hide{opacity:0;pointer-events:none}
    #introSkip{position:absolute;bottom:30px;right:30px;padding:10px 22px;border-radius:30px;border:1px solid rgba(255,255,255,.4);
      background:rgba(255,255,255,.15);color:#fff;font-weight:600;font-size:14px;cursor:pointer;backdrop-filter:blur(2px)}
    #introSkip:hover{background:rgba(255,255,255,.3)}
    body.intro-on .login-box{animation:none;opacity:0}      /* card waits until the video is done, then rises in */
  </style>
</head>
<body class="hold-transition login-page<?php echo $playIntro ? ' intro-on' : ''; ?>">

<?php if ($playIntro): ?>
<div id="introSplash">
  <video id="introVideo" autoplay muted playsinline preload="auto">
    <source src="<?php echo WEB_ROOT; ?>intro.mp4" type="video/mp4">
  </video>
  <button type="button" id="introSkip">Skip Intro &raquo;</button>
</div>
<?php endif; ?>

<div class="bg-orb o1"></div>
<div class="bg-orb o2"></div>
<div class="bg-orb o3"></div>
<div class="bg-orb o4"></div>

<div class="login-box">
  <div class="card">

    <div class="login-top">
      <div class="login-logo-wrap"><img src="csr-css.png" alt="Logo"></div>
      <h1>SOL SOLUTIONS</h1>
      <small>School Management System</small>
    </div>

    <div class="login-card-body">
      <p class="login-box-msg">Welcome back! Login to start your session.</p>

      <form action="#" method="post" id="loginForm" autocomplete="on">

        <div class="field">
          <i class="fas fa-user lead-ico"></i>
          <input type="text" class="form-control" name="username" placeholder="Username" autocomplete="username" autofocus>
        </div>

        <div class="field has-toggle">
          <i class="fas fa-lock lead-ico"></i>
          <input type="password" class="form-control" name="userpass" id="userpass" placeholder="Password" autocomplete="current-password">
          <button type="button" class="toggle-pass" id="togglePass" title="Show / hide password" tabindex="-1">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <button type="submit" name="btnLogin" class="btn btn-login btn-block">
          <i class="fas fa-sign-in-alt"></i>Log in
        </button>

      </form>

      <!-- Student Portal link -->
      <div class="divider"><span>or</span></div>
      <a class="portal-link" href="<?php echo WEB_ROOT; ?>portal/login.php">
        <i class="fas fa-user-graduate"></i> Go to the Student Portal
      </a>

      <div class="foot">&copy; <?php echo date('Y'); ?> SOLITARIO. All rights reserved.</div>
    </div>

  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="<?php echo  WEB_ROOT;?>plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?php echo  WEB_ROOT;?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo  WEB_ROOT;?>dist/js/adminlte.min.js"></script>

<script>
  /* Show / hide password */
  document.getElementById('togglePass').addEventListener('click', function () {
    var p = document.getElementById('userpass');
    var show = p.type === 'password';
    p.type = show ? 'text' : 'password';
    this.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
  });

  /* Gentle shake if the form is submitted empty (server still validates) */
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    var u = this.username.value.trim(), p = this.userpass.value.trim();
    if (!u || !p) {
      e.preventDefault();
      var box = document.querySelector('.login-box .card');
      box.classList.remove('shake'); void box.offsetWidth; box.classList.add('shake');
      (!u ? this.username : this.userpass).focus();
    }
  });
</script>

<?php if ($playIntro): ?>
<script>
  /* Intro video -> login page: the video ends on the login background, so it simply fades away while the card rises in. */
  (function () {
    var splash = document.getElementById('introSplash'), vid = document.getElementById('introVideo'), done = false;
    function finish() {
      if (done) return; done = true;
      document.body.classList.remove('intro-on');          // login card animates in
      splash.classList.add('hide');
      setTimeout(function () { splash.parentNode && splash.parentNode.removeChild(splash); }, 900);
      if (window.history && history.replaceState) history.replaceState(null, '', location.pathname);
      var u = document.querySelector('input[name=username]'); if (u) setTimeout(function () { u.focus(); }, 500);
    }
    vid.addEventListener('ended', finish);
    vid.addEventListener('error', finish);                  // video missing/unsupported -> go straight to the login
    document.getElementById('introSkip').addEventListener('click', finish);
    setTimeout(finish, 7000);                               // safety net if playback is blocked
    var pr = vid.play(); if (pr && pr.catch) pr.catch(finish);
  })();
</script>
<?php endif; ?>

</body>
</html>
<?php 

if(isset($_POST['btnLogin'])){

 
  $email = trim($_POST['username']);
  $upass  = trim($_POST['userpass']);
  $h_upass = sha1($upass);
  

     if ($email == '' OR $upass == '') {

        message("Invalid Username and Password!", "error");
        redirect("login.php");
           
      } else {  
    //it creates a new objects of member
      $user = new User();
      //make use of the static function, and we passed to parameters
      
        $res = $user::AuthenticateUser($email, $h_upass);

       
          /*The old version only sent Administrator / Doctor / Staff to the
           dashboard. Any other user type was authenticated but then just
           sat on the login page with nothing happening, and a failed login
           was bounced to index.php which bounced straight back here.
           Any active account now lands on the dashboard, and a bad login
           stays on the login page. */
        if ($res==true) { 
            ?>
               <script language="javascript">
                  window.location.href = "index.php";
              </script>
              <?php
        }else{
          echo "<script type='text/javascript'>alert('Invalid username or password.'); window.location.href = 'login.php';</script>";
       }
      }
 }


 ?>