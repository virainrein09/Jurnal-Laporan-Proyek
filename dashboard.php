<?php
// admin/dashboard.php
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$db = getDB();

// Stats
$totalReports = $db->query("SELECT COUNT(*) FROM journals")->fetchColumn();
$totalUsers   = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$todayReports = $db->query("SELECT COUNT(*) FROM journals WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$thisMonth    = $db->query("SELECT COUNT(*) FROM journals WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

// By type
$typeStats = $db->query("SELECT report_type, COUNT(*) as c FROM journals GROUP BY report_type")->fetchAll();
$typeMap   = [];
foreach ($typeStats as $r) $typeMap[$r['report_type']] = $r['c'];

// Recent journals
$recent = $db->query("SELECT j.*, u.class as user_class FROM journals j LEFT JOIN users u ON j.user_id=u.id ORDER BY j.created_at DESC LIMIT 8")->fetchAll();

// Monthly trend (last 6 months)
$trend = $db->query("SELECT DATE_FORMAT(created_at,'%b %Y') as month, COUNT(*) as c FROM journals WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY MIN(created_at)")->fetchAll();

$pageTitle  = 'Admin Dashboard';
$activePage = 'dashboard';
$basePath   = '../';
include '../includes/header.php';
$logo = file_get_contents('../assets/logo_data_uri.txt');
?>
<!-- Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-[150] hidden lg:hidden backdrop-blur-sm"></div>
  <aside id="sidebar" class="sidebar">
    <div class="p-5 border-b border-white/[0.07] flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="<?= $logo ?>" alt="Logo" class="w-8 h-8 object-contain">
        <span class="font-bold text-white text-sm">Admin Panel</span>
      </div>
      <button id="sidebar-close" class="text-slate-500 hover:text-white transition lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <nav class="p-3 space-y-1 mt-2">
      <a href="dashboard.php" class="sidebar-link <?= $activePage==='dashboard'?'active':'' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>
      <a href="journals.php" class="sidebar-link <?= $activePage==='journals'?'active':'' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Semua Jurnal
      </a>
      <a href="users.php" class="sidebar-link <?= $activePage==='users'?'active':'' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Pengguna
      </a>
    </nav>
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/[0.07]">
      <a href="logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Logout
      </a>
    </div>
  </aside>

  <!-- Main -->
  <main class="flex-1 lg:ml-60">
    <!-- Top bar -->
    <header class="navbar flex items-center justify-between px-5 h-14">
      <div class="flex items-center gap-3">
        <button id="sidebar-open" class="text-slate-400 hover:text-white transition lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="text-sm font-semibold text-white hidden sm:block">Dashboard Admin</span>
      </div>
      <div class="relative">
        <button id="profile-trigger" class="flex items-center gap-2 hover:bg-white/5 px-2.5 py-1.5 rounded-lg transition">
          <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center text-xs font-bold text-white">
            <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
          </div>
          <span class="text-xs font-medium text-white hidden sm:block"><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></span>
        </button>
        <div id="profile-dropdown" class="dropdown-menu hidden">
          <a href="logout.php" class="dropdown-item danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Logout
          </a>
        </div>
      </div>
    </header>

    <div class="p-5 lg:p-7 space-y-6 animate-fade-in">
      <div>
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-slate-400 text-sm mt-1">Ringkasan data jurnal proyek PPLG</p>
      </div>

      <!-- Bento Stats — clickable -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass rounded-2xl stat-card col-span-1" onclick="showModal('modal-reports')" title="Klik untuk detail">
          <div class="w-10 h-10 rounded-xl bg-blue-600/20 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div class="text-3xl font-bold text-white" id="cnt-reports"><?= $totalReports ?></div>
          <div class="text-slate-400 text-sm mt-1">Total Laporan</div>
          <div class="text-xs text-blue-400 mt-2">Klik untuk detail →</div>
        </div>
        <div class="glass rounded-2xl stat-card" onclick="showModal('modal-users')" title="Klik untuk detail">
          <div class="w-10 h-10 rounded-xl bg-green-600/20 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div class="text-3xl font-bold text-white"><?= $totalUsers ?></div>
          <div class="text-slate-400 text-sm mt-1">Total User</div>
          <div class="text-xs text-green-400 mt-2">Klik untuk detail →</div>
        </div>
        <div class="glass rounded-2xl stat-card">
          <div class="w-10 h-10 rounded-xl bg-orange-600/20 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <div class="text-3xl font-bold text-white"><?= $todayReports ?></div>
          <div class="text-slate-400 text-sm mt-1">Hari Ini</div>
        </div>
        <div class="glass rounded-2xl stat-card">
          <div class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <div class="text-3xl font-bold text-white"><?= $thisMonth ?></div>
          <div class="text-slate-400 text-sm mt-1">Bulan Ini</div>
        </div>
      </div>

      <!-- Charts Bento -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Donut chart (2 cols wide) -->
        <div class="glass rounded-2xl p-5 lg:col-span-1">
          <h3 class="text-sm font-semibold text-white mb-1">Distribusi Tipe Proyek</h3>
          <p class="text-xs text-slate-500 mb-4">Berdasarkan semua laporan</p>
          <div id="donut-chart" style="height:240px"></div>
        </div>
        <!-- Bar chart -->
        <div class="glass rounded-2xl p-5 lg:col-span-2">
          <h3 class="text-sm font-semibold text-white mb-1">Tren Laporan 6 Bulan</h3>
          <p class="text-xs text-slate-500 mb-4">Jumlah jurnal per bulan</p>
          <div id="bar-chart" style="height:240px"></div>
        </div>
      </div>

      <!-- Recent journals table -->
      <div class="glass rounded-2xl p-5">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-base font-semibold text-white">Jurnal Terbaru</h2>
          <a href="journals.php" class="btn-ghost text-xs py-1.5 px-3">Semua Jurnal</a>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead><tr>
              <th>Proyek</th><th>User</th><th>Tipe</th><th>No. Order</th><th>Tanggal</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php
            $tconf = ['client_project'=>['🤝','badge-blue'],'initiative_project'=>['💡','badge-green'],'school_project'=>['🎓','badge-orange'],'independent_project'=>['⚡','badge-purple']];
            foreach ($recent as $j):
              $tc = $tconf[$j['report_type']] ?? ['📋','badge-blue'];
            ?>
            <tr>
              <td class="font-medium text-white max-w-[200px]"><div class="truncate-2"><?= htmlspecialchars($j['project_name'] ?? $j['title'] ?? '-') ?></div></td>
              <td><div class="text-sm text-white"><?= htmlspecialchars($j['name'] ?? '-') ?></div><div class="text-xs text-slate-500"><?= htmlspecialchars($j['user_class'] ?? '-') ?></div></td>
              <td><span class="badge <?= $tc[1] ?>"><?= $tc[0] ?></span></td>
              <td><code class="text-xs bg-slate-800/60 px-2 py-0.5 rounded text-slate-300"><?= htmlspecialchars($j['order_number'] ?? '—') ?></code></td>
              <td class="text-slate-400 text-sm whitespace-nowrap"><?= date('d M Y', strtotime($j['created_at'])) ?></td>
              <td>
                <div class="flex gap-2">
                  <a href="journal_detail.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2.5 text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View
                  </a>
                  <a href="journal_edit.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2.5 text-xs text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                  </a>
                  <a href="export_word.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2.5 text-xs text-purple-400">
                    📄 Word
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Reports Detail -->
<div id="modal-reports" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-reports')">
  <div class="modal-box">
    <div class="flex items-center justify-between p-6 border-b border-white/[0.07]">
      <h3 class="font-bold text-white text-lg">Daftar Semua Laporan (<?= $totalReports ?>)</h3>
      <button onclick="closeModal('modal-reports')" class="text-slate-500 hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-6">
      <input type="text" id="search-reports" class="form-input mb-4" placeholder="Cari laporan...">
      <div class="overflow-x-auto max-h-80">
        <table class="data-table">
          <thead><tr><th>#</th><th>Proyek</th><th>User</th><th>Tipe</th><th>No.Order</th></tr></thead>
          <tbody id="tbody-reports">
          <?php
          $all = $db->query("SELECT j.*, u.name as uname FROM journals j LEFT JOIN users u ON j.user_id=u.id ORDER BY j.created_at DESC")->fetchAll();
          foreach ($all as $i => $r):
            $tc = $tconf[$r['report_type']] ?? ['📋','badge-blue'];
          ?>
          <tr>
            <td class="text-slate-500"><?= $i+1 ?></td>
            <td class="font-medium text-white"><?= htmlspecialchars($r['project_name'] ?? '-') ?></td>
            <td class="text-slate-400"><?= htmlspecialchars($r['uname'] ?? '-') ?></td>
            <td><span class="badge <?= $tc[1] ?>"><?= $tc[0] ?></span></td>
            <td><code class="text-xs text-slate-400"><?= htmlspecialchars($r['order_number'] ?? '-') ?></code></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Users Detail -->
<div id="modal-users" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-users')">
  <div class="modal-box">
    <div class="flex items-center justify-between p-6 border-b border-white/[0.07]">
      <h3 class="font-bold text-white text-lg">Daftar Pengguna (<?= $totalUsers ?>)</h3>
      <button onclick="closeModal('modal-users')" class="text-slate-500 hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-6">
      <input type="text" id="search-users" class="form-input mb-4" placeholder="Cari pengguna...">
      <div class="overflow-x-auto max-h-80">
        <table class="data-table">
          <thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th>Jurnal</th><th>Bergabung</th></tr></thead>
          <tbody id="tbody-users">
          <?php
          $users = $db->query("SELECT u.*, COUNT(j.id) as jcount FROM users u LEFT JOIN journals j ON j.user_id=u.id GROUP BY u.id ORDER BY u.created_at DESC")->fetchAll();
          foreach ($users as $i => $usr):
          ?>
          <tr>
            <td class="text-slate-500"><?= $i+1 ?></td>
            <td class="font-medium text-white"><?= htmlspecialchars($usr['name']) ?></td>
            <td class="text-slate-400"><?= htmlspecialchars($usr['class']) ?></td>
            <td><span class="badge badge-blue"><?= $usr['jcount'] ?> jurnal</span></td>
            <td class="text-slate-500 text-sm"><?= date('d M Y', strtotime($usr['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Google Charts
google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
  // Donut
  const donutData = google.visualization.arrayToDataTable([
    ['Tipe', 'Jumlah'],
    <?php foreach ($typeStats as $r): ?>
    ['<?= addslashes($r['report_type']) ?>', <?= (int)$r['c'] ?>],
    <?php endforeach; ?>
  ]);
  const donutOpt = {
    pieHole: 0.55,
    backgroundColor: 'transparent',
    legend: { position: 'bottom', textStyle: { color: '#94a3b8', fontSize: 11 } },
    pieSliceText: 'none',
    chartArea: { width: '95%', height: '82%' },
    colors: ['#3b82f6','#22c55e','#f97316','#a855f7'],
    tooltip: { textStyle: { color: '#0f172a' } }
  };
  new google.visualization.PieChart(document.getElementById('donut-chart')).draw(donutData, donutOpt);

  // Bar
  const barData = google.visualization.arrayToDataTable([
    ['Bulan', 'Laporan', { role: 'style' }],
    <?php foreach ($trend as $r): ?>
    ['<?= addslashes($r['month']) ?>', <?= (int)$r['c'] ?>, '#3b82f6'],
    <?php endforeach; ?>
  ]);
  const barOpt = {
    backgroundColor: 'transparent',
    legend: { position: 'none' },
    bar: { groupWidth: '60%' },
    chartArea: { width: '88%', height: '80%' },
    hAxis: { textStyle: { color: '#64748b', fontSize: 11 } },
    vAxis: { textStyle: { color: '#64748b', fontSize: 11 }, gridlines: { color: '#1e293b' }, baselineColor: '#1e293b' },
    colors: ['#3b82f6']
  };
  new google.visualization.ColumnChart(document.getElementById('bar-chart')).draw(barData, barOpt);
}

document.addEventListener('DOMContentLoaded', () => {
  initTableSearch('search-reports', 'tbody-reports');
  initTableSearch('search-users', 'tbody-users');

  // Count-up animation
  const el = document.getElementById('cnt-reports');
  if (el) countUp(el, <?= $totalReports ?>, 1200);
});
</script>

<?php include '../includes/footer.php'; ?>
