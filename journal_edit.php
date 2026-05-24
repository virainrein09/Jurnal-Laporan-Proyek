<?php
// admin/journal_edit.php
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM journals WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { header('Location: journals.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pic       = trim($_POST['pic_name'] ?? '');
    $proj      = trim($_POST['project_name'] ?? '');
    $client    = trim($_POST['client_name'] ?? '') ?: null;
    $orderNo   = trim($_POST['order_number'] ?? '');
    $orderDate = $_POST['order_date'] ?? '';
    $compDate  = $_POST['completion_date'] ?? '';
    $desc      = trim($_POST['description'] ?? '');
    $rtype     = $_POST['report_type'] ?? '';

    if (!$pic||!$proj||!$orderNo||!$orderDate||!$compDate) {
        $errors[] = 'Semua field wajib diisi.';
    }

    // Handle new evidence image
    $evidenceImg = $j['evidence_image'];
    if (!empty($_FILES['evidence_image']['name'])) {
        $uploaded = uploadImage($_FILES['evidence_image'], 'evidence');
        if ($uploaded === false) {
            $errors[] = 'Upload gambar gagal. Gunakan .jpg/.png maks 2MB.';
        } else {
            if ($evidenceImg && file_exists(UPLOADS_PATH . $evidenceImg)) @unlink(UPLOADS_PATH . $evidenceImg);
            $evidenceImg = $uploaded;
        }
    }
    // Delete image if requested
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($evidenceImg && file_exists(UPLOADS_PATH . $evidenceImg)) @unlink(UPLOADS_PATH . $evidenceImg);
        $evidenceImg = null;
    }

    if (empty($errors)) {
        $s = $db->prepare("UPDATE journals SET report_type=?,pic_name=?,project_name=?,client_name=?,order_number=?,order_date=?,completion_date=?,description=?,evidence_image=?,updated_at=NOW() WHERE id=?");
        $s->execute([$rtype,$pic,$proj,$client,$orderNo,$orderDate,$compDate,$desc,$evidenceImg,$id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Jurnal berhasil diperbarui!'];
        header('Location: journal_detail.php?id='.$id); exit;
    }
}

$tconf = [
    'client_project'      => ['🤝 Client Project',      'badge-blue'],
    'initiative_project'  => ['💡 Initiative Project',  'badge-green'],
    'school_project'      => ['🎓 School Project',       'badge-orange'],
    'independent_project' => ['⚡ Independent Project',  'badge-purple'],
];
$pageTitle = 'Edit Jurnal'; $activePage = 'journals'; $basePath = '../';
$logo = file_get_contents('../assets/logo_data_uri.txt');
include '../includes/header.php';
?>

<div class="flex min-h-screen">
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-[150] hidden lg:hidden backdrop-blur-sm"></div>
  <aside id="sidebar" class="sidebar">
    <div class="p-5 border-b border-white/[0.07] flex items-center gap-3">
      <img src="<?= $logo ?>" alt="" class="w-8 h-8 object-contain">
      <span class="font-bold text-white text-sm">Admin Panel</span>
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
        <nav class="text-sm flex items-center gap-2 hidden sm:flex">
          <a href="journals.php" class="text-slate-400 hover:text-white">Jurnal</a>
          <span class="text-slate-600">/</span>
          <span class="text-white">Edit</span>
        </nav>
      </div>
      <a href="journal_detail.php?id=<?= $id ?>" class="btn-ghost text-xs py-1.5 px-3">← Kembali</a>
    </header>

    <div class="p-5 lg:p-7 max-w-3xl mx-auto animate-fade-in space-y-5">
      <?php if (!empty($errors)): ?>
        <div id="flash-data" data-type="error" data-msg="<?= htmlspecialchars(implode(' ', $errors)) ?>" class="hidden"></div>
      <?php endif; ?>

      <div>
        <h1 class="text-2xl font-bold text-white">Edit Jurnal</h1>
        <p class="text-slate-400 text-sm mt-1">Perbarui data jurnal <span class="text-white font-medium"><?= htmlspecialchars($j['project_name'] ?? '—') ?></span></p>
      </div>

      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <!-- Reporter info (read-only) -->
        <div class="glass rounded-2xl p-5 space-y-4">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Info Pelapor (read-only)</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-600 text-slate-500 mb-1.5">Nama</label>
              <div class="form-input opacity-60 cursor-not-allowed"><?= htmlspecialchars($j['name'] ?? '—') ?></div>
            </div>
            <div>
              <label class="block text-xs font-600 text-slate-500 mb-1.5">Kelas</label>
              <div class="form-input opacity-60 cursor-not-allowed"><?= htmlspecialchars($j['class'] ?? '—') ?></div>
            </div>
          </div>
        </div>

        <!-- Type -->
        <div class="glass rounded-2xl p-5">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-white/[0.07] pb-3 mb-4">Tipe Laporan</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php foreach ($tconf as $val => [$lbl,$cls]): ?>
            <label class="cursor-pointer">
              <input type="radio" name="report_type" value="<?= $val ?>" class="sr-only peer" <?= $j['report_type']===$val?'checked':'' ?>>
              <div class="p-3 rounded-xl border-2 border-white/[0.08] peer-checked:border-blue-500 peer-checked:bg-blue-600/15 transition text-center text-sm font-medium text-slate-300">
                <?= $lbl ?>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Project details -->
        <div class="glass rounded-2xl p-5 space-y-4">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-white/[0.07] pb-3">Detail Proyek</h3>
          <div>
            <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Nama PIC</label>
            <input type="text" name="pic_name" class="form-input" value="<?= htmlspecialchars($j['pic_name'] ?? '') ?>" required>
          </div>
          <div>
            <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Nama Proyek</label>
            <input type="text" name="project_name" class="form-input" value="<?= htmlspecialchars($j['project_name'] ?? '') ?>" required>
          </div>
          <div id="client-field" style="display:<?= in_array($j['report_type'],['client_project','school_project'])?'block':'none' ?>">
            <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Nama Klien</label>
            <input type="text" name="client_name" id="client-input" class="form-input" value="<?= htmlspecialchars($j['client_name'] ?? '') ?>">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">No. Order</label>
              <input type="text" name="order_number" class="form-input font-mono" value="<?= htmlspecialchars($j['order_number'] ?? '') ?>" required>
            </div>
            <div>
              <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Tgl. Order</label>
              <input type="date" name="order_date" class="form-input" value="<?= $j['order_date'] ?? '' ?>" required>
            </div>
            <div>
              <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Tgl. Selesai</label>
              <input type="date" name="completion_date" class="form-input" value="<?= $j['completion_date'] ?? '' ?>" required>
            </div>
          </div>
          <div>
            <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Deskripsi</label>
            <textarea name="description" rows="4" class="form-input resize-none"><?= htmlspecialchars($j['description'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Evidence image -->
        <div class="glass rounded-2xl p-5">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-white/[0.07] pb-3 mb-4">Bukti Gambar</h3>
          <?php if (!empty($j['evidence_image'])): ?>
          <div class="mb-4 p-3 bg-slate-800/40 rounded-xl flex items-center gap-4">
            <img src="<?= htmlspecialchars(UPLOADS_URL . $j['evidence_image']) ?>" class="w-16 h-16 rounded-lg object-cover">
            <div class="flex-1">
              <p class="text-sm text-white font-medium">Gambar saat ini</p>
              <p class="text-xs text-slate-500"><?= $j['evidence_image'] ?></p>
            </div>
            <label class="flex items-center gap-2 text-xs text-red-400 cursor-pointer">
              <input type="checkbox" name="remove_image" value="1" class="rounded">
              Hapus gambar
            </label>
          </div>
          <?php endif; ?>
          <div id="upload-area" class="upload-area">
            <input type="file" id="evidence-file" name="evidence_image" accept=".jpg,.jpeg,.png" class="hidden">
            <img id="evidence-preview" class="hidden h-28 mx-auto rounded-lg object-cover mb-3">
            <p class="text-sm text-slate-400">Klik atau drag foto baru</p>
            <p class="text-xs text-slate-600 mt-1">JPG, PNG — Maks. 2MB</p>
          </div>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn-primary flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Perubahan
          </button>
          <a href="journal_detail.php?id=<?= $id ?>" class="btn-ghost">Batal</a>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initUploadPreview('evidence-file', 'evidence-preview', 'upload-area');
  document.querySelectorAll('input[name="report_type"]').forEach(r => {
    r.addEventListener('change', () => {
      const show = ['client_project','school_project'].includes(r.value);
      document.getElementById('client-field').style.display = show ? 'block' : 'none';
      document.getElementById('client-input').required = show;
    });
  });
});
</script>
<?php include '../includes/footer.php'; ?>
