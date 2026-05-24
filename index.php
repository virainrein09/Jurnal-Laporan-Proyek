<?php
// admin/index.php — Admin Dashboard
session_start();
require_once '../includes/db.php';

$isLoggedIn = isset($_SESSION['admin_id']);
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db = getDB();

    if ($action === 'login') {
        $name = trim($_POST['name'] ?? '');
        $pass = $_POST['password'] ?? '';
        $s = $db->prepare("SELECT * FROM admins WHERE name=? LIMIT 1");
        $s->execute([$name]);
        $admin = $s->fetch();
        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_name']  = $admin['name'];
            $_SESSION['admin_class'] = $admin['class'];
            header('Location: dashboard.php'); exit;
        }
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Nama atau password salah.'];
        header('Location: index.php'); exit;
    }

    if ($action === 'register') {
        $name  = trim($_POST['name'] ?? '');
        $class = trim($_POST['class'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if ($pass !== $pass2) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Password tidak cocok.'];
            header('Location: index.php?mode=register'); exit;
        }
        $chk = $db->prepare("SELECT id FROM admins WHERE name=? LIMIT 1");
        $chk->execute([$name]);
        if ($chk->fetch()) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Nama admin sudah digunakan.'];
            header('Location: index.php?mode=register'); exit;
        }
        $ins = $db->prepare("INSERT INTO admins (name,class,password,created_at) VALUES (?,?,?,NOW())");
        $ins->execute([$name, $class, password_hash($pass, PASSWORD_DEFAULT)]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Akun admin dibuat! Silakan login.'];
        header('Location: index.php'); exit;
    }
}

if ($isLoggedIn) { header('Location: dashboard.php'); exit; }

// Flash
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$pageTitle = 'Admin Login';
$basePath  = '../';
include '../includes/header.php';
$logo = file_get_contents('../assets/logo_data_uri.txt');
?>

<div class="min-h-screen flex">
  <!-- Left visual -->
  <div class="hidden lg:flex flex-1 items-center justify-center p-12 relative overflow-hidden" style="background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f172a 100%)">
    <div class="absolute inset-0 opacity-[0.07]" style="background-image:linear-gradient(rgba(167,139,250,.8) 1px,transparent 1px),linear-gradient(90deg,rgba(167,139,250,.8) 1px,transparent 1px);background-size:48px 48px"></div>
    <div class="absolute top-0 left-0 w-80 h-80 bg-violet-600/20 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl animate-float" style="animation-delay:-4s"></div>
    <div class="relative z-10 text-center max-w-sm">
      <img src="<?= $logo ?>" alt="Logo" class="w-20 h-20 object-contain mx-auto mb-8 drop-shadow-[0_0_30px_rgba(167,139,250,.7)] animate-float">
      <h1 class="text-3xl font-bold text-white leading-tight mb-3">
        Admin <span class="text-gradient">Report</span><br>Recap
      </h1>
      <p class="text-slate-400 text-sm leading-relaxed">Panel admin untuk mengelola dan meninjau seluruh jurnal proyek siswa PPLG.</p>
      <div class="flex gap-3 justify-center mt-8 flex-wrap">
        <span class="badge badge-purple animate-float" style="animation-delay:-.5s">📊 Dashboard</span>
        <span class="badge badge-blue animate-float" style="animation-delay:-2s">✏️ Edit Jurnal</span>
        <span class="badge badge-green animate-float" style="animation-delay:-3.5s">📄 Export Word</span>
      </div>
    </div>
  </div>

  <!-- Right form -->
  <div class="w-full lg:w-[440px] flex flex-col justify-center px-8 py-12 bg-slate-950">
    <div class="max-w-sm mx-auto w-full">
      <img src="<?= $logo ?>" alt="Logo" class="w-12 h-12 object-contain mb-6 lg:hidden">

      <?php if ($flash): ?>
      <div id="flash-data" data-type="<?= $flash['type'] ?>" data-msg="<?= htmlspecialchars($flash['msg']) ?>" class="hidden"></div>
      <?php endif; ?>

      <div class="flex gap-1 mb-8 p-1 glass rounded-xl">
        <a href="?mode=login" class="flex-1 text-center py-2 rounded-lg text-sm font-medium transition <?= $mode==='login' ? 'bg-violet-600 text-white shadow' : 'text-slate-400 hover:text-white' ?>">Login Admin</a>
        <a href="?mode=register" class="flex-1 text-center py-2 rounded-lg text-sm font-medium transition <?= $mode==='register' ? 'bg-violet-600 text-white shadow' : 'text-slate-400 hover:text-white' ?>">Daftar</a>
      </div>

      <?php if ($mode === 'login'): ?>
      <h2 class="text-2xl font-bold text-white mb-1">Welcome back, Admin.</h2>
      <p class="text-slate-400 text-sm mb-6">Masuk ke panel admin PPLG Journal</p>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="login">
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Nama Admin</label>
          <input type="text" name="name" class="form-input" placeholder="Nama terdaftar" required>
        </div>
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Password</label>
          <div class="relative">
            <input type="password" name="password" id="li-pw" class="form-input pr-11" placeholder="••••••••" required>
            <button type="button" onclick="togglePw('li-pw',this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition"><?= eyeSvgA() ?></button>
          </div>
        </div>
        <button type="submit" class="btn-primary w-full" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">Sign In →</button>
      </form>

      <?php else: ?>
      <h2 class="text-2xl font-bold text-white mb-1">Buat Akun Admin</h2>
      <p class="text-slate-400 text-sm mb-6">Daftarkan akun admin baru</p>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="register">
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Nama</label>
          <input type="text" name="name" class="form-input" required>
        </div>
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Kelas / Jabatan</label>
          <input type="text" name="class" class="form-input" placeholder="Admin / XI PPLG" required>
        </div>
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Password</label>
          <div class="relative">
            <input type="password" name="password" id="rg-pw" class="form-input pr-11" required>
            <button type="button" onclick="togglePw('rg-pw',this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition"><?= eyeSvgA() ?></button>
          </div>
        </div>
        <div>
          <label class="block text-xs font-600 text-slate-400 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
          <div class="relative">
            <input type="password" name="password2" id="rg-pw2" class="form-input pr-11" required>
            <button type="button" onclick="togglePw('rg-pw2',this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition"><?= eyeSvgA() ?></button>
          </div>
        </div>
        <button type="submit" class="btn-primary w-full" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">Buat Akun →</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function togglePw(id,btn){
  const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';
  btn.innerHTML=i.type==='text'
    ?`<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`
    :`<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
}
</script>
<?php
function eyeSvgA(){return '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';}
include '../includes/footer.php'; ?>
