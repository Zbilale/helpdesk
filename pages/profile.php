<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .profile-grid{display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start}
    .profile-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;text-align:center}
    .profile-avatar{width:80px;height:80px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:600;color:#fff;margin:0 auto 16px}
    .profile-name{font-size:18px;font-weight:600;margin-bottom:4px}
    .profile-role{font-size:13px;color:var(--text-secondary);text-transform:capitalize;margin-bottom:16px}
    .pstat{background:var(--bg-secondary);border-radius:var(--radius-md);padding:12px;margin-bottom:8px}
    .pstat-val{font-size:18px;font-weight:600;color:var(--accent);word-break:break-all}
    .pstat-label{font-size:12px;color:var(--text-muted);margin-top:2px}
    .form-section{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;margin-bottom:16px}
    .form-section-title{font-size:14px;font-weight:600;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px}
    .row-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    @media(max-width:900px){.profile-grid{grid-template-columns:1fr}.row-2{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="page-title" data-i18n="profile">Profile</div>
      <div class="profile-grid">
        <div>
          <div class="profile-card">
            <div class="profile-avatar" id="pAvatar">?</div>
            <div class="profile-name" id="pName">—</div>
            <div class="profile-role" id="pRole">—</div>
            <div class="pstat"><div class="pstat-val" id="pEmail">—</div><div class="pstat-label">Email</div></div>
            <div class="pstat"><div class="pstat-val" id="pDept">—</div><div class="pstat-label">Department</div></div>
          </div>
        </div>
        <div>
          <div id="alertBox" class="alert alert-error"></div>
          <div id="successBox" class="alert alert-success"></div>
          <div class="form-section">
            <div class="form-section-title"><i class="ti ti-user"></i> Personal Information</div>
            <div class="row-2">
              <div class="form-group"><label>First name</label><input type="text" id="firstName"/></div>
              <div class="form-group"><label>Last name</label><input type="text" id="lastName"/></div>
            </div>
            <div class="row-2">
              <div class="form-group"><label>Phone</label><input type="text" id="phone"/></div>
              <div class="form-group"><label>Department</label><input type="text" id="department"/></div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:8px">
              <button class="btn btn-primary" style="width:auto" onclick="saveProfile()"><i class="ti ti-device-floppy"></i> Save</button>
            </div>
          </div>
          <div class="form-section">
            <div class="form-section-title"><i class="ti ti-lock"></i> Change Password</div>
            <div class="form-group"><label>New password</label><input type="password" id="newPass" placeholder="Enter new password"/></div>
            <div class="form-group"><label>Confirm password</label><input type="password" id="confirmPass" placeholder="Confirm new password"/></div>
            <div style="display:flex;justify-content:flex-end;margin-top:8px">
              <button class="btn btn-primary" style="width:auto" onclick="changePassword()"><i class="ti ti-key"></i> Update Password</button>
            </div>
          </div>
          <div class="form-section">
            <div class="form-section-title"><i class="ti ti-settings"></i> Preferences</div>
            <div class="form-group"><label data-i18n="lang">Language</label>
              <select id="langSel" onchange="setLang(this.value)"><option value="en">English</option><option value="fr">Français</option><option value="ar">العربية</option></select>
            </div>
            <div class="form-group"><label>Theme</label>
              <select id="themeSel" onchange="setTheme(this.value)"><option value="dark">Dark mode</option><option value="light">Light mode</option></select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('profile');
  const user = getUser();

  async function loadProfile() {
    const { ok, data } = await apiFetch(`/users/index.php?id=${user.id}`);
    if (!ok) return;
    document.getElementById('pAvatar').textContent = `${data.first_name[0]}${data.last_name[0]}`;
    document.getElementById('pName').textContent   = `${data.first_name} ${data.last_name}`;
    document.getElementById('pRole').textContent   = data.role;
    document.getElementById('pEmail').textContent  = data.email;
    document.getElementById('pDept').textContent   = data.department || '—';
    document.getElementById('firstName').value   = data.first_name;
    document.getElementById('lastName').value    = data.last_name;
    document.getElementById('phone').value       = data.phone || '';
    document.getElementById('department').value  = data.department || '';
    document.getElementById('langSel').value     = getLang();
    document.getElementById('themeSel').value    = getTheme();
  }

  async function saveProfile() {
    const { ok, data } = await apiFetch(`/users/index.php?id=${user.id}`, {
      method: 'PUT',
      body: JSON.stringify({
        first_name: document.getElementById('firstName').value.trim(),
        last_name:  document.getElementById('lastName').value.trim(),
        phone:      document.getElementById('phone').value.trim(),
        department: document.getElementById('department').value.trim(),
      })
    });
    if (!ok) return showAlert('alertBox', data.message);
    showAlert('successBox', t('profileUpdated'), 'success');
    const updated = { ...user, first_name: document.getElementById('firstName').value.trim(), last_name: document.getElementById('lastName').value.trim() };
    localStorage.setItem('user', JSON.stringify(updated));
    loadProfile();
  }

  async function changePassword() {
    const newPass     = document.getElementById('newPass').value;
    const confirmPass = document.getElementById('confirmPass').value;
    if (!newPass || !confirmPass) return showAlert('alertBox', t('fillRequired'));
    if (newPass !== confirmPass) return showAlert('alertBox', 'Passwords do not match.');
    if (newPass.length < 6) return showAlert('alertBox', 'Min 6 characters.');
    const { ok, data } = await apiFetch(`/users/index.php?id=${user.id}`, { method: 'PUT', body: JSON.stringify({ password: newPass }) });
    if (!ok) return showAlert('alertBox', data.message);
    document.getElementById('newPass').value = ''; document.getElementById('confirmPass').value = '';
    showAlert('successBox', t('passwordChanged'), 'success');
  }

  loadProfile();
</script>
</body>
</html>
