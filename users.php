<?php
// admin/users.php
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$db = getDB();

$search = trim($_GET['q'] ?? '');
$sql    = "SELECT u.*, COUNT(j.id) as jcount FROM users u LEFT JOIN journals j ON j.user_id=u.id";
$params = [];
if ($search) { $sql .= " WHERE (u.name LIKE ? OR u.class LIKE ?)"; $params = ["%$search%","%$search%"]; }
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Pengguna'; $activePage = 'users'; $basePath = '../';
$logo = file_get_contents('../assets/logo_data_uri.txt');
include '../includes/header.php';
?>
<div class="flex min-h-screen">
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-[150] hidden lg:hidden backdrop-blur-sm"></div>
  <aside id="sidebar" class="sidebar">
    <div class="p-5 border-b border-white/[0.07] flex items-center gap-3"><img src="<?= $logo ?>" alt="" class="w-8 h-8 object-contain"><span class="font-bold text-white text-sm">Admin Panel</span></div>
    <nav class="p-3 space-y-1 mt-2">
      <a href="dashboard.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard</a>
      <a href="journals.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Semua Jurnal</a>
      <a href="users.php" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Pengguna</a>
    </nav>
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/[0.07]"><a href="logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg> Logout</a></div>
  </aside>

  <main class="flex-1 lg:ml-60">
    <header class="navbar flex items-center justify-between px-5 h-14">
      <div class="flex items-center gap-3">
        <button id="sidebar-open" class="text-slate-400 hover:text-white transition lg:hidden"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <span class="text-sm text-slate-400 hidden sm:block">Admin / Pengguna</span>
      </div>
      <span class="badge badge-green"><?= count($users) ?> pengguna</span>
    </header>

    <div class="p-5 lg:p-7 space-y-5 animate-fade-in">
      <div>
        <h1 class="text-2xl font-bold text-white">Daftar Pengguna</h1>
        <p class="text-slate-400 text-sm mt-1">Semua akun siswa yang terdaftar</p>
      </div>

      <form method="GET" class="flex gap-3">
        <div class="relative flex-1 max-w-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35"/></svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-input pl-9" placeholder="Cari nama atau kelas…">
        </div>
        <button type="submit" class="btn-primary px-5">Cari</button>
        <?php if ($search): ?><a href="users.php" class="btn-ghost">Reset</a><?php endif; ?>
      </form>

      <!-- User cards grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($users)): ?>
          <div class="col-span-full glass rounded-2xl p-12 text-center text-slate-500">Tidak ada pengguna ditemukan.</div>
        <?php else: foreach ($users as $i => $u): ?>
        <div class="glass rounded-2xl p-5 hover:border-slate-600/50 transition">
          <div class="flex items-center gap-3 mb-4">
            <?php if (!empty($u['avatar'])): ?>
              <img src="<?= htmlspecialchars(UPLOADS_URL . $u['avatar']) ?>" class="w-12 h-12 rounded-full object-cover ring-2 ring-blue-500/30">
            <?php else: ?>
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-lg font-bold text-white flex-shrink-0">
                <?= strtoupper(substr($u['name'], 0, 1)) ?>
              </div>
            <?php endif; ?>
            <div class="min-w-0">
              <p class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($u['name']) ?></p>
              <p class="text-slate-500 text-xs"><?= htmlspecialchars($u['class']) ?></p>
            </div>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">Bergabung <?= date('d M Y', strtotime($u['created_at'])) ?></span>
            <span class="badge badge-blue"><?= $u['jcount'] ?> jurnal</span>
          </div>
          <div class="mt-3 pt-3 border-t border-white/[0.05]">
            <a href="journals.php?q=<?= urlencode($u['name']) ?>" class="btn-ghost py-1.5 px-3 text-xs w-full text-center">
              Lihat Jurnal →
            </a>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </main>
</div>
<?php include '../includes/footer.php'; ?>
