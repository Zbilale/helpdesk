function buildLayout(activePage) {
  const user = getUser();
  if (!user) { window.location.href = `${BASE}/index.php`; return; }
  const lang = getLang();

  const navItems = [
    { key:'home',      icon:'ti-home',                href:'home.php',      roles:['user'] },
    { key:'dashboard', icon:'ti-layout-dashboard',     href:'dashboard.php', roles:['admin','technician'] },
    { key:'tickets',   icon:'ti-ticket',               href:'tickets.php',   roles:['admin','technician','user'] },
    { key:'newTicket', icon:'ti-plus',                 href:'new-ticket.php',roles:['admin','technician','user'] },
    { key:'users',     icon:'ti-users',                href:'users.php',     roles:['admin'] },
    { key:'knowledge', icon:'ti-book',                 href:'knowledge.php', roles:['admin','technician','user'] },
    { key:'categories',icon:'ti-category',             href:'categories.php',roles:['admin'] },
  ].filter(item => item.roles.includes(user.role));

  document.getElementById('sidebar-placeholder').innerHTML = `
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <div class="logo-icon"><i class="ti ti-headset"></i></div>
        <span data-i18n="appName">${t('appName')}</span>
      </div>
      <nav class="sidebar-nav">
        ${navItems.map(item => `
          <a class="nav-item ${activePage===item.key?'active':''}" href="${item.href}">
            <i class="ti ${item.icon}"></i>
            <span data-i18n="${item.key}">${t(item.key)}</span>
          </a>`).join('')}
      </nav>
      <div class="sidebar-bottom">
        <a class="nav-item" href="profile.php">
          <i class="ti ti-user-circle"></i>
          <span data-i18n="profile">${t('profile')}</span>
        </a>
        <div class="nav-item" onclick="logout()" style="cursor:pointer">
          <i class="ti ti-logout"></i>
          <span data-i18n="logout">${t('logout')}</span>
        </div>
      </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>`;

  document.getElementById('topbar-placeholder').innerHTML = `
    <header class="topbar">
      <button class="btn-icon sidebar-toggle" onclick="toggleSidebar()">
        <i class="ti ti-menu-2"></i>
      </button>
      <div class="topbar-search">
        <i class="ti ti-search"></i>
        <input type="text" data-i18n-placeholder="search" placeholder="${t('search')}"
               oninput="if(this.value.length>2) window.location.href='tickets.php?search='+encodeURIComponent(this.value)"/>
      </div>
      <div class="topbar-right">
        <div class="lang-switcher">
          <button onclick="setLang('en')" class="lang-btn ${lang==='en'?'active':''}">EN</button>
          <button onclick="setLang('fr')" class="lang-btn ${lang==='fr'?'active':''}">FR</button>
          <button onclick="setLang('ar')" class="lang-btn ${lang==='ar'?'active':''}">ع</button>
        </div>
        <button class="btn-icon" onclick="toggleThemeLayout()" id="themeToggle">
          <i class="ti ti-${getTheme()==='dark'?'sun':'moon'}"></i>
        </button>
        <div class="notif-wrap">
          <button class="btn-icon notif-btn" onclick="toggleNotifs()">
            <i class="ti ti-bell"></i>
            <span class="notif-badge" id="notifBadge" style="display:none"></span>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
              <span>${t('notifications')}</span>
              <button class="btn-sm btn-secondary" onclick="markAllRead()">${t('markRead')}</button>
            </div>
            <div id="notifList"><p class="notif-empty">${t('noNotifs')}</p></div>
          </div>
        </div>
        <div class="user-chip" onclick="window.location.href='profile.php'">
          <div class="user-avatar">${user.first_name[0]}${user.last_name[0]}</div>
          <div class="user-info">
            <span class="user-name">${user.first_name} ${user.last_name}</span>
            <span class="user-role">${user.role}</span>
          </div>
        </div>
      </div>
    </header>`;

  applyLang(getLang());
  loadNotifications();

  document.querySelectorAll('.sidebar .nav-item').forEach(el => {
    el.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); });
  });

  document.addEventListener('click', e => {
    const wrap = document.querySelector('.notif-wrap');
    if (wrap && !wrap.contains(e.target)) document.getElementById('notifDropdown')?.classList.remove('open');
  });
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (window.innerWidth <= 768) {
    sidebar.classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show', sidebar.classList.contains('open'));
  } else {
    sidebar.classList.toggle('collapsed');
  }
}

function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}

function toggleThemeLayout() {
  const next = getTheme() === 'dark' ? 'light' : 'dark';
  setTheme(next);
  document.getElementById('themeToggle').innerHTML = `<i class="ti ti-${next==='dark'?'sun':'moon'}"></i>`;
}

function toggleNotifs() {
  document.getElementById('notifDropdown').classList.toggle('open');
}

async function loadNotifications() {
  const { ok, data } = await apiFetch('/notifications/index.php');
  if (!ok) return;
  const unread = data.filter(n => !n.is_read);
  const badge = document.getElementById('notifBadge');
  if (unread.length > 0) { badge.style.display = 'flex'; badge.textContent = unread.length > 9 ? '9+' : unread.length; }
  if (!data.length) return;
  document.getElementById('notifList').innerHTML = data.slice(0,8).map(n => `
    <div class="notif-item ${n.is_read?'':'unread'}" onclick="if(${n.ticket_id}) window.location.href='ticket-detail.php?id=${n.ticket_id}'">
      <div class="notif-msg">${n.message}</div>
      <div class="notif-time">${formatDate(n.created_at)}</div>
    </div>`).join('');
}

async function markAllRead() {
  await apiFetch('/notifications/index.php', { method: 'PUT' });
  document.getElementById('notifBadge').style.display = 'none';
  loadNotifications();
}
