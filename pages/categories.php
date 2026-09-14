<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Categories</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start}
    .section-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden}
    .section-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);font-size:14px;font-weight:600}
    .cat-item{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border);font-size:13px}
    .cat-item:last-child{border-bottom:none}
    .cat-item:hover{background:var(--bg-hover)}
    @media(max-width:768px){.two-col{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="page-title" data-i18n="categories">Categories</div>
      <div id="successBox" class="alert alert-success"></div>
      <div class="two-col">
        <div class="section-card">
          <div class="section-header"><span>Categories</span><button class="btn btn-primary btn-sm" onclick="openModal('newCatModal')"><i class="ti ti-plus"></i> Add</button></div>
          <div id="catList"><div style="padding:20px;color:var(--text-muted);font-size:13px">Loading...</div></div>
        </div>
        <div class="section-card">
          <div class="section-header"><span>Priorities</span></div>
          <div id="priList"><div style="padding:20px;color:var(--text-muted);font-size:13px">Loading...</div></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-overlay" id="newCatModal">
  <div class="modal">
    <div class="modal-header"><span>New Category</span><button class="btn-icon" onclick="closeModal('newCatModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <div id="catAlert" class="alert alert-error"></div>
      <div class="form-group"><label>Name</label><input type="text" id="newCatName" placeholder="e.g. Hardware"/></div>
      <div class="form-group"><label>Description</label><input type="text" id="newCatDesc" placeholder="Short description"/></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('newCatModal')">Cancel</button>
      <button class="btn btn-primary" onclick="createCat()">Save</button>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('categories');

  async function load() {
    const [catRes, priRes] = await Promise.all([
      apiFetch('/categories/index.php?type=categories'),
      apiFetch('/categories/index.php?type=priorities'),
    ]);
    if (catRes.ok) {
      document.getElementById('catList').innerHTML = catRes.data.length
        ? catRes.data.map(c => `<div class="cat-item"><div><div style="font-weight:500">${c.name}</div><div style="font-size:12px;color:var(--text-muted)">${c.description||'—'}</div></div><button class="btn-icon" onclick="deleteCat(${c.id})"><i class="ti ti-trash" style="color:var(--danger)"></i></button></div>`).join('')
        : '<div style="padding:20px;color:var(--text-muted);font-size:13px">No categories yet.</div>';
    }
    if (priRes.ok) {
      document.getElementById('priList').innerHTML = priRes.data.map(p => `
        <div class="cat-item">
          <div style="display:flex;align-items:center;gap:10px"><div style="width:12px;height:12px;border-radius:50%;background:${p.color}"></div><div><div style="font-weight:500">${p.name}</div><div style="font-size:12px;color:var(--text-muted)">Level ${p.level}</div></div></div>
        </div>`).join('');
    }
  }

  async function createCat() {
    const name = document.getElementById('newCatName').value.trim();
    const description = document.getElementById('newCatDesc').value.trim();
    if (!name) return showAlert('catAlert', t('fillRequired'));
    const { ok, data } = await apiFetch('/categories/index.php?type=categories', { method:'POST', body: JSON.stringify({ name, description }) });
    if (!ok) return showAlert('catAlert', data.message);
    closeModal('newCatModal');
    showAlert('successBox', 'Category created.', 'success');
    document.getElementById('newCatName').value = '';
    load();
  }

  async function deleteCat(id) {
    if (!confirm(t('confirm'))) return;
    const { ok } = await apiFetch(`/categories/index.php?type=categories&id=${id}`, { method:'DELETE' });
    if (ok) { showAlert('successBox', 'Deleted.', 'success'); load(); }
  }

  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  load();
</script>
</body>
</html>
