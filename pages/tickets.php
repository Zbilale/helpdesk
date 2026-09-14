<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Tickets</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="page-title" data-i18n="tickets">Tickets</div>
      <div class="page-subtitle" id="ticketCount"></div>
      <div class="table-wrap">
        <div class="table-header">
          <div class="filters">
            <input type="text" id="searchInput" placeholder="Search..." style="width:200px;padding:7px 12px;font-size:13px" oninput="filterTickets()"/>
            <select id="filterStatus" onchange="filterTickets()">
              <option value="">All</option><option value="Open">Open</option>
              <option value="In Progress">In Progress</option><option value="Resolved">Resolved</option><option value="Closed">Closed</option>
            </select>
            <select id="filterPriority" onchange="filterTickets()">
              <option value="">All</option><option value="Low">Low</option>
              <option value="Medium">Medium</option><option value="High">High</option><option value="Critical">Critical</option>
            </select>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-secondary btn-sm" onclick="exportCSV()"><i class="ti ti-download"></i> CSV</button>
            <button class="btn btn-primary btn-sm" onclick="window.location.href='new-ticket.php'"><i class="ti ti-plus"></i> <span data-i18n="newTicket">New Ticket</span></button>
          </div>
        </div>
        <table>
          <thead><tr>
            <th>#</th><th data-i18n="ticketTitle">Title</th><th data-i18n="category">Category</th>
            <th data-i18n="subcategory">Device</th><th data-i18n="postNumber">Post</th>
            <th data-i18n="priority">Priority</th><th data-i18n="status">Status</th>
            <th data-i18n="assignedTo">Assigned to</th><th data-i18n="createdAt">Date</th>
          </tr></thead>
          <tbody id="ticketsBody"><tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('tickets');
  let allTickets = [];

  async function loadTickets() {
    const params = new URLSearchParams(window.location.search);
    const search = params.get('search') || '';
    if (search) document.getElementById('searchInput').value = search;
    const { ok, data } = await apiFetch('/tickets/get_all.php');
    if (!ok) return;
    allTickets = data;
    document.getElementById('ticketCount').textContent = `${data.length} ticket(s)`;
    renderTickets(data);
  }

  function renderTickets(tickets) {
    const tbody = document.getElementById('ticketsBody');
    if (!tickets.length) { tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><i class="ti ti-ticket-off"></i><p>${t('noTickets')}</p></div></td></tr>`; return; }
    tbody.innerHTML = tickets.map(tk => `
      <tr onclick="window.location.href='ticket-detail.php?id=${tk.id}'">
        <td style="color:var(--text-muted)">#${tk.id}</td>
        <td style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${tk.title}</td>
        <td>${tk.category||'—'}</td><td>${tk.subcategory||'—'}</td><td>${tk.post_number||'—'}</td>
        <td>${priorityBadge(tk.priority)}</td><td>${statusBadge(tk.status)}</td>
        <td>${tk.assigned_to||`<span style="color:var(--text-muted)">${t('unassigned')}</span>`}</td>
        <td style="color:var(--text-muted)">${formatDate(tk.created_at)}</td>
      </tr>`).join('');
  }

  function filterTickets() {
    const search   = document.getElementById('searchInput').value.toLowerCase();
    const status   = document.getElementById('filterStatus').value;
    const priority = document.getElementById('filterPriority').value;
    const filtered = allTickets.filter(tk =>
      (!search   || tk.title.toLowerCase().includes(search) || String(tk.id).includes(search)) &&
      (!status   || tk.status === status) &&
      (!priority || tk.priority === priority)
    );
    document.getElementById('ticketCount').textContent = `${filtered.length} ticket(s)`;
    renderTickets(filtered);
  }

  function exportCSV() {
    const rows = [['ID','Title','Category','Device','Post','Priority','Status','Assigned To','Created At']];
    allTickets.forEach(tk => rows.push([tk.id,tk.title,tk.category,tk.subcategory||'',tk.post_number||'',tk.priority,tk.status,tk.assigned_to||'',formatDate(tk.created_at)]));
    const csv = rows.map(r => r.map(v=>`"${v}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download = 'tickets.csv'; a.click();
  }

  loadTickets();
</script>
</body>
</html>
