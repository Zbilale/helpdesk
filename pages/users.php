<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Users</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .uavatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#fff}
    .role-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
    .role-admin{background:var(--danger-light);color:var(--danger)}
    .role-technician{background:var(--accent-light);color:var(--accent)}
    .role-user{background:var(--info-light);color:var(--info)}
    .dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="page-title" data-i18n="users">Users</div>
      <div class="page-subtitle" id="userCount"></div>
      <div id="successBox" class="alert alert-success"></div>
      <div class="table-wrap">
        <div class="table-header">
          <div class="filters">
            <input type="text" id="searchInput" placeholder="Search users..." style="width:200px;padding:7px 12px;font-size:13px" oninput="filterUsers()"/>
            <select id="filterRole" onchange="filterUsers()">
              <option value="">All roles</option><option value="admin">Admin</option>
              <option value="technician">Technician</option><option value="user">User</option>
            </select>
          </div>
        </div>
        <table>
          <thead><tr><th></th><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead>
          <tbody id="usersBody"><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header"><span>Edit User</span><button class="btn-icon" onclick="closeModal('editModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <input type="hidden" id="editId"/>
      <div id="editAlert" class="alert alert-error"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group"><label>First name</label><input type="text" id="editFirst"/></div>
        <div class="form-group"><label>Last name</label><input type="text" id="editLast"/></div>
      </div>
      <div class="form-group"><label>Phone</label><input type="text" id="editPhone"/></div>
      <div class="form-group"><label>Department</label><input type="text" id="editDept"/></div>
      <div class="form-group"><label>Role</label>
        <select id="editRole"><option value="1">Admin</option><option value="2">Technician</option><option value="3">User</option></select>
      </div>
      <div class="form-group"><label>Status</label>
        <select id="editStatus"><option value="1">Active</option><option value="0">Inactive</option></select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveUser()">Save</button>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('users');
  let allUsers = [];

  async function loadUsers() {
    const { ok, data } = await apiFetch('/users/index.php');
    if (!ok) return;
    allUsers = data;
    document.getElementById('userCount').textContent = `${data.length} user(s)`;
    renderUsers(data);
  }

  function renderUsers(users) {
    const colors = { admin:'var(--danger)', technician:'var(--accent)', user:'var(--info)' };
    document.getElementById('usersBody').innerHTML = users.length
      ? users.map(u => `
        <tr onclick="openEdit(${u.id})">
          <td><div class="uavatar" style="background:${colors[u.role]||'var(--accent)'}">${u.first_name[0]}${u.last_name[0]}</div></td>
          <td><strong>${u.first_name} ${u.last_name}</strong></td>
          <td style="color:var(--text-secondary)">${u.email}</td>
          <td>${u.department||'—'}</td>
          <td><span class="role-badge role-${u.role}">${u.role}</span></td>
          <td><span class="dot" style="background:${u.is_active?'var(--success)':'var(--text-muted)'}"></span>${u.is_active?'Active':'Inactive'}</td>
          <td style="color:var(--text-muted)">${formatDate(u.created_at)}</td>
          <td onclick="event.stopPropagation()">
            <button class="btn-icon" onclick="openEdit(${u.id})"><i class="ti ti-edit"></i></button>
          </td>
        </tr>`).join('')
      : `<tr><td colspan="8"><div class="empty-state"><i class="ti ti-users-off"></i><p>No users found.</p></div></td></tr>`;
  }

  function filterUsers() {
    const s = document.getElementById('searchInput').value.toLowerCase();
    const r = document.getElementById('filterRole').value;
    renderUsers(allUsers.filter(u => (!s || `${u.first_name} ${u.last_name} ${u.email}`.toLowerCase().includes(s)) && (!r || u.role === r)));
  }

  function openEdit(id) {
    const u = allUsers.find(u => u.id == id);
    if (!u) return;
    document.getElementById('editId').value    = u.id;
    document.getElementById('editFirst').value = u.first_name;
    document.getElementById('editLast').value  = u.last_name;
    document.getElementById('editPhone').value = u.phone || '';
    document.getElementById('editDept').value  = u.department || '';
    document.getElementById('editStatus').value = u.is_active ? '1' : '0';
    document.getElementById('editRole').value  = u.role === 'admin' ? '1' : u.role === 'technician' ? '2' : '3';
    document.getElementById('editModal').classList.add('open');
  }

  async function saveUser() {
    const id = document.getElementById('editId').value;
    const { ok, data } = await apiFetch(`/users/index.php?id=${id}`, {
      method: 'PUT',
      body: JSON.stringify({
        first_name: document.getElementById('editFirst').value,
        last_name:  document.getElementById('editLast').value,
        phone:      document.getElementById('editPhone').value,
        department: document.getElementById('editDept').value,
        role_id:    parseInt(document.getElementById('editRole').value),
        is_active:  parseInt(document.getElementById('editStatus').value),
      })
    });
    if (!ok) return showAlert('editAlert', data.message);
    closeModal('editModal');
    showAlert('successBox', t('profileUpdated'), 'success');
    loadUsers();
  }

  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  loadUsers();
</script>
</body>
</html>
