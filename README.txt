╔══════════════════════════════════════════════════════════════════╗
║       PPLG JOURNAL SYSTEM — COMPLETE UPDATE PACKAGE             ║
║       Modern Glassmorphism + Bento Grid Edition                  ║
╚══════════════════════════════════════════════════════════════════╝

📁 FILE STRUCTURE
─────────────────────────────────────────────────────────────────
htdocs/ (or your domain root)
├── .htaccess                    ← Security & PHP config
├── composer.json                ← PHPWord dependency
├── alter_table.sql              ← RUN THIS FIRST in phpMyAdmin
│
├── includes/
│   ├── db.php                   ← PDO config + helpers (EDIT THIS)
│   ├── header.php               ← Shared HTML head
│   ├── footer.php               ← Scroll buttons + JS
│   └── user_nav.php             ← User sidebar/navbar
│
├── assets/
│   ├── css/main.css             ← Glassmorphism design system
│   └── js/main.js               ← Dropdowns, upload, SweetAlert
│
├── uploads/                     ← Auto-created, chmod 755
│   └── .htaccess                ← Blocks PHP execution here
│
├── user/                        → jurnalrekaponlivity.ct.ws/user/
│   ├── index.php                ← Login + Register
│   ├── dashboard.php            ← User dashboard (bento stats)
│   ├── journal.php              ← List all journals
│   ├── journal_add.php          ← Add/Edit journal + image upload
│   ├── journal_detail.php       ← Detail view + QR code
│   ├── profile.php              ← Edit name/class/password/avatar
│   └── logout.php
│
└── admin/                       → adminjurnalonlivity.ct.ws/admin/
    ├── index.php                ← Admin login + register
    ├── dashboard.php            ← Dashboard + Google Charts
    ├── journals.php             ← All journals table
    ├── journal_detail.php       ← Full detail view
    ├── journal_edit.php         ← Edit any journal
    ├── users.php                ← User cards grid
    ├── export_word.php          ← Export .docx with QR code
    └── logout.php

══════════════════════════════════════════════════════════════════
STEP-BY-STEP DEPLOYMENT
══════════════════════════════════════════════════════════════════

STEP 1 — RUN SQL MIGRATION
  Open phpMyAdmin → your database → SQL tab
  Paste the contents of alter_table.sql → click Go
  This adds: avatar, updated_at to users
             evidence_image, updated_at to journals

STEP 2 — EDIT includes/db.php
  Update these 4 lines with your InfinityFree credentials:
    define('DB_HOST', 'sql304.infinityfree.com');
    define('DB_USER', 'your_db_user');
    define('DB_PASS', 'your_db_password');
    define('DB_NAME', 'your_db_name');

  Also update the domain constants:
    define('USER_URL',  'https://jurnalrekaponlivity.ct.ws');
    define('ADMIN_URL', 'https://adminjurnalonlivity.ct.ws');

STEP 3 — INSTALL PHPWord (for Word export)
  On your local machine or via SSH:
    composer install
  Then upload the generated vendor/ folder to htdocs/
  
  If you can't use Composer on InfinityFree:
  ➜ Run: composer install --no-dev on your computer
  ➜ Upload the vendor/ folder via FTP

STEP 4 — CREATE uploads/ FOLDER
  In InfinityFree File Manager:
  - Create folder: htdocs/uploads/
  - Set permissions: 755 (chmod 755)
  - Upload uploads/.htaccess into it

STEP 5 — UPLOAD ALL FILES
  Upload everything maintaining the exact folder structure.
  Do NOT upload: composer.json, README.txt, alter_table.sql
  (keep these locally for reference)

STEP 6 — TEST
  User site:  https://jurnalrekaponlivity.ct.ws/user/
  Admin site: https://adminjurnalonlivity.ct.ws/admin/

══════════════════════════════════════════════════════════════════
NEW FEATURES IN THIS UPDATE
══════════════════════════════════════════════════════════════════

UI/UX
  ✅ Tailwind CSS via CDN — dark glassmorphism theme
  ✅ Bento Grid layout on dashboard
  ✅ Glassmorphism cards (glass / glass-strong classes)
  ✅ Animated ambient background blobs
  ✅ Smooth floating Scroll Up/Down buttons
  ✅ Profile dropdown in top-right corner
  ✅ Collapsible sidebar (mobile-first)
  ✅ SweetAlert2 for all notifications

USER FEATURES
  ✅ Upload evidence image (jpg/png, max 2MB)
  ✅ Drag & drop upload area with preview
  ✅ Images stored in /uploads/, path in DB
  ✅ Profile page: change name, class, password, avatar
  ✅ Journal detail page with QR code
  ✅ Copy link button

ADMIN FEATURES
  ✅ Google Charts — Donut + Bar chart on dashboard
  ✅ Clickable stats → modal with full data table
  ✅ Count-up animation on numbers
  ✅ View + Edit buttons on every journal row
  ✅ User cards grid with journal count
  ✅ Search + filter in all tables

WORD EXPORT
  ✅ PHPWord professional brochure layout
  ✅ Logo + header on every page
  ✅ Colored table with project details
  ✅ Evidence image embedded in document
  ✅ QR Code (Google Charts API) at bottom
  ✅ QR links to live journal detail page

SECURITY
  ✅ PDO with prepared statements (no SQL injection)
  ✅ password_hash() / password_verify()
  ✅ File MIME type validation (not just extension)
  ✅ uploads/.htaccess blocks PHP execution
  ✅ Session guards on all protected pages
  ✅ .htaccess blocks direct access to includes/

══════════════════════════════════════════════════════════════════
TROUBLESHOOTING
══════════════════════════════════════════════════════════════════

Image upload not working?
  → Check uploads/ folder exists and is chmod 755
  → Check PHP upload_max_filesize ≥ 3M in .htaccess

Word export error "PHPWord not installed"?
  → Run: composer install in project root
  → Upload the vendor/ folder to your server

Charts not showing?
  → Check internet connection (loads from gstatic.com)
  → Ensure Google Charts API isn't blocked

QR code not loading?
  → Check USER_URL in db.php is correct
  → Google Charts QR requires internet access

Session issues?
  → Both user/ and admin/ use the same PHP session
  → Each has separate session keys (user_id vs admin_id)
  → They can run on the same OR different domains

══════════════════════════════════════════════════════════════════
