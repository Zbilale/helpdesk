<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — New Ticket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .form-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;max-width:700px}
    .row-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .priority-pills{display:flex;gap:8px;flex-wrap:wrap}
    .priority-pill{padding:7px 16px;border-radius:20px;border:1px solid var(--border);background:var(--bg-tertiary);color:var(--text-secondary);font-size:13px;font-weight:500;cursor:pointer;transition:all .15s}
    .priority-pill.low.active{background:var(--success-light);color:var(--success);border-color:var(--success)}
    .priority-pill.medium.active{background:var(--warning-light);color:var(--warning);border-color:var(--warning)}
    .priority-pill.high.active{background:var(--warning-light);color:var(--warning);border-color:var(--warning)}
    .priority-pill.critical.active{background:var(--danger-light);color:var(--danger);border-color:var(--danger)}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <button class="btn-icon" onclick="history.back()"><i class="ti ti-arrow-left"></i></button>
        <div><div class="page-title" data-i18n="newTicket">New Ticket</div></div>
      </div>
      <div class="form-card">
        <div id="alertBox" class="alert alert-error"></div>
        <div id="successBox" class="alert alert-success"></div>
        <div class="form-group">
          <label data-i18n="ticketTitle">Title <span style="color:var(--danger)">*</span></label>
          <input type="text" id="title" placeholder="Describe your issue briefly"/>
        </div>
        <div class="row-2">
          <div class="form-group">
            <label data-i18n="postNumber">Post number <span style="color:var(--danger)">*</span></label>
            <input type="text" id="post_number" placeholder="e.g. A-204"/>
          </div>
          <div class="form-group">
            <label data-i18n="category">Category <span style="color:var(--danger)">*</span></label>
            <select id="category" onchange="loadSubcategories()">
              <option value="" data-i18n="selectCategory">Select category</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label data-i18n="subcategory">Device type</label>
          <select id="subcategory"><option value="" data-i18n="selectSubcategory">Select device type</option></select>
        </div>
        <div class="form-group">
          <label data-i18n="description">Description <span style="color:var(--danger)">*</span></label>
          <textarea id="description" rows="5" placeholder="Describe your problem in detail..."></textarea>
        </div>
        <div class="form-group">
          <label data-i18n="priority">Priority <span style="color:var(--danger)">*</span></label>
          <div class="priority-pills" id="priorityPills"></div>
          <input type="hidden" id="priority_id"/>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px">
          <button class="btn btn-secondary" style="width:auto" onclick="history.back()" data-i18n="cancel">Cancel</button>
          <button class="btn btn-primary" style="width:auto" onclick="submitTicket()">
            <i class="ti ti-send"></i> <span data-i18n="submit">Submit ticket</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('newTicket');

  async function loadFormData() {
    const [catRes, priRes] = await Promise.all([
      apiFetch('/categories/index.php?type=categories'),
      apiFetch('/categories/index.php?type=priorities'),
    ]);
    if (catRes.ok) {
      const sel = document.getElementById('category');
      catRes.data.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name; sel.appendChild(o); });
    }
    if (priRes.ok) {
      const container = document.getElementById('priorityPills');
      priRes.data.forEach(p => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `priority-pill ${p.name.toLowerCase()}`;
        btn.textContent = p.name;
        btn.onclick = () => {
          document.getElementById('priority_id').value = p.id;
          document.querySelectorAll('.priority-pill').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
        };
        container.appendChild(btn);
      });
    }
  }

  async function loadSubcategories() {
    const category_id = document.getElementById('category').value;
    const sub = document.getElementById('subcategory');
    sub.innerHTML = `<option value="">${t('selectSubcategory')}</option>`;
    if (!category_id) return;
    const { ok, data } = await apiFetch(`/categories/index.php?type=subcategories&category_id=${category_id}`);
    if (ok) data.forEach(s => { const o = document.createElement('option'); o.value = s.id; o.textContent = s.name; sub.appendChild(o); });
  }

  async function submitTicket() {
    const title         = document.getElementById('title').value.trim();
    const post_number   = document.getElementById('post_number').value.trim();
    const description   = document.getElementById('description').value.trim();
    const category_id   = document.getElementById('category').value;
    const subcategory_id= document.getElementById('subcategory').value;
    const priority_id   = document.getElementById('priority_id').value;
    if (!title || !description || !category_id || !priority_id || !post_number) return showAlert('alertBox', t('fillRequired'));
    const { ok, data } = await apiFetch('/tickets/create.php', {
      method: 'POST',
      body: JSON.stringify({ title, description, post_number, category_id: parseInt(category_id), subcategory_id: subcategory_id ? parseInt(subcategory_id) : null, priority_id: parseInt(priority_id) })
    });
    if (!ok) return showAlert('alertBox', data.message);
    showAlert('successBox', t('ticketCreated'), 'success');
    setTimeout(() => window.location.href = 'tickets.php', 1500);
  }

  loadFormData();
</script>
</body>
</html>
