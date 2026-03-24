<?php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
if ($auth->isLoggedIn()) {
    header("Location: index.php?page=home");
    exit;
}

// Handle Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        $_SESSION['flash_msg'] = "Berhasil Login! Selamat Datang.";
        $_SESSION['flash_type'] = "success";
        
        // Redirect berdasarkan role
        if ($_SESSION['hotel_res_role'] === 'admin' || $_SESSION['hotel_res_role'] === 'receptionist') {
            header("Location: index.php?page=admin_dashboard");
        } else {
            header("Location: index.php?page=home");
        }
        exit;
    } else {
        $login_error = "Username atau Password salah!";
    }
}

// Handle Register Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $data = [
        'username' => $_POST['req_username'],
        'email' => $_POST['req_email'],
        'password' => $_POST['req_password'],
        'phone' => $_POST['req_phone']
    ];
    
    try {
        if ($auth->register($data)) {
            $_SESSION['flash_msg'] = "Registrasi Berhasil! Silahkan Login.";
            $_SESSION['flash_type'] = "success";
            header("Location: index.php?page=login");
            exit;
        }
    } catch(Exception $e) {
        $reg_error = "Gagal mendaftar. Username/Email mungkin sudah terpakai.";
    }
}
?>

<div class="max-w-6xl mx-auto py-10">
    <!-- Header Section -->
    <div class="text-center mb-16">
        <h2 class="text-4xl font-serif tracking-tight mb-4">Autentikasi Akses</h2>
        <p class="text-stone-500 font-light text-sm max-w-md mx-auto">
            Silakan masuk untuk mengelola reservasi Anda atau daftar sebagai tamu baru untuk memulai pengalaman menginap tak tertandingi.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-12 lg:gap-20 items-start">
        
        <!-- Form Login -->
        <div class="bg-white p-10 sm:p-14 border border-stone-100 shadow-2xl shadow-stone-200/50">
            <div class="mb-10">
                <h3 class="text-2xl font-serif italic mb-2">Welcome Back.</h3>
                <div class="w-12 h-[1px] bg-amber-700"></div>
            </div>

            <?php if(isset($login_error)): ?>
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm font-medium flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 shrink-0"></i>
                    <?= $login_error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="login">
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Username</label>
                    <input type="text" name="username" required 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium placeholder-stone-300"
                        placeholder="Ketik username Anda">
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Kata Sandi</label>
                    <input type="password" name="password" required 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium placeholder-stone-300"
                        placeholder="••••••••">
                </div>
                
                <button type="submit" class="w-full bg-stone-900 text-white py-4 text-[12px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors flex justify-center items-center mt-6">
                    Akses Masuk <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </button>
            </form>
        </div>
        
        <!-- Form Register -->
        <div class="bg-stone-50 p-10 sm:p-14 border border-stone-200">
            <div class="mb-10">
                <h3 class="text-2xl font-serif italic mb-2">Join as Guest.</h3>
                <div class="w-12 h-[1px] bg-stone-400"></div>
            </div>

            <?php if(isset($reg_error)): ?>
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm font-medium flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 shrink-0"></i>
                    <?= $reg_error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="register">
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Username</label>
                    <input type="text" name="req_username" required 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 font-medium placeholder-stone-300">
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Alamat Email</label>
                    <input type="email" name="req_email" required 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 font-medium placeholder-stone-300">
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Nomor Telepon</label>
                    <input type="text" name="req_phone" 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 font-medium placeholder-stone-300">
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Kata Sandi Baru</label>
                    <input type="password" name="req_password" required 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 font-medium placeholder-stone-300">
                </div>
                
                <button type="submit" class="w-full bg-transparent border border-stone-900 text-stone-900 py-4 text-[12px] font-bold uppercase tracking-[0.2em] hover:bg-stone-900 hover:text-white transition-all flex justify-center items-center mt-8">
                    Daftar Sekarang
                </button>
            </form>
        </div>
        
    </div>
</div>
