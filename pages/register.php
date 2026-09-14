<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Register</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <style>
    body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
    .auth-wrapper{width:100%;max-width:480px}
    .auth-logo{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:32px}
    .auth-logo .icon{width:44px;height:44px;background:var(--accent);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center}
    .auth-logo .icon i{color:#fff;font-size:22px}
    .auth-logo .name{font-size:20px;font-weight:600;color:var(--text-primary)}
    .auth-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px}
    @media(max-width:480px){
      .auth-card{padding:20px}
      .row-2{grid-template-columns:1fr !important}
    }
    .auth-title{font-size:22px;font-weight:600;margin-bottom:4px}
    .auth-subtitle{font-size:13px;color:var(--text-secondary);margin-bottom:28px}
    .row-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .input-wrap{position:relative}
    .input-wrap i{position:absolute;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:18px;pointer-events:none}
    body:not([dir=rtl]) .input-wrap i{left:12px}
    body:not([dir=rtl]) .input-wrap input{padding-left:38px}
    body[dir=rtl] .input-wrap i{right:12px}
    body[dir=rtl] .input-wrap input{padding-right:38px}
    .auth-footer{text-align:center;margin-top:20px;font-size:13px;color:var(--text-secondary)}
    .lang-bar{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:24px}
    .lang-btn{padding:5px 12px;border-radius:20px;border:1px solid var(--border);background:var(--bg-primary);color:var(--text-secondary);font-size:12px;cursor:pointer;font-family:var(--font);transition:all .2s}
    .lang-btn.active,.lang-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="lang-bar">
    <button class="lang-btn" onclick="setLang('en')">EN</button>
    <button class="lang-btn" onclick="setLang('fr')">FR</button>
    <button class="lang-btn" onclick="setLang('ar')">ع</button>
  </div>
  <div class="auth-logo">
    <div class="icon"><i class="ti ti-headset"></i></div>
    <span class="name" data-i18n="appName">IT Helpdesk</span>
  </div>
  <div class="auth-card">
    <div class="auth-title" data-i18n="register">Create account</div>
    <div class="auth-subtitle" data-i18n="loginSubtitle">Sign in to your IT Helpdesk account</div>
    <div id="alertBox" class="alert alert-error"></div>
    <div id="successBox" class="alert alert-success"></div>
    <div class="row-2">
      <div class="form-group">
        <label data-i18n="firstName">First name</label>
        <div class="input-wrap"><i class="ti ti-user"></i><input type="text" id="first_name" placeholder="First name"/></div>
      </div>
      <div class="form-group">
        <label data-i18n="lastName">Last name</label>
        <div class="input-wrap"><i class="ti ti-user"></i><input type="text" id="last_name" placeholder="Last name"/></div>
      </div>
    </div>
    <div class="form-group">
      <label data-i18n="email">Email</label>
      <div class="input-wrap"><i class="ti ti-mail"></i><input type="email" id="email" placeholder="Email address"/></div>
    </div>
    <div class="row-2">
      <div class="form-group">
        <label data-i18n="phone">Phone</label>
        <div class="input-wrap"><i class="ti ti-phone"></i><input type="text" id="phone" placeholder="Phone"/></div>
      </div>
      <div class="form-group">
        <label data-i18n="department">Department</label>
        <div class="input-wrap"><i class="ti ti-building"></i><input type="text" id="department" placeholder="Department"/></div>
      </div>
    </div>
    <div class="form-group">
      <label data-i18n="password">Password</label>
      <div class="input-wrap"><i class="ti ti-lock"></i><input type="password" id="password" placeholder="Password"/></div>
    </div>
    <button class="btn btn-primary" onclick="doRegister()">
      <i class="ti ti-user-plus"></i> <span data-i18n="registerBtn">Create account</span>
    </button>
    <div class="auth-footer">
      <span data-i18n="hasAccount">Already have an account?</span>
      <a href="../index.php" data-i18n="login"> Sign in</a>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script>
  function highlightLang() {
    const lang = getLang();
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.classList.toggle('active', btn.textContent.trim() === (lang==='ar'?'ع':lang.toUpperCase()));
    });
  }
  const _setLang = setLang;
  window.setLang = lang => { _setLang(lang); highlightLang(); };

  async function doRegister() {
    const first_name = document.getElementById('first_name').value.trim();
    const last_name  = document.getElementById('last_name').value.trim();
    const email      = document.getElementById('email').value.trim();
    const phone      = document.getElementById('phone').value.trim();
    const department = document.getElementById('department').value.trim();
    const password   = document.getElementById('password').value;
    if (!first_name || !last_name || !email || !password) return showAlert('alertBox', t('fillRequired'));
    const { ok, data } = await apiFetch('/auth/register.php', {
      method: 'POST',
      body: JSON.stringify({ first_name, last_name, email, phone, department, password })
    });
    if (!ok) return showAlert('alertBox', data.message);
    showAlert('successBox', t('ticketCreated'), 'success');
    setTimeout(() => window.location.href = '../index.php', 1500);
  }
  highlightLang();
  /*echo*/
</script>
</body>
</html>
