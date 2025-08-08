<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Admin';

// DB connection
$host = "localhost";
$dbname = "ams";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

function safeCount(mysqli $conn, string $sql, ?string $fallbackSql = null): int {
    $result = $conn->query($sql);
    if ($result && ($row = $result->fetch_assoc()) && isset($row['total'])) {
        return (int)$row['total'];
    }
    if ($fallbackSql) {
        $fallbackResult = $conn->query($fallbackSql);
        if ($fallbackResult && ($row2 = $fallbackResult->fetch_assoc()) && isset($row2['total'])) {
            return (int)$row2['total'];
        }
    }
    return 0;
}

// Dashboard counters with safe fallbacks
$schedule_count = safeCount($conn, "SELECT COUNT(*) AS total FROM tbl_schedule");
$report_count   = safeCount($conn, "SELECT COUNT(*) AS total FROM tbl_report");
$programme_count = safeCount(
    $conn,
    "SELECT COUNT(*) AS total FROM tbl_programme",
    // Common alternative table names (fallbacks)
    "SELECT COUNT(*) AS total FROM programme"
);
$staff_active_count = safeCount(
    $conn,
    "SELECT COUNT(*) AS total FROM tbl_staff WHERE status='Active'",
    "SELECT COUNT(*) AS total FROM tbl_staff"
);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <meta name="theme-color" content="#2E7D32" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#111418" media="(prefers-color-scheme: dark)">
  <meta name="description" content="Admin Dashboard - Activity Management System" />
  <title>Admin Dashboard - AMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #2e7d32;
      --primary-600: #2E7D32;
      --primary-700: #1b5e20;
      --accent: #FF8C00;
      --bg: #F6F7F9;
      --surface: #FFFFFF;
      --text: #1E293B;
      --text-muted: #64748B;
      --border: #E2E8F0;
      --ring: rgba(46,125,50,0.35);
      --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.10), 0 8px 10px -6px rgba(0,0,0,0.10);
      --shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.06);
      --radius: 14px;
      --radius-sm: 10px;
      --transition: all 220ms ease;
      --container: 1200px;
    }

    [data-theme="dark"] {
      --primary: #4CAF50;
      --primary-600: #4CAF50;
      --primary-700: #43A047;
      --accent: #FFB74D;
      --bg: #0B0F14;
      --surface: #10151C;
      --text: #E6EDF3;
      --text-muted: #91A4B7;
      --border: #233041;
      --ring: rgba(76,175,80,0.35);
      --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.45), 0 8px 10px -6px rgba(0,0,0,0.40);
      --shadow: 0 10px 15px -3px rgba(0,0,0,0.35), 0 4px 6px -4px rgba(0,0,0,0.30);
    }

    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
      background: var(--bg);
      color: var(--text);
      line-height: 1.55;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    a { color: inherit; text-decoration: none; }

    .sr-only {
      position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
    }

    .top-bar {
      position: sticky; top: 0; inset-inline: 0;
      background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
      color: #fff;
      display: flex; align-items: center; gap: 1rem; justify-content: space-between;
      padding: clamp(0.75rem, 2vw, 1rem) clamp(1rem, 3vw, 1.25rem);
      box-shadow: var(--shadow);
      z-index: 50;
    }

    .brand { display: flex; align-items: center; gap: .75rem; }
    .brand .menu-toggle {
      display: inline-flex; align-items: center; justify-content: center;
      width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.2);
      cursor: pointer; transition: var(--transition);
    }
    .brand .menu-toggle:hover { background: rgba(255,255,255,0.18); }

    .brand h1 { font-size: clamp(1rem, 2.5vw, 1.25rem); font-weight: 600; margin: 0; }

    .actions { display: flex; align-items: center; gap: .5rem; }
    .action-btn {
      display: inline-flex; align-items: center; justify-content: center;
      height: 40px; padding: 0 .8rem; gap: .5rem; border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.25); color: #fff; background: transparent;
      cursor: pointer; transition: var(--transition);
    }
    .action-btn:hover { background: rgba(255,255,255,0.15); }

    .avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: linear-gradient(135deg, #A5D6A7, #66BB6A); display: grid; place-items: center;
      font-weight: 700; color: #0b2a0e; border: 2px solid rgba(255,255,255,0.35);
    }

    .layout { display: grid; grid-template-columns: 280px 1fr; min-height: calc(100vh - 64px); }
    /* Collapsed sidebar (desktop) */
    body[data-sidebar="collapsed"] .layout { grid-template-columns: 80px 1fr; }
    body[data-sidebar="collapsed"] .sidebar { padding: .75rem; }
    body[data-sidebar="collapsed"] .sidebar .section-title { display: none; }
    body[data-sidebar="collapsed"] .nav a { justify-content: center; padding: .6rem; }
    body[data-sidebar="collapsed"] .nav a span { display: none; }
    body[data-sidebar="collapsed"] .nav a i { color: var(--text); }

    .sidebar {
      position: sticky; top: 0; align-self: start;
      background: var(--surface); border-right: 1px solid var(--border);
      padding: 1rem; height: calc(100vh - 64px); overflow-y: auto;
    }

    .sidebar .section-title {
      color: var(--text-muted); font-size: .8rem; letter-spacing: .06em; text-transform: uppercase; margin: .25rem 0 .75rem;
    }

    .nav {
      display: grid; gap: .35rem;
    }

    .nav a {
      display: flex; align-items: center; gap: .75rem; padding: .75rem .8rem;
      border-radius: 10px; color: var(--text); border: 1px solid var(--border);
      background: linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.35));
      backdrop-filter: blur(6px);
      transition: var(--transition);
    }

    [data-theme="dark"] .nav a {
      background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
    }

    .nav a:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: transparent; }
    .nav a i { width: 20px; text-align: center; color: var(--primary); }

    .logout { margin-top: auto; }
    .logout a { border-color: #F87171; color: #B91C1C; }
    [data-theme="dark"] .logout a { color: #fecaca; border-color: #7f1d1d; }

    .content {
      padding: clamp(1rem, 3vw, 1.5rem);
    }

    .container { max-width: var(--container); margin: 0 auto; }

    .header {
      display: grid; gap: .75rem; margin-bottom: 1rem;
    }
    .breadcrumb { color: var(--text-muted); font-size: .9rem; }
    .page-title { font-size: clamp(1.25rem, 3vw, 1.6rem); font-weight: 700; }

    .overview {
      display: grid; grid-template-columns: repeat(12, 1fr); gap: 1rem; margin-top: .5rem;
    }

    .kpi {
      grid-column: span 3; background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 1rem; box-shadow: var(--shadow);
      display: grid; gap: .35rem; transition: var(--transition);
    }
    .kpi:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .kpi .kpi-top { display: flex; align-items: center; justify-content: space-between; color: var(--text-muted); font-size: .9rem; }
    .kpi .kpi-value { font-size: clamp(1.4rem, 4vw, 1.8rem); font-weight: 800; color: var(--primary); }

    .cards {
      display: grid; grid-template-columns: repeat(12, 1fr); gap: 1rem; margin-top: 1rem;
    }
    .card {
      grid-column: span 6; background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 1rem; box-shadow: var(--shadow);
      transition: var(--transition);
    }
    .card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .card h3 { margin: 0 0 .35rem; font-size: 1.05rem; }
    .card p { color: var(--text-muted); margin: 0 0 .75rem; }
    .card a.btn {
      display: inline-flex; align-items: center; gap: .5rem; background: var(--primary);
      color: #fff; padding: .6rem .9rem; border-radius: 10px; font-weight: 600; border: 0; transition: var(--transition);
    }
    .card a.btn:hover { filter: brightness(1.05); transform: translateY(-1px); box-shadow: 0 10px 18px -8px var(--ring); }

    .footer { color: var(--text-muted); font-size: .9rem; padding: 1.25rem 0; text-align: center; }

    /* Backdrop for mobile sidebar */
    .backdrop {
      position: fixed; inset: 0; background: rgba(0,0,0,.35); opacity: 0; visibility: hidden;
      transition: var(--transition); z-index: 40;
    }
    .backdrop.active { opacity: 1; visibility: visible; }

    /* Mobile */
    @media (max-width: 1024px) {
      .layout { grid-template-columns: 1fr; }
      .sidebar {
        position: fixed; inset: 64px 0 0 auto; width: min(86vw, 320px); height: calc(100vh - 64px);
        transform: translateX(110%); transition: var(--transition); z-index: 45;
      }
      .sidebar.active { transform: translateX(0); box-shadow: var(--shadow-lg); }
    }

    @media (max-width: 900px) {
      .kpi { grid-column: span 6; }
      .card { grid-column: span 12; }
    }

    @media (max-width: 520px) {
      .kpi { grid-column: span 12; }
      .brand h1 { display: none; }
      .action-btn span { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
      * { transition: none !important; animation: none !important; }
    }

    /* Focus ring */
    :focus-visible {
      outline: 2px solid transparent;
      box-shadow: 0 0 0 3px var(--ring), 0 1px 2px rgba(0,0,0,0.05);
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <a class="sr-only" href="#main">Skip to content</a>

  <div class="top-bar" role="banner">
    <div class="brand">
      <button id="menuToggle" class="menu-toggle" aria-label="Toggle sidebar" aria-controls="sidebar" aria-expanded="false">
        <i class="fas fa-bars"></i>
      </button>
      <h1>Admin Dashboard</h1>
    </div>
    <div class="actions">
      <button id="themeToggle" class="action-btn" aria-label="Toggle theme">
        <i class="fa-solid fa-moon"></i><span>Theme</span>
      </button>
      <div class="avatar" title="<?php echo htmlspecialchars($username); ?>" aria-label="User: <?php echo htmlspecialchars($username); ?>">
        <?php echo strtoupper(substr(htmlspecialchars($username), 0, 1)); ?>
      </div>
    </div>
  </div>

  <div class="layout">
    <aside id="sidebar" class="sidebar" aria-label="Sidebar navigation">
      <div class="section-title">Welcome, <?php echo htmlspecialchars($username); ?>!</div>
      <nav class="nav">
        <a href="add_pgm.php"><i class="fas fa-calendar-alt"></i> <span>Manage Programmes</span></a>
        <a href="add_staff.php"><i class="fas fa-users"></i> <span>Manage Staff</span></a>
        <a href="add_inst.php"><i class="fas fa-building"></i> <span>Manage Institution</span></a>
        <a href="schedule.php"><i class="fas fa-clipboard-list"></i> <span>Activity Schedule</span></a>
        <a href="attendance.php"><i class="fas fa-user-check"></i> <span>Attendance Records</span></a>
        <a href="diary.php"><i class="fas fa-book"></i> <span>Activity Diary</span></a>
        <a href="pgm_report.php"><i class="fas fa-file-lines"></i> <span>Programme Reports</span></a>
        <div class="logout">
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </div>
      </nav>
    </aside>

    <div class="content" id="main" role="main">
      <div class="container">
        <div class="header">
          <div class="breadcrumb">Home / Dashboard</div>
          <div class="page-title">Overview</div>
        </div>

        <section class="overview" aria-label="Dashboard statistics">
          <article class="kpi" aria-live="polite">
            <div class="kpi-top"><span>Total Programmes</span><i class="fa-solid fa-calendar-days"></i></div>
            <div class="kpi-value"><?php echo number_format($programme_count); ?></div>
          </article>
          <article class="kpi" aria-live="polite">
            <div class="kpi-top"><span>Active Staff</span><i class="fa-solid fa-users"></i></div>
            <div class="kpi-value"><?php echo number_format($staff_active_count); ?></div>
          </article>
          <article class="kpi" aria-live="polite">
            <div class="kpi-top"><span>Scheduled Activities</span><i class="fa-solid fa-list-check"></i></div>
            <div class="kpi-value"><?php echo number_format($schedule_count); ?></div>
          </article>
          <article class="kpi" aria-live="polite">
            <div class="kpi-top"><span>Reports Submitted</span><i class="fa-solid fa-file-check"></i></div>
            <div class="kpi-value"><?php echo number_format($report_count); ?></div>
          </article>
        </section>

        <section class="cards" aria-label="Quick actions">
          <div class="card">
            <h3>Activity Schedule</h3>
            <p>Review activities scheduled by the staff.</p>
            <a class="btn" href="schedule.php"><i class="fa-solid fa-arrow-right"></i> View Schedule</a>
          </div>
          <div class="card">
            <h3>Attendance Records</h3>
            <p>Track attendance details of the staff.</p>
            <a class="btn" href="attendance.php"><i class="fa-solid fa-arrow-right"></i> View Attendance</a>
          </div>
          <div class="card">
            <h3>View Activity Diary</h3>
            <p>Monitor and review staff activity logs.</p>
            <a class="btn" href="diary.php"><i class="fa-solid fa-arrow-right"></i> View Diary</a>
          </div>
          <div class="card">
            <h3>View Programme Reports</h3>
            <p>Analyze and download reports submitted by staff.</p>
            <a class="btn" href="pgm_report.php"><i class="fa-solid fa-arrow-right"></i> View Reports</a>
          </div>
        </section>

        <div class="footer">&copy; <?php echo date('Y'); ?> AMS • All rights reserved</div>
      </div>
    </div>
  </div>

  <div id="backdrop" class="backdrop" tabindex="-1" aria-hidden="true"></div>

  <script>
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const themeToggle = document.getElementById('themeToggle');
    const backdrop = document.getElementById('backdrop');

    // Sidebar toggle with state persisted (mobile overlay)
    function setSidebar(open) {
      const isMobile = window.matchMedia('(max-width: 1024px)').matches;
      if (isMobile) {
        sidebar.classList.toggle('active', open);
        backdrop.classList.toggle('active', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        document.documentElement.style.overflowY = open ? 'hidden' : '';
      }
      localStorage.setItem('ams.sidebar.open', open ? '1' : '0');
    }

    // Desktop collapse mode
    function setDesktopCollapsed(collapsed) {
      if (collapsed) {
        body.setAttribute('data-sidebar', 'collapsed');
      } else {
        body.removeAttribute('data-sidebar');
      }
      menuToggle.setAttribute('aria-expanded', String(!collapsed));
      localStorage.setItem('ams.sidebar.collapsed', collapsed ? '1' : '0');
    }

    function toggleSidebar() {
      const isMobile = window.matchMedia('(max-width: 1024px)').matches;
      if (isMobile) {
        const open = !sidebar.classList.contains('active');
        setSidebar(open);
      } else {
        const isCollapsed = body.getAttribute('data-sidebar') === 'collapsed';
        setDesktopCollapsed(!isCollapsed);
      }
    }

    // Theme toggle with system preference fallback
    function applyTheme(theme) {
      if (theme === 'dark') {
        body.setAttribute('data-theme', 'dark');
      } else {
        body.removeAttribute('data-theme');
      }
      localStorage.setItem('ams.theme', theme);
    }

    function initTheme() {
      const stored = localStorage.getItem('ams.theme');
      if (stored) {
        applyTheme(stored);
        return;
      }
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      applyTheme(prefersDark ? 'dark' : 'light');
    }

    function applyInitialSidebarState() {
      const isMobile = window.matchMedia('(max-width: 1024px)').matches;
      if (isMobile) {
        // Mobile: overlay closed by default
        setSidebar(false);
        body.removeAttribute('data-sidebar');
        menuToggle.setAttribute('aria-expanded', 'false');
      } else {
        // Desktop: restore collapsed state
        const collapsed = localStorage.getItem('ams.sidebar.collapsed') === '1';
        setDesktopCollapsed(collapsed);
        // Ensure overlay elements are reset
        sidebar.classList.remove('active');
        backdrop.classList.remove('active');
        document.documentElement.style.overflowY = '';
      }
    }

    // Event listeners
    menuToggle.addEventListener('click', toggleSidebar);
    backdrop.addEventListener('click', () => setSidebar(false));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setSidebar(false);
    });

    themeToggle.addEventListener('click', () => {
      const isDark = body.getAttribute('data-theme') === 'dark';
      applyTheme(isDark ? 'light' : 'dark');
    });

    // Initialize on load
    initTheme();
    applyInitialSidebarState();

    // Update on resize to ensure correct layout state
    window.addEventListener('resize', () => {
      const isMobile = window.matchMedia('(max-width: 1024px)').matches;
      if (isMobile) {
        // Switch to overlay mode
        body.removeAttribute('data-sidebar');
        const open = localStorage.getItem('ams.sidebar.open') === '1';
        setSidebar(open && false); // default to closed on enter mobile
      } else {
        // Switch to desktop collapse mode
        sidebar.classList.remove('active');
        backdrop.classList.remove('active');
        document.documentElement.style.overflowY = '';
        const collapsed = localStorage.getItem('ams.sidebar.collapsed') === '1';
        setDesktopCollapsed(collapsed);
      }
    });
  </script>
</body>
</html>