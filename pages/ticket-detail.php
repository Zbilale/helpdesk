<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Ticket Detail</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .detail-grid{display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start}
    .detail-main{display:flex;flex-direction:column;gap:16px}
    .detail-side{display:flex;flex-direction:column;gap:14px}
    .info-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px}
    .info-title{font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}
    .meta-list{display:flex;flex-direction:column;gap:10px}
    .meta-item{display:flex;justify-content:space-between;align-items:center;font-size:13px}
    .meta-label{color:var(--text-secondary)}
    .meta-value{color:var(--text-primary);font-weight:500}
    .solution-box{background:var(--success-light);border:1px solid rgba(106,191,106,.25);border-radius:var(--radius-md);padding:16px}
    .sol-label{font-size:12px;font-weight:600;color:var(--success);margin-bottom:8px;display:flex;align-items:center;gap:6px}
    .sol-text{font-size:13px;color:var(--text-primary);line-height:1.7;white-space:pre-wrap}
    .comment-item{display:flex;gap:12px;margin-bottom:14px}
    .comment-avatar{width:32px;height:32px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#fff;flex-shrink:0}
    .comment-bubble{flex:1;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px 14px}
    .comment-bubble.internal{background:var(--warning-light);border-color:rgba(245,165,106,.25)}
    .comment-header{display:flex;justify-content:space-between;margin-bottom:6px}
    .comment-author{font-size:12px;font-weight:600;color:var(--text-primary)}
    .comment-time{font-size:11px;color:var(--text-muted)}
    .comment-text{font-size:13px;color:var(--text-primary);line-height:1.6}
    .internal-badge{font-size:10px;color:var(--warning);background:var(--warning-light);padding:1px 7px;border-radius:10px;margin-left:6px}
    .actions-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;display:flex;flex-direction:column;gap:10px}
    @media(max-width:900px){.detail-grid{grid-template-columns:1fr}}
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
        <div><div class="page-title" id="ticketHeader">Loading...</div><div style="font-size:13px;color:var(--text-muted)" id="ticketMeta"></div></div>
        <div id="ticketBadges" style="display:flex;gap:8px;margin-left:auto"></div>
      </div>
      <div id="alertBox" class="alert alert-error"></div>
      <div id="successBox" class="alert alert-success"></div>
      <div class="detail-grid">
        <div class="detail-main">
          <div class="info-card">
            <div class="info-title" data-i18n="description">Description</div>
            <div style="font-size:14px;color:var(--text-primary);line-height:1.8;white-space:pre-wrap" id="ticketDesc">—</div>
          </div>
          <div class="solution-box" id="solutionBox" style="display:none">
            <div class="sol-label"><i class="ti ti-check"></i> Solution</div>
            <div class="sol-text" id="solutionText"></div>
          </div>
          <div class="info-card">
            <div class="info-title" data-i18n="comments">Comments</div>
            <div id="commentList"></div>
            <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
              <textarea id="commentInput" placeholder="Add a comment..." style="margin-bottom:10px"></textarea>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <label id="internalToggle" style="display:none;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer">
                  <input type="checkbox" id="isInternal" style="width:auto"/> <span data-i18n="internalNote">Internal note</span>
                </label>
                <button class="btn btn-primary btn-sm" style="width:auto" onclick="postComment()">
                  <i class="ti ti-send"></i> <span data-i18n="postComment">Post</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="detail-side">
          <div class="info-card" id="slaCard">
            <div class="info-title">SLA</div>
            <div style="font-size:22px;font-weight:600;margin-top:6px" id="slaDisplay">—</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px" id="slaLabel"></div>
          </div>
          <div class="info-card">
            <div class="info-title">Details</div>
            <div class="meta-list">
              <div class="meta-item"><span class="meta-label">Status</span><span id="mStatus">—</span></div>
              <div class="meta-item"><span class="meta-label">Priority</span><span id="mPriority">—</span></div>
              <div class="meta-item"><span class="meta-label">Category</span><span class="meta-value" id="mCategory">—</span></div>
              <div class="meta-item"><span class="meta-label">Device</span><span class="meta-value" id="mDevice">—</span></div>
              <div class="meta-item"><span class="meta-label">Post</span><span class="meta-value" id="mPost">—</span></div>
              <div class="meta-item"><span class="meta-label">Created by</span><span class="meta-value" id="mCreatedBy">—</span></div>
              <div class="meta-item"><span class="meta-label">Assigned to</span><span class="meta-value" id="mAssignedTo">—</span></div>
              <div class="meta-item"><span class="meta-label">Created</span><span class="meta-value" id="mCreatedAt">—</span></div>
            </div>
          </div>
          <div class="actions-card">
            <div class="info-title">Actions</div>
            <div id="actionBtns"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Solve Modal -->
<div class="modal-overlay" id="solveModal">
  <div class="modal">
    <div class="modal-header"><span data-i18n="solveTicket">Mark as resolved</span><button class="btn-icon" onclick="closeModal('solveModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <label data-i18n="solution">Solution</label>
      <textarea id="solutionInput" style="margin-top:8px;min-height:140px" placeholder="Write the solution..."></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('solveModal')" data-i18n="cancel">Cancel</button>
      <button class="btn btn-primary" onclick="confirmSolve()"><i class="ti ti-check"></i> Resolve</button>
    </div>
  </div>
</div>

<!-- Release Modal -->
<div class="modal-overlay" id="releaseModal">
  <div class="modal">
    <div class="modal-header"><span data-i18n="releaseTicket">Release ticket</span><button class="btn-icon" onclick="closeModal('releaseModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <label data-i18n="whyCantSolve">Why can't you solve this?</label>
      <textarea id="releaseNote" style="margin-top:8px" placeholder="Reason..."></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('releaseModal')" data-i18n="cancel">Cancel</button>
      <button class="btn btn-danger" onclick="confirmRelease()" data-i18n="confirm">Confirm</button>
    </div>
  </div>
</div>

<!-- Assign Modal -->
<div class="modal-overlay" id="assignModal">
  <div class="modal">
    <div class="modal-header"><span data-i18n="assignTicket">Assign to technician</span><button class="btn-icon" onclick="closeModal('assignModal')"><i class="ti ti-x"></i></button></div>
    <div class="modal-body">
      <label data-i18n="selectTech">Select technician</label>
      <select id="assignSelect" style="margin-top:8px"><option value="">Loading...</option></select>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('assignModal')" data-i18n="cancel">Cancel</button>
      <button class="btn btn-primary" onclick="confirmAssign()" data-i18n="assignTicket">Assign</button>
    </div>
  </div>
</div>

<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('tickets');
  const ticketId = new URLSearchParams(window.location.search).get('id');
  const user = getUser();
  let ticket = null;
  if (!ticketId) window.location.href = 'tickets.php';

  async function loadTicket() {
    const { ok, data } = await apiFetch(`/tickets/get_one.php?id=${ticketId}`);
    if (!ok) { showAlert('alertBox', data.message); return; }
    ticket = data;
    renderTicket();
    loadComments();
  }

  function renderTicket() {
    document.title = `#${ticket.id} — ${ticket.title}`;
    document.getElementById('ticketHeader').textContent = `#${ticket.id} — ${ticket.title}`;
    document.getElementById('ticketMeta').textContent = `Created by: ${ticket.created_by_name} • ${formatDate(ticket.created_at)}`;
    document.getElementById('ticketBadges').innerHTML = statusBadge(ticket.status) + ' ' + priorityBadge(ticket.priority);
    document.getElementById('ticketDesc').textContent = ticket.description;
    document.getElementById('mStatus').innerHTML   = statusBadge(ticket.status);
    document.getElementById('mPriority').innerHTML = priorityBadge(ticket.priority);
    document.getElementById('mCategory').textContent   = ticket.category || '—';
    document.getElementById('mDevice').textContent     = ticket.subcategory || '—';
    document.getElementById('mPost').textContent       = ticket.post_number || '—';
    document.getElementById('mCreatedBy').textContent  = ticket.created_by_name;
    document.getElementById('mAssignedTo').textContent = ticket.assigned_to_name || t('unassigned');
    document.getElementById('mCreatedAt').textContent  = formatDate(ticket.created_at);
    if (ticket.solution) { document.getElementById('solutionBox').style.display = 'block'; document.getElementById('solutionText').textContent = ticket.solution; }
    if (user.role !== 'user') document.getElementById('internalToggle').style.display = 'flex';
    renderSLA();
    renderActions();
  }

  function renderSLA() {
    if (!ticket.sla_deadline) return;
    const diff = new Date(ticket.sla_deadline) - new Date();
    const el = document.getElementById('slaDisplay');
    if (ticket.sla_breached || diff < 0) {
      el.textContent = t('slaBreached'); el.style.color = 'var(--danger)';
      document.getElementById('slaCard').style.borderColor = 'var(--danger)';
    } else {
      const h = Math.floor(diff/3600000), m = Math.floor((diff%3600000)/60000);
      el.textContent = `${h}h ${m}m`; el.style.color = h < 1 ? 'var(--warning)' : 'var(--success)';
    }
    document.getElementById('slaLabel').textContent = `Deadline: ${formatDate(ticket.sla_deadline)}`;
  }

  function renderActions() {
    const btns = document.getElementById('actionBtns');
    const isTech = user.role === 'technician', isAdmin = user.role === 'admin';
    const isAssignedToMe = parseInt(ticket.assigned_to) === user.id;
    let html = '';
    if (isTech && ticket.status === 'Open' && !ticket.assigned_to) html += `<button class="btn btn-primary" onclick="takeTicket()"><i class="ti ti-hand-stop"></i> ${t('takeTicket')}</button>`;
    if (isTech && isAssignedToMe && ticket.status === 'In Progress') {
      html += `<button class="btn btn-primary" onclick="openModal('solveModal')"><i class="ti ti-check"></i> ${t('solveTicket')}</button>`;
      html += `<button class="btn btn-danger" onclick="openModal('releaseModal')"><i class="ti ti-arrow-back-up"></i> ${t('releaseTicket')}</button>`;
    }
    if (isAdmin && (ticket.status === 'Open' || ticket.status === 'In Progress')) html += `<button class="btn btn-secondary" onclick="openAssignModal()"><i class="ti ti-user-check"></i> ${t('assignTicket')}</button>`;
    if (isAdmin && ticket.status === 'Resolved') html += `<button class="btn btn-secondary" onclick="closeTicket()"><i class="ti ti-archive"></i> Close</button>`;
    if (isAdmin) html += `<button class="btn btn-danger btn-sm" onclick="deleteTicket()" style="margin-top:8px"><i class="ti ti-trash"></i> ${t('delete')}</button>`;
    btns.innerHTML = html || `<p style="font-size:13px;color:var(--text-muted)">No actions available.</p>`;
  }

  async function takeTicket() {
    const { ok, data } = await apiFetch(`/tickets/update.php?id=${ticketId}`, { method:'POST', body: JSON.stringify({ status_id:2, assigned_to: user.id }) });
    if (!ok) return showAlert('alertBox', data.message);
    showAlert('successBox', t('ticketUpdated'), 'success'); loadTicket();
  }

  async function confirmSolve() {
    const solution = document.getElementById('solutionInput').value.trim();
    if (!solution) return showAlert('alertBox', t('fillRequired'));
    const { ok, data } = await apiFetch(`/tickets/update.php?id=${ticketId}`, { method:'POST', body: JSON.stringify({ status_id:3, solution }) });
    if (!ok) return showAlert('alertBox', data.message);
    closeModal('solveModal'); showAlert('successBox', t('ticketUpdated'), 'success'); loadTicket();
  }

  async function confirmRelease() {
    const { ok, data } = await apiFetch(`/tickets/update.php?id=${ticketId}`, { method:'POST', body: JSON.stringify({ status_id:1, assigned_to:null }) });
    if (!ok) return showAlert('alertBox', data.message);
    closeModal('releaseModal'); showAlert('successBox', t('ticketUpdated'), 'success'); loadTicket();
  }

  async function openAssignModal() {
    openModal('assignModal');
    const { ok, data } = await apiFetch('/users/technicians.php');
    if (!ok) return;
    const sel = document.getElementById('assignSelect');
    sel.innerHTML = `<option value="">${t('selectTech')}</option>` + data.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
  }

  async function confirmAssign() {
    const techId = document.getElementById('assignSelect').value;
    if (!techId) return showAlert('alertBox', t('fillRequired'));
    const { ok, data } = await apiFetch(`/tickets/update.php?id=${ticketId}`, { method:'POST', body: JSON.stringify({ assigned_to: parseInt(techId), status_id:2 }) });
    if (!ok) return showAlert('alertBox', data.message);
    closeModal('assignModal'); showAlert('successBox', t('ticketUpdated'), 'success'); loadTicket();
  }

  async function closeTicket() {
    const { ok } = await apiFetch(`/tickets/update.php?id=${ticketId}`, { method:'POST', body: JSON.stringify({ status_id:4 }) });
    if (ok) { showAlert('successBox', t('ticketUpdated'), 'success'); loadTicket(); }
  }

  async function deleteTicket() {
    if (!confirm(t('confirm'))) return;
    const { ok } = await apiFetch(`/tickets/delete.php?id=${ticketId}`, { method:'POST' });
    if (ok) window.location.href = 'tickets.php';
  }

  async function loadComments() {
    const { ok, data } = await apiFetch(`/comments/index.php?ticket_id=${ticketId}`);
    if (!ok) return;
    const list = document.getElementById('commentList');
    if (!data.length) { list.innerHTML = `<p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">No comments yet.</p>`; return; }
    const colors = { technician:'var(--accent)', admin:'var(--danger)', user:'var(--info)' };
    list.innerHTML = data.map(c => `
      <div class="comment-item">
        <div class="comment-avatar" style="background:${colors[c.author_role]||'var(--accent)'}">${c.author.split(' ').map(n=>n[0]).join('')}</div>
        <div class="comment-bubble ${c.is_internal?'internal':''}">
          <div class="comment-header">
            <span class="comment-author">${c.author}${c.is_internal?'<span class="internal-badge">Internal</span>':''}</span>
            <span class="comment-time">${formatDate(c.created_at)}</span>
          </div>
          <div class="comment-text">${c.content}</div>
        </div>
      </div>`).join('');
  }

  async function postComment() {
    const content = document.getElementById('commentInput').value.trim();
    const is_internal = document.getElementById('isInternal').checked ? 1 : 0;
    if (!content) return;
    const { ok } = await apiFetch(`/comments/index.php?ticket_id=${ticketId}`, { method:'POST', body: JSON.stringify({ content, is_internal }) });
    if (ok) { document.getElementById('commentInput').value = ''; loadComments(); }
  }

  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  loadTicket();
</script>
</body>
</html>
