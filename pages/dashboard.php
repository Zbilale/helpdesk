<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .charts-row{display:grid;grid-template-columns:1.5fr 1fr;gap:16px;margin-bottom:24px}
    .chart-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px}
    .chart-title{font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:16px}
    .chart-wrap{position:relative;height:220px}
    .bottom-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .list-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px}
    .list-title{font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:14px}
    .ticket-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);cursor:pointer}
    .ticket-row:last-child{border-bottom:none}
    .ticket-id{font-size:12px;color:var(--text-muted);width:40px;flex-shrink:0}
    .ticket-name{flex:1;font-size:13px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .stat-card{border-left:3px solid var(--accent)}
    @media(max-width:900px){.charts-row,.bottom-row{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="page-title" data-i18n="dashboard">Dashboard</div>
      <div class="page-subtitle" id="welcomeMsg"></div>
      <div class="stats-grid">
        <div class="stat-card card"><div class="stat-label" data-i18n="totalTickets">Total</div><div class="stat-value" id="sTotal">—</div></div>
        <div class="stat-card card" style="border-left-color:var(--info)"><div class="stat-label" data-i18n="open">Open</div><div class="stat-value" id="sOpen" style="color:var(--info)">—</div></div>
        <div class="stat-card card" style="border-left-color:var(--accent)"><div class="stat-label" data-i18n="inProgress">In Progress</div><div class="stat-value" id="sProgress" style="color:var(--accent)">—</div></div>
        <div class="stat-card card" style="border-left-color:var(--danger)"><div class="stat-label" data-i18n="critical">Critical</div><div class="stat-value" id="sCritical" style="color:var(--danger)">—</div></div>
      </div>
      <div class="charts-row">
        <div class="chart-card"><div class="chart-title">Tickets — last 6 months</div><div class="chart-wrap"><canvas id="lineChart"></canvas></div></div>
        <div class="chart-card"><div class="chart-title" data-i18n="categories">By category</div><div class="chart-wrap"><canvas id="donutChart"></canvas></div></div>
      </div>
      <div class="bottom-row">
        <div class="list-card"><div class="list-title" data-i18n="recentTickets">Recent tickets</div><div id="recentList">Loading...</div></div>
        <div class="list-card"><div class="list-title" data-i18n="topTechs">Top technicians</div><div id="techList">Loading...</div></div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/app.js"></script>
<script src="../js/layout.js"></script>
<script>
  buildLayout('dashboard');
  if (getUser().role === 'user') window.location.href = 'home.php';
  const user = getUser();
  document.getElementById('welcomeMsg').textContent = `${t('welcomeBack')}, ${user.first_name}!`;

  async function load() {
    const [statsRes, ticketsRes, techsRes] = await Promise.all([
      apiFetch('/tickets/stats.php'),
      apiFetch('/tickets/get_all.php'),
      apiFetch('/users/technicians.php'),
    ]);
    if (statsRes.ok) {
      const s = statsRes.data;
      document.getElementById('sTotal').textContent    = s.total;
      document.getElementById('sOpen').textContent     = s.open_count;
      document.getElementById('sProgress').textContent = s.in_progress;
      document.getElementById('sCritical').textContent = s.critical;
    }
    if (ticketsRes.ok) {
      const tickets = ticketsRes.data.slice(0,5);
      document.getElementById('recentList').innerHTML = tickets.length
        ? tickets.map(tk => `<div class="ticket-row" onclick="window.location.href='ticket-detail.php?id=${tk.id}'"><span class="ticket-id">#${tk.id}</span><span class="ticket-name">${tk.title}</span>${priorityBadge(tk.priority)}</div>`).join('')
        : `<p style="color:var(--text-muted);font-size:13px">${t('noTickets')}</p>`;
    }
    if (techsRes.ok) {
      const colors = ['var(--accent)','var(--info)','var(--warning)','var(--success)'];
      document.getElementById('techList').innerHTML = techsRes.data.slice(0,4).map((tech,i) => `
        <div class="ticket-row"><div style="width:28px;height:28px;border-radius:50%;background:${colors[i%colors.length]};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;flex-shrink:0">${tech.name.split(' ').map(n=>n[0]).join('')}</div><span class="ticket-name">${tech.name}</span></div>`).join('');
    }
  }

  function buildCharts() {
    const dark = getTheme()==='dark';
    const grid = dark?'rgba(255,255,255,0.05)':'rgba(0,0,0,0.06)';
    const tick = dark?'#8b8fa8':'#5a5e7a';
    new Chart(document.getElementById('lineChart'),{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[
      {label:t('open'),data:[45,52,38,60,55,87],borderColor:'var(--info)',backgroundColor:'rgba(106,156,245,0.07)',tension:.4,pointRadius:3,borderWidth:2},
      {label:t('inProgress'),data:[20,30,25,40,48,54],borderColor:'var(--accent)',backgroundColor:'rgba(108,99,255,0.07)',tension:.4,pointRadius:3,borderWidth:2},
      {label:t('resolved'),data:[60,45,70,55,80,143],borderColor:'var(--success)',backgroundColor:'rgba(106,191,106,0.07)',tension:.4,pointRadius:3,borderWidth:2}
    ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:tick,font:{size:11}}}},scales:{x:{ticks:{color:tick,font:{size:11}},grid:{color:grid}},y:{ticks:{color:tick,font:{size:11}},grid:{color:grid}}}}});
    new Chart(document.getElementById('donutChart'),{type:'doughnut',data:{labels:[t('software'),t('hardware'),t('network'),t('security'),t('other')],datasets:[{data:[40,30,20,7,3],backgroundColor:['var(--accent)','var(--info)','var(--success)','var(--warning)','var(--text-muted)'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{color:tick,font:{size:11},padding:12}}}}});
  }

  load();
  buildCharts();
</script>
</body>
</html>
