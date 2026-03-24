<?php
require_once __DIR__ . '/../../classes/Auth.php';
if (!isset($auth)) {
    $auth = new Auth();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Reservasi Hotel & Homestay</title>
    <!-- Tailwind CSS & Lucide Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #FDFCF8; color: #1A1A1A; }
        
        /* Modal Backdrop Override Global */
        .modal-active { overflow: hidden; }
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 998;
            display: none; justify-content: center; align-items: center;
        }

        /* Prevent auto-styling of generic links if needed */
        a { text-decoration: none; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['"Inter"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="selection:bg-amber-200">

<?php 
// Konfigurasi Navigasi tergantung laman apa yang merender (Transparent untuk home)
global $page;
$navClass = ($page == 'home') ? 'fixed top-0 bg-transparent py-4' : 'sticky top-0 bg-white/80 backdrop-blur-xl border-b border-stone-100 py-3'; 
?>
<!-- Navigation -->
<nav id="main-nav" class="<?= $navClass ?> w-full z-50 transition-all duration-500 px-6">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-12">
            <a href="index.php?page=home" class="text-2xl font-serif italic tracking-tighter font-bold text-stone-900">
                Hotel<span class="text-amber-700">Res.</span>
            </a>
            <div class="hidden lg:flex gap-8 text-[13px] font-medium uppercase tracking-[0.2em] text-stone-500">
                <a href="index.php?page=home" class="hover:text-amber-700 transition-colors <?= $page == 'home' ? 'text-amber-800' : '' ?>">Beranda</a>
                <?php if($auth->hasRole(['admin', 'receptionist'])): ?>
                    <a href="index.php?page=admin_dashboard" class="hover:text-amber-700 transition-colors <?= in_array($page, ['admin_dashboard', 'admin_rooms', 'admin_bookings']) ? 'text-amber-800' : '' ?>">Dashboard Admin</a>
                <?php endif; ?>
                <?php if($auth->hasRole(['admin'])): ?>
                    <a href="index.php?page=admin_room_types" class="hover:text-amber-700 transition-colors <?= $page == 'admin_room_types' ? 'text-amber-800' : '' ?>">Tipe Kamar</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <?php if(isset($_SESSION['hotel_res_logged_in'])): ?>
                <a href="index.php?page=my_bookings" class="hidden sm:flex items-center gap-2 text-[12px] font-bold uppercase tracking-widest <?= $page == 'my_bookings' ? 'text-amber-700 border-b border-amber-700' : 'text-stone-600 hover:border-b hover:border-amber-700' ?> pb-1 transition-all">
                    Pesanan Saya
                </a>
                
                <div class="hidden sm:flex flex-col text-right ml-2 mr-2 leading-tight">
                    <span class="text-[11px] font-bold text-stone-900"><?= htmlspecialchars($_SESSION['hotel_res_username']) ?></span>
                    <span class="text-[9px] uppercase tracking-widest text-amber-700"><?= ucfirst($_SESSION['hotel_res_role']) ?></span>
                </div>
                
                <div class="h-10 w-10 border border-stone-200 rounded-full bg-stone-100 flex items-center justify-center">
                    <i data-lucide="user" class="w-4 h-4 text-stone-600"></i>
                </div>
                <a href="index.php?page=logout" class="ml-2 py-2 px-3 text-red-500 hover:bg-stone-100 rounded-md transition-colors hidden sm:flex items-center" title="Keluar">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            <?php else: ?>
                <a href="index.php?page=login" class="hidden sm:flex items-center gap-2 text-[12px] font-bold uppercase tracking-widest text-stone-600 border-b border-transparent hover:border-amber-700 pb-1 transition-all">
                    Login / Register
                </a>
            <?php endif; ?>
            
            <button class="lg:hidden text-stone-600"><i data-lucide="menu" class="w-6 h-6"></i></button>
        </div>
    </div>
</nav>

<!-- Flash Message Global - Native Tailwind -->
<?php if(isset($_SESSION['flash_msg'])): ?>
    <?php 
        $typeAlert = $_SESSION['flash_type'] ?? 'info'; 
        // Konversi warna alert Bootstrap ke palet Tailwind
        $bg = 'bg-stone-800'; 
        $tc = 'text-white';
        $icon = 'info';
        if ($typeAlert == 'success') { $bg = 'bg-emerald-600'; $icon = 'check-circle'; }
        if ($typeAlert == 'danger') { $bg = 'bg-rose-600'; $icon = 'x-circle'; }
        if ($typeAlert == 'warning') { $bg = 'bg-amber-500'; $tc = 'text-stone-900'; $icon = 'alert-triangle'; }
    ?>
    <div class="fixed top-24 right-6 z-[999] p-4 rounded-sm shadow-2xl <?= $bg ?> <?= $tc ?> flex items-start max-w-sm border border-black/10 animate-fade-in-down" id="flashMsg">
        <i data-lucide="<?= $icon ?>" class="w-5 h-5 mr-3 shrink-0"></i>
        <div class="text-[13px] font-medium leading-relaxed font-sans">
            <?= htmlspecialchars($_SESSION['flash_msg']) ?>
        </div>
        <button onclick="document.getElementById('flashMsg').style.display='none'" class="ml-4 opacity-70 hover:opacity-100 focus:outline-none shrink-0" type="button">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <?php 
    unset($_SESSION['flash_msg']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<!-- Main Wrapper -->
<?php $mainClass = ($page == 'home') ? '' : 'max-w-7xl mx-auto px-6 py-12 min-h-[70vh] relative pt-16'; ?>
<main class="<?= $mainClass ?>">
