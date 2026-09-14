<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IT Helpdesk — Home</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Tajawal:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <link rel="stylesheet" href="../css/style.css"/>
  <link rel="stylesheet" href="../css/layout.css"/>
  <style>
    .home-wrap{display:flex;align-items:center;justify-content:center;min-height:calc(100vh - var(--topbar-height));padding:24px}
    .home-inner{text-align:center;max-width:700px;width:100%}
    .home-title{font-size:26px;font-weight:600;color:var(--text-primary);margin-bottom:8px}
    .home-subtitle{font-size:14px;color:var(--text-secondary);margin-bottom:40px}
    .action-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    .action-card{
      background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-lg);
      padding:40px 24px;cursor:pointer;transition:all .2s;display:flex;flex-direction:column;align-items:center;gap:16px;
    }
    .action-card:hover{border-color:var(--accent);transform:translateY(-4px);box-shadow:var(--shadow)}
    .action-icon{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px}
    .action-icon.new{background:var(--accent-light);color:var(--accent)}
    .action-icon.existing{background:var(--info-light);color:var(--info)}
    .action-title{font-size:17px;font-weight:600;color:var(--text-primary)}
    .action-desc{font-size:13px;color:var(--text-secondary)}
    @media(max-width:600px){.action-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="app-shell">
  <div id="sidebar-placeholder"></div>
  <div class="main-area">
    <div id="topbar-placeholder"></div>
    <div class="page-content">
      <div class="home-wrap">
        <div class="home-inner">
          <div class="home-title" id="welcomeTitle">Welcome!</div>
          <div class="home-subtitle" data-i18n="homeSubtitle">What would you like to do?</div>
          <div class="action-grid">
            <div class="action-card" onclick="window.location.href='new-ticket.php'">
              <div class="action-icon new"><i class="ti ti-plus"></i></div>
              <div class="action-title" data-i18n="newTicket">New Ticket</div>
              <div class="action-desc" data-i18n="newTicketDesc">Report a new issue with your device or network</div>
            </div>
            <div class="action-card" onclick="window.location.href='tickets.php'">
              <div class="action-icon existing"><i class="ti ti-list-details"></i></div>
              <div class="action-title" data-i18n="existingTickets">Existing Tickets</div>
              <div class="action-desc" data-i18n="existingTicketsDesc">View and track your submitted tickets</div>
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
  buildLayout('home');
  const user = getUser();
  document.getElementById('welcomeTitle').textContent = `${t('welcomeBack')}, ${user.first_name}!`;
</script>
</body>
</html>
