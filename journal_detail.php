<?php
// admin/journal_detail.php
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT j.*, u.class as user_class_ref, u.avatar as user_avatar FROM journals j LEFT JOIN users u ON j.user_id=u.id WHERE j.id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { header('Location: journals.php'); exit; }

$tconf = [
    'client_project'      => ['🤝 Client Project',      'badge-blue'],
    'initiative_project'  => ['💡 Initiative Project',  'badge-green'],
    'school_project'      => ['🎓 School Project',       'badge-orange'],
    'independent_project' => ['⚡ Independent Project',  'badge-purple'],
];
[$tlabel, $tcls] = $tconf[$j['report_type']] ?? ['📋', 'badge-blue'];
$detailUrl = USER_URL . '/user/journal_detail.php?id=' . $j['id'];
$qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=160x160&chl=' . urlencode($detailUrl) . '&choe=UTF-8';

$pageTitle  = 'Detail Jurnal Admin';
$activePage = 'journals';
$basePath   = '../';
$logo = file_get_contents('../assets/logo_data_uri.txt');
include '../includes/header.php';
?>

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-[150] hidden lg:hidden backdrop-blur-sm"></div>
  <aside id="sidebar" class="sidebar">
    <div class="p-5 border-b border-white/[0.07] flex items-center justify-between">
      <div class="flex items-center gap-3"><img src="<?= $logo ?>" alt="" class="w-8 h-8 object-contain"><span class="font-bold text-white text-sm">Admin Panel</span></div>
      <button id="sidebar-close" class="text-slate-500 hover:text-white transition lg:hidden"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>
    <nav class="p-3 space-y-1 mt-2">
      <a href="dashboard.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Dashboard</a>
      <a href="journals.php" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Semua Jurnal</a>
      <a href="users.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Pengguna</a>
    </nav>
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/[0.07]">
      <a href="logout.php" class="sidebar-link text-red-400 hover:bg-red-500/10"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg> Logout</a>
    </div>
  </aside>

  <main class="flex-1 lg:ml-60">
    <header class="navbar flex items-center justify-between px-5 h-14">
      <div class="flex items-center gap-3">
        <button id="sidebar-open" class="text-slate-400 hover:text-white transition lg:hidden"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <nav class="text-sm flex items-center gap-2">
          <a href="journals.php" class="text-slate-400 hover:text-white transition">Jurnal</a>
          <span class="text-slate-600">/</span>
          <span class="text-white font-medium truncate max-w-xs"><?= htmlspecialchars($j['project_name'] ?? '—') ?></span>
        </nav>
      </div>
      <div class="flex gap-2">
        <a href="journal_edit.php?id=<?= $j['id'] ?>" class="btn-ghost text-xs py-1.5 px-3 text-blue-400">✏️ Edit</a>
        <a href="export_word.php?id=<?= $j['id'] ?>" class="btn-ghost text-xs py-1.5 px-3 text-purple-400">📄 Word</a>
      </div>
    </header>

    <div class="p-5 lg:p-7 max-w-3xl mx-auto animate-fade-in space-y-5">
      <a href="journals.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Jurnal
      </a>

      <!-- Header card -->
      <div class="glass rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
          <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap">
              <span class="badge <?= $tcls ?>"><?= $tlabel ?></span>
              <code class="text-xs bg-slate-800/60 px-2 py-1 rounded text-slate-300"><?= htmlspecialchars($j['order_number'] ?? '—') ?></code>
            </div>
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($j['project_name'] ?? '—') ?></h1>
          </div>
          <!-- User info pill -->
          <div class="flex items-center gap-2 bg-slate-800/50 rounded-xl px-3 py-2">
            <?php if (!empty($j['user_avatar'])): ?>
              <img src="<?= htmlspecialchars(UPLOADS_URL . $j['user_avatar']) ?>" class="w-7 h-7 rounded-full object-cover">
            <?php else: ?>
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-xs font-bold text-white">
                <?= strtoupper(substr($j['name'] ?? 'U', 0, 1)) ?>
              </div>
            <?php endif; ?>
            <div>
              <p class="text-xs font-semibold text-white"><?= htmlspecialchars($j['name'] ?? '—') ?></p>
              <p class="text-xs text-slate-500"><?= htmlspecialchars($j['class'] ?? $j['user_class_ref'] ?? '—') ?></p>
            </div>
          </div>
        </div>

        <!-- Data grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <?php
          $fields = [
              ['PIC',            $j['pic_name'] ?? '—'],
              ['Tgl. Order',     $j['order_date'] ?? '—'],
              ['Tgl. Selesai',   $j['completion_date'] ?? '—'],
              ['Dibuat',         date('d M Y H:i', strtotime($j['created_at']))],
          ];
          if (!empty($j['client_name'])) array_unshift($fields, ['Klien', $j['client_name']]);
          if (!empty($j['updated_at'])) $fields[] = ['Diperbarui', date('d M Y H:i', strtotime($j['updated_at']))];
          foreach ($fields as [$label, $val]):
          ?>
          <div class="bg-slate-800/40 rounded-xl p-3">
            <div class="text-xs text-slate-500 mb-1 uppercase tracking-wider"><?= $label ?></div>
            <div class="text-sm text-white font-medium"><?= htmlspecialchars($val) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Description -->
      <?php if (!empty($j['description'])): ?>
      <div class="glass rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Deskripsi</h3>
        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($j['description']) ?></p>
      </div>
      <?php endif; ?>

      <!-- Evidence image -->
      <?php if (!empty($j['evidence_image'])): ?>
      <div class="glass rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Bukti / Dokumentasi</h3>
        <a href="<?= htmlspecialchars(UPLOADS_URL . $j['evidence_image']) ?>" target="_blank">
          <img src="<?= htmlspecialchars(UPLOADS_URL . $j['evidence_image']) ?>"
               alt="Bukti"
               class="rounded-xl max-h-80 w-full object-cover border border-white/[0.07] hover:border-blue-500/50 transition">
        </a>
      </div>
      <?php endif; ?>

      <!-- QR + Actions -->
      <div class="glass rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">QR Code & Aksi</h3>
        <div class="flex items-center gap-5 flex-wrap">
          <div class="bg-white p-2 rounded-xl">
            <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code" class="w-32 h-32">
          </div>
          <div class="flex-1">
            <p class="text-xs text-slate-500 break-all mb-4"><?= htmlspecialchars($detailUrl) ?></p>
            <div class="flex gap-2 flex-wrap">
              <a href="journal_edit.php?id=<?= $j['id'] ?>" class="btn-ghost py-2 px-4 text-sm text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Jurnal
              </a>
              <a href="export_word.php?id=<?= $j['id'] ?>" class="btn-primary py-2 px-4 text-sm" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
                📄 Export Word
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
