<?php
// admin/journals.php
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$db = getDB();

$search = trim($_GET['q'] ?? '');
$type   = trim($_GET['type'] ?? '');

$sql  = "SELECT j.*, u.class as uclass FROM journals j LEFT JOIN users u ON j.user_id=u.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (j.project_name LIKE ? OR j.name LIKE ? OR j.order_number LIKE ? OR j.pic_name LIKE ?)"; $params = array_fill(0,4,"%$search%"); }
if ($type)   { $sql .= " AND j.report_type=?"; $params[] = $type; }
$sql .= " ORDER BY j.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$journals = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$pageTitle = 'Semua Jurnal'; $activePage = 'journals'; $basePath = '../';
include '../includes/header.php';
$logo = file_get_contents('../assets/logo_data_uri.txt');
$tconf = ['client_project'=>['🤝 Client','badge-blue'],'initiative_project'=>['💡 Initiative','badge-green'],'school_project'=>['🎓 School','badge-orange'],'independent_project'=>['⚡ Independent','badge-purple']];
?>
<div class="flex min-h-screen">
  <?php
  // inline minimal sidebar for this page
  echo '<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-[150] hidden lg:hidden backdrop-blur-sm"></div>';
  ?>
  <aside id="sidebar" class="sidebar">
    <div class="p-5 border-b border-white/[0.07] flex items-center justify-between">
      <div class="flex items-center gap-3"><img src="<?= $logo ?>" alt="" class="w-8 h-8 object-contain"><span class="font-bold text-white text-sm">Admin Panel</span></div>
      <button id="sidebar-close" class="text-slate-500 hover:text-white transition lg:hidden"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>
    <nav class="p-3 space-y-1 mt-2">
      <a href="dashboard.php" class="sidebar-link">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard
      </a>
      <a href="journals.php" class="sidebar-link active">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Semua Jurnal
      </a>
      <a href="users.php" class="sidebar-link">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Pengguna
      </a>
    </nav>
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/[0.07]">
      <a href="logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg> Logout</a>
    </div>
  </aside>

  <main class="flex-1 lg:ml-60">
    <header class="navbar flex items-center justify-between px-5 h-14">
      <div class="flex items-center gap-3">
        <button id="sidebar-open" class="text-slate-400 hover:text-white transition lg:hidden"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <span class="text-sm text-slate-400 hidden sm:block">Admin <span class="text-slate-600">/</span> Semua Jurnal</span>
      </div>
      <span class="badge badge-blue"><?= count($journals) ?> hasil</span>
    </header>

    <div class="p-5 lg:p-7 space-y-5 animate-fade-in">
      <?php if ($flash): ?><div id="flash-data" data-type="<?= $flash['type'] ?>" data-msg="<?= htmlspecialchars($flash['msg']) ?>" class="hidden"></div><?php endif; ?>

      <div>
        <h1 class="text-2xl font-bold text-white">Semua Jurnal</h1>
        <p class="text-slate-400 text-sm mt-1">Kelola seluruh laporan proyek siswa</p>
      </div>

      <!-- Filters -->
      <form method="GET" class="flex gap-3 flex-wrap">
        <div class="relative flex-1 min-w-[200px]">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"/></svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-input pl-9" placeholder="Cari proyek, user, order…">
        </div>
        <select name="type" class="form-input w-auto">
          <option value="">Semua Tipe</option>
          <?php foreach ($tconf as $v => [$l,$c]): ?>
          <option value="<?= $v ?>" <?= $type===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary px-5">Filter</button>
        <?php if ($search||$type): ?><a href="journals.php" class="btn-ghost">Reset</a><?php endif; ?>
      </form>

      <!-- Table -->
      <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead><tr>
              <th>#</th><th>Proyek</th><th>User</th><th>Tipe</th>
              <th>No. Order</th><th>Tgl. Order</th><th>Tgl. Selesai</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (empty($journals)): ?>
              <tr><td colspan="8" class="text-center text-slate-500 py-12">Tidak ada jurnal ditemukan.</td></tr>
            <?php else: foreach ($journals as $i => $j):
              [$tlabel,$tcls] = $tconf[$j['report_type']] ?? ['📋','badge-blue'];
            ?>
            <tr>
              <td class="text-slate-500 w-8"><?= $i+1 ?></td>
              <td class="font-medium text-white max-w-[200px]"><div class="truncate"><?= htmlspecialchars($j['project_name'] ?? '-') ?></div></td>
              <td><div class="text-sm text-white"><?= htmlspecialchars($j['name'] ?? '-') ?></div><div class="text-xs text-slate-500"><?= htmlspecialchars($j['uclass'] ?? '-') ?></div></td>
              <td><span class="badge <?= $tcls ?>"><?= $tlabel ?></span></td>
              <td><code class="text-xs bg-slate-800/60 px-2 py-0.5 rounded text-slate-300"><?= htmlspecialchars($j['order_number'] ?? '—') ?></code></td>
              <td class="text-slate-400 text-sm whitespace-nowrap"><?= $j['order_date'] ?? '—' ?></td>
              <td class="text-slate-400 text-sm whitespace-nowrap"><?= $j['completion_date'] ?? '—' ?></td>
              <td>
                <div class="flex gap-1.5">
                  <a href="journal_detail.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2 text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> View
                  </a>
                  <a href="journal_edit.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2 text-xs text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit
                  </a>
                  <a href="export_word.php?id=<?= $j['id'] ?>" class="btn-ghost py-1 px-2 text-xs text-purple-400">📄</a>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include '../includes/footer.php'; ?>
