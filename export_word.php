<?php
// admin/export_word.php — Export journal as professional Word doc with QR code
session_start();
require_once '../includes/db.php';
if (empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: journals.php'); exit; }

$db   = getDB();
$stmt = $db->prepare("SELECT j.*, u.class as user_class_ref FROM journals j LEFT JOIN users u ON j.user_id=u.id WHERE j.id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { header('Location: journals.php'); exit; }

// PHPWord via Composer (install: composer require phpoffice/phpword)
// Path: vendor/autoload.php relative to project root
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#0f172a;color:#f1f5f9"><h2>PHPWord belum diinstall</h2><p>Jalankan: <code style="background:#1e293b;padding:4px 8px;border-radius:4px">composer require phpoffice/phpword</code></p><a href="journals.php" style="color:#60a5fa">← Kembali</a></div>');
}

require_once $autoload;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

// ── Build Word document ──────────────────────────────────────
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Arial');
$phpWord->setDefaultFontSize(11);

// Page styling
$section = $phpWord->addSection([
    'marginTop'    => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
    'marginLeft'   => Converter::cmToTwip(2.5),
    'marginRight'  => Converter::cmToTwip(2.5),
]);

// ── Header ──
$header = $section->addHeader();
$headerTable = $header->addTable(['borderSize'=>0,'cellMargin'=>80]);
$headerTable->addRow();

// Logo cell
$logoPath = __DIR__ . '/../uploads/' . ($j['evidence_image'] ?? '');
$logoCell = $headerTable->addCell(1800);
if (file_exists(__DIR__ . '/../assets/logo.jpeg')) {
    $logoCell->addImage(__DIR__ . '/../assets/logo.jpeg', ['width'=>40,'height'=>40,'wrappingStyle'=>'inline']);
} else {
    $logoCell->addText('ONLIVITY', ['bold'=>true,'size'=>14,'color'=>'1E3A8A']);
}

// Title cell
$titleCell = $headerTable->addCell(7000);
$titleCell->addText('ONLIVITY PROJECT RECAP REPORT', ['bold'=>true,'size'=>13,'color'=>'1E3A8A','allCaps'=>true]);
$titleCell->addText('Laporan Resmi Proyek Siswa PPLG', ['size'=>9,'color'=>'64748B','italic'=>true]);
$header->addLine(['weight'=>1,'color'=>'BFDBFE','space'=>0]);

// ── Footer ──
$footer = $section->addFooter();
$footer->addLine(['weight'=>1,'color'=>'E2E8F0']);
$footTable = $footer->addTable(['borderSize'=>0]);
$footTable->addRow();
$footTable->addCell(5500)->addText('Dicetak oleh PPLG Journal System — ' . date('d F Y, H:i'), ['size'=>8,'color'=>'94A3B8']);
$footTable->addCell(3300)->addText('Halaman ', ['size'=>8,'color'=>'94A3B8'], ['alignment'=>'right']);

// ── Body ──
// Title block
$section->addText('LAPORAN REKAP PROYEK', ['bold'=>true,'size'=>16,'color'=>'0F172A','allCaps'=>true], ['alignment'=>'center','spaceAfter'=>80]);
$section->addText(strtoupper($j['project_name'] ?? '—'), ['bold'=>true,'size'=>13,'color'=>'2563EB'], ['alignment'=>'center','spaceAfter'=>200]);

// Decorative line
$section->addLine(['weight'=>2,'color'=>'3B82F6','space'=>160]);
$section->addText('');

// Section: Informasi Pelaporan
$section->addText('INFORMASI PELAPORAN', ['bold'=>true,'size'=>10,'color'=>'64748B','allCaps'=>true], ['spaceAfter'=>80]);

$tStyle = ['unit'=>'pct','width'=>100,'cellMarginTop'=>80,'cellMarginBottom'=>80,'cellMarginLeft'=>120,'cellMarginRight'=>120,'borderInsideHColor'=>'E2E8F0','borderInsideVColor'=>'FFFFFF','borderInsideHSize'=>4,'borderInsideVSize'=>0];
$tbl = $section->addTable($tStyle);

$rows = [
    ['No. Order',   $j['order_number'] ?? '—'],
    ['Tipe Laporan', ucwords(str_replace('_',' ', $j['report_type'] ?? '—'))],
    ['Nama Pelapor', $j['name'] ?? '—'],
    ['Kelas',        $j['class'] ?? ($j['user_class_ref'] ?? '—')],
];
foreach ($rows as [$label, $val]) {
    $tbl->addRow();
    $cell1 = $tbl->addCell(3000, ['bgColor'=>'EFF6FF']);
    $cell1->addText($label, ['bold'=>true,'size'=>10,'color'=>'1E40AF']);
    $cell2 = $tbl->addCell(6000);
    $cell2->addText($val, ['size'=>10,'color'=>'0F172A']);
}

$section->addText('');
$section->addText('DETAIL PROYEK', ['bold'=>true,'size'=>10,'color'=>'64748B','allCaps'=>true], ['spaceAfter'=>80]);

$tbl2 = $section->addTable($tStyle);
$detail = [
    ['Nama Proyek',       $j['project_name'] ?? '—'],
    ['Nama PIC',          $j['pic_name'] ?? '—'],
];
if (!empty($j['client_name'])) $detail[] = ['Nama Klien', $j['client_name']];
$detail = array_merge($detail, [
    ['Tgl. Order',        $j['order_date'] ?? '—'],
    ['Tgl. Selesai',      $j['completion_date'] ?? '—'],
]);
if (!empty($j['description'])) $detail[] = ['Keterangan', $j['description']];

foreach ($detail as [$label, $val]) {
    $tbl2->addRow();
    $c1 = $tbl2->addCell(3000, ['bgColor'=>'EFF6FF']);
    $c1->addText($label, ['bold'=>true,'size'=>10,'color'=>'1E40AF']);
    $c2 = $tbl2->addCell(6000);
    $c2->addText($val, ['size'=>10,'color'=>'0F172A']);
}

// Evidence image
if (!empty($j['evidence_image'])) {
    $imgPath = __DIR__ . '/../uploads/' . $j['evidence_image'];
    if (file_exists($imgPath)) {
        $section->addText('');
        $section->addText('BUKTI / DOKUMENTASI', ['bold'=>true,'size'=>10,'color'=>'64748B','allCaps'=>true], ['spaceAfter'=>80]);
        $section->addImage($imgPath, ['width'=>300,'height'=>200,'wrappingStyle'=>'inline']);
    }
}

// QR Code section
$detailUrl = USER_URL . '/user/journal_detail.php?id=' . $j['id'];
$qrUrl     = 'https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=' . urlencode($detailUrl) . '&choe=UTF-8';

$section->addText('');
$section->addLine(['weight'=>1,'color'=>'E2E8F0']);
$section->addText('');

// QR code + info table
$qrTbl = $section->addTable(['borderSize'=>0,'cellMargin'=>80]);
$qrTbl->addRow();
$qrCell = $qrTbl->addCell(2000);
try {
    // Fetch QR image into temp file
    $qrTemp = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
    $qrImg  = @file_get_contents($qrUrl);
    if ($qrImg) {
        file_put_contents($qrTemp, $qrImg);
        $qrCell->addImage($qrTemp, ['width'=>90,'height'=>90,'wrappingStyle'=>'inline']);
    } else {
        $qrCell->addText('[QR]', ['size'=>8,'color'=>'94A3B8']);
    }
} catch (Exception $e) {
    $qrCell->addText('[QR]', ['size'=>8,'color'=>'94A3B8']);
}

$infoCell = $qrTbl->addCell(7000, ['valign'=>'center']);
$infoCell->addText('Scan QR untuk melihat jurnal online:', ['size'=>9,'color'=>'64748B','bold'=>true]);
$infoCell->addText($detailUrl, ['size'=>8,'color'=>'2563EB']);
$infoCell->addText('');
$infoCell->addText('Dokumen ini digenerate otomatis oleh PPLG Journal System.', ['size'=>8,'color'=>'94A3B8','italic'=>true]);

// ── Output ──
$filename = 'Report_' . ($j['order_number'] ?? $j['id']) . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit;
?>
