<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="stylesheet" href="style.css">
  
</head>
<body>
<div class="auth-wrapper">
  <div class="lang-bar">
    <button class="lang-btn" onclick="setLang('en')">EN</button>
    <button class="lang-btn" onclick="setLang('fr')">FR</button>
    <button class="lang-btn" onclick="setLang('ar')">ع</button>
    <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"><i class="ti ti-moon"></i></button>
  </div>
  <div class="auth-logo">
    <div class="icon"><i class="ti ti-headset"></i></div>
    <span class="name" data-i18n="appName">IT Helpdesk</span>
  </div>
  <div class="auth-card">
    <div class="auth-title" data-i18n="welcomeBack">Welcome back</div>
    <div class="auth-subtitle" data-i18n="loginSubtitle">Sign in to your IT Helpdesk account</div>
    <div id="alertBox" class="alert alert-error"></div>
    <div class="form-group">
      <label data-i18n="email">Email</label>
      <div class="input-wrap">
        <i class="ti ti-mail icon-left"></i>
        <input type="email" id="email" data-i18n-placeholder="email" placeholder="Email address"/>
      </div>
    </div>
    <div class="form-group">
      <label data-i18n="password">Password</label>
      <div class="input-wrap">
        <i class="ti ti-lock icon-left"></i>
        <input type="password" id="password" data-i18n-placeholder="password" placeholder="Password"/>
        <i class="ti ti-eye toggle-pass" onclick="togglePass()"></i>
      </div>
    </div>
    <button class="btn btn-primary" onclick="doLogin()">
      <i class="ti ti-login"></i> <span data-i18n="loginBtn">Sign in</span>
    </button>
    <div class="auth-footer">
      <span data-i18n="noAccount">Don't have an account?</span>
      <a href="pages/register.php" data-i18n="register"> Register</a>
    </div>
  </div>
</div>
<script src="js/app.js"></script>
<script>
  redirectIfAuth();

  function toggleTheme() {
    const next = getTheme() === 'dark' ? 'light' : 'dark';
    setTheme(next);
    document.getElementById('themeBtn').innerHTML = `<i class="ti ti-${next === 'dark' ? 'moon' : 'sun'}"></i>`;
  }

  function togglePass() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
  }

  function highlightLang() {
    const lang = getLang();
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.classList.toggle('active', btn.textContent.trim() === (lang === 'ar' ? 'ع' : lang.toUpperCase()));
    });
  }

  const _setLang = setLang;
  window.setLang = lang => { _setLang(lang); highlightLang(); };

  async function doLogin() {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    if (!email || !password) return showAlert('alertBox', t('fillRequired'));

    const { ok, data } = await apiFetch('/auth/login.php', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });

    if (!ok) return showAlert('alertBox', data.message || t('invalidCredentials'));

    localStorage.setItem('user', JSON.stringify(data.user));
    window.location.href = `pages/${landingPage(data.user.role)}`;
  }

  document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
  highlightLang();
  document.getElementById('themeBtn').innerHTML = `<i class="ti ti-${getTheme() === 'dark' ? 'moon' : 'sun'}"></i>`;
</script>
</body>
</html>
