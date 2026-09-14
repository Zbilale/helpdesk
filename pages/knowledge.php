<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Knowledge Base</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .kb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
    .kb-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;cursor:pointer;transition:all .2s}
    .kb-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:var(--shadow)}
    .kb-card.draft{opacity:.6;border-style:dashed}
    .kb-title{font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:8px}
    .kb-meta{display:flex;gap:10px;font-size:12px;color:var(--text-muted);margin-bottom:8px}
    .kb-preview{font-size:13px;color:var(--text-secondary);line-height:1.6;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
        <div><div class="page-title" data-i18n="knowledge">Knowledge Base</div><div class="page-subtitle" id="articleCount"></div></div>
        <div style="display:flex;gap:8px;align-items:center">
          <input type="text" id="searchInput" placeholder="Search articles..." style="width:220px;padding:8px 12px;font-size:13px" oninput="filterArticles()"/>
          <div id="newBtn" style="display:none"><button class="btn btn-primary btn-sm" onclick="openNewModal()"><i class="ti ti-plus"></i> New</button></div>
        </div>
      </div>
      <div id="successBox" class="alert alert-success"></div>
      <div class="kb-grid" id="kbGrid"><p style="color:var(--text-muted)">Loading...</p></div>
    </div>
  </div>
</div>
<div class="modal-overlay" id="viewModal">
  <div class="modal" style="max-width:680px">
    <div class="modal-header">
      <span id="viewTitle" style="font-size:17px"></span>
      <div style="display:flex;gap:8px"><div id="editArticleBtn" style="display:none"><button class="btn-icon" onclick="openEditModal()"><i class="ti ti-edit"></i></button></div><button class="btn-icon" onclick="closeModal('viewModal')"><i class="ti ti-x"></i></button></div>
    </div>
    <div class="modal-body">
      <div style="display:flex;gap:12px;margin-bottom:16px;font-size:12px;color:var(--text-muted)"><span id="vCat"></span><span id="vAuthor"></span><span><i class="ti ti-eye" style="font-size:13px"></i> <span id="vViews"></span></span></div>
      <div style="font-size:14px;color:var(--text-primary);line-height:1.9;white-space:pre-wrap" id="vContent"></div>
    </div>
  </div>
</div>
<div class="modal-overlay" id="editModal">
  <div class="modal" style="max-width:600px">
    <div class="modal-header"><span id="editModalTitle">New Article</span><button class="btn-icon" onclick="closeModal('editModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <input type="hidden" id="editId"/>
      <div id="editAlert" class="alert alert-error"></div>
      <div class="form-group"><label>Title</label><input type="text" id="editTitle" placeholder="Article title"/></div>
      <div class="form-group"><label>Category</label><select id="editCat"><option value="">Select category</option></select></div>
      <div class="form-group"><label>Content</label><textarea id="editContent" style="min-height:200px" placeholder="Write the article content..."></textarea></div>
      <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" id="editPublished" style="width:auto"/> Publish immediately</label></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveArticle()">Save</button>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('knowledge');
  const user = getUser();
  let allArticles = [], currentArticle = null;
  if (user.role !== 'user') document.getElementById('newBtn').style.display = 'block';

  async function loadArticles() {
    const { ok, data } = await apiFetch('/knowledge/index.php');
    if (!ok) return;
    allArticles = data;
    document.getElementById('articleCount').textContent = `${data.length} article(s)`;
    renderArticles(data);
  }

  function renderArticles(articles) {
    document.getElementById('kbGrid').innerHTML = articles.length
      ? articles.map(a => `
        <div class="kb-card ${!a.is_published?'draft':''}" onclick="viewArticle(${a.id})">
          <div class="kb-title">${a.title}</div>
          <div class="kb-meta"><span><i class="ti ti-category" style="font-size:13px"></i> ${a.category||'General'}</span><span><i class="ti ti-eye" style="font-size:13px"></i> ${a.views}</span>${!a.is_published?'<span style="color:var(--warning)">Draft</span>':''}</div>
          <div class="kb-preview">${a.content||''}</div>
        </div>`).join('')
      : `<div class="empty-state" style="grid-column:1/-1"><i class="ti ti-book-off"></i><p>No articles found.</p></div>`;
  }

  function filterArticles() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    renderArticles(allArticles.filter(a => a.title.toLowerCase().includes(q) || (a.content||'').toLowerCase().includes(q)));
  }

  async function viewArticle(id) {
    const { ok, data } = await apiFetch(`/knowledge/index.php?id=${id}`);
    if (!ok) return;
    currentArticle = data;
    document.getElementById('viewTitle').textContent = data.title;
    document.getElementById('vCat').textContent    = data.category || 'General';
    document.getElementById('vAuthor').textContent = `By ${data.created_by}`;
    document.getElementById('vViews').textContent  = data.views;
    document.getElementById('vContent').textContent = data.content;
    if (user.role !== 'user') document.getElementById('editArticleBtn').style.display = 'block';
    openModal('viewModal');
    loadArticles();
  }

  async function loadCategories() {
    const { ok, data } = await apiFetch('/categories/index.php?type=categories');
    if (!ok) return;
    const sel = document.getElementById('editCat');
    data.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name; sel.appendChild(o); });
  }

  function openNewModal() {
    document.getElementById('editId').value = '';
    document.getElementById('editModalTitle').textContent = 'New Article';
    document.getElementById('editTitle').value = '';
    document.getElementById('editContent').value = '';
    document.getElementById('editPublished').checked = false;
    openModal('editModal');
  }

  function openEditModal() {
    if (!currentArticle) return;
    closeModal('viewModal');
    document.getElementById('editId').value = currentArticle.id;
    document.getElementById('editModalTitle').textContent = 'Edit Article';
    document.getElementById('editTitle').value = currentArticle.title;
    document.getElementById('editContent').value = currentArticle.content;
    document.getElementById('editPublished').checked = !!currentArticle.is_published;
    openModal('editModal');
  }

  async function saveArticle() {
    const id = document.getElementById('editId').value;
    const body = { title: document.getElementById('editTitle').value.trim(), content: document.getElementById('editContent').value.trim(), category_id: document.getElementById('editCat').value || null, is_published: document.getElementById('editPublished').checked ? 1 : 0 };
    if (!body.title || !body.content) return showAlert('editAlert', t('fillRequired'));
    const { ok, data } = await apiFetch(id ? `/knowledge/index.php?id=${id}` : '/knowledge/index.php', { method: id ? 'PUT' : 'POST', body: JSON.stringify(body) });
    if (!ok) return showAlert('editAlert', data.message);
    closeModal('editModal');
    showAlert('successBox', 'Article saved.', 'success');
    loadArticles();
  }

  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  loadArticles(); loadCategories();
</script>
</body>
</html>
