<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Booking.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: index.php?page=login");
    exit;
}

$bookingModel = new Booking();
$db = Database::getInstance()->getConnection();

// Handle Form Rating (Fitur Khusus 4: Sistem Rating komentar & Unicode star)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate') {
    $booking_id = (int)$_POST['booking_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    // Validasi Server Side (Angka postif dan Field Tidak Kosong)
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        $_SESSION['flash_msg'] = "Gagal: Rating harus di antara 1-5 dan komentar wajib diisi.";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=my_bookings");
        exit;
    }

    $stmt = $db->prepare("INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)");
    if ($stmt->execute([$booking_id, $rating, $comment])) {
        $_SESSION['flash_msg'] = "Terima kasih atas ulasan Anda!";
        $_SESSION['flash_type'] = "success";
    }
    header("Location: index.php?page=my_bookings");
    exit;
}

// Ambil history order milik user login
$user_id = $_SESSION['hotel_res_user_id'];

// Pagination Setup
$limit = 10; 
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

$my_bookings = $bookingModel->getBookingsByUserIdPaginated($user_id, $limit, $offset);
$totalData = $bookingModel->countBookingsByUserId($user_id);
$totalPages = ceil($totalData / $limit);
?>

<div class="max-w-6xl mx-auto py-10 px-4">
    <div class="mb-12 border-b border-stone-200 pb-6 flex justify-between items-end">
        <div>
            <h1 class="text-4xl font-serif tracking-tight text-stone-900">Riwayat Pemesanan Saya</h1>
            <p class="text-stone-500 font-light text-sm mt-2">Daftar agenda penginapan dan status konfirmasi transaksi Anda.</p>
        </div>
    </div>

    <?php if(empty($my_bookings)): ?>
        <div class="text-center py-20 bg-stone-50 border border-stone-100">
            <h4 class="text-2xl font-serif italic text-stone-400 mb-4">Anda belum memiliki riwayat pemesanan.</h4>
            <a href="index.php?page=home" class="inline-block mt-4 bg-stone-900 text-white px-8 py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors no-underline">
                Mulai Eksplorasi Kamar
            </a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-8">
            <?php foreach($my_bookings as $b): ?>
                <?php
                    // Styling status
                    $st_bg = 'bg-stone-100 text-stone-600';
                    $st_border = 'border-stone-200';
                    if($b['status'] == 'completed') { $st_bg = 'bg-emerald-100 text-emerald-800'; $st_border = 'border-emerald-500'; }
                    elseif($b['status'] == 'cancelled') { $st_bg = 'bg-rose-100 text-rose-800'; $st_border = 'border-rose-500'; }
                    elseif($b['status'] == 'pending') { $st_bg = 'bg-amber-100 text-amber-800'; $st_border = 'border-amber-500'; }
                ?>
                <div class="bg-white border-l-4 <?= $st_border ?> p-8 shadow-xl shadow-stone-200/30 relative flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h5 class="text-xl font-serif text-stone-900 mb-1"><?= htmlspecialchars($b['type_name']) ?></h5>
                                <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Kamar No. <?= $b['room_number'] ?></span>
                            </div>
                            <span class="px-4 py-1.5 bg-stone-900 text-white text-[10px] font-bold tracking-widest uppercase">
                                Rp <?= number_format($b['total_price'],0,',','.') ?>
                            </span>
                        </div>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex justify-between border-b border-stone-100 pb-2">
                                <span class="text-xs text-stone-500">Check In</span>
                                <span class="text-sm font-medium text-stone-900"><?= date('d M Y', strtotime($b['check_in'])) ?></span>
                            </div>
                            <div class="flex justify-between border-b border-stone-100 pb-2">
                                <span class="text-xs text-stone-500">Check Out</span>
                                <span class="text-sm font-medium text-stone-900"><?= date('d M Y', strtotime($b['check_out'])) ?></span>
                            </div>
                            <div class="flex justify-between pt-2">
                                <span class="text-xs text-stone-500">Status Reservasi</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 <?= $st_bg ?>">
                                    <?= strtoupper($b['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-4 mt-auto">
                        <!-- Cetak Fitur Nomor 3 -->
                        <a href="views/auth/print_invoice.php?id=<?= $b['id'] ?>" target="_blank" class="px-4 py-2 border border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900 text-[10px] font-bold uppercase tracking-widest transition-colors flex items-center gap-2 no-underline">
                            <i data-lucide="printer" class="w-3 h-3"></i> Cetak Invoice
                        </a>
                        
                        <!-- Review Fitur Khusus 4 -->
                        <?php if($b['status'] == 'completed' && empty($b['rating'])): ?>
                            <button type="button" onclick="openModal('reviewModal<?= $b['id'] ?>')" class="px-4 py-2 border border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white text-[10px] font-bold uppercase tracking-widest transition-colors flex items-center gap-2">
                                <i data-lucide="star" class="w-3 h-3"></i> Beri Ulasan
                            </button>
                        <?php elseif(!empty($b['rating'])): ?>
                            <div class="px-4 py-2 border border-emerald-200 bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                <span>Nilai Anda:</span>
                                <span class="text-amber-500 text-sm tracking-widest">
                                    <?php 
                                        echo str_repeat('★', $b['rating']);
                                        echo str_repeat('☆', 5 - $b['rating']);
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Modal Rating Vanilla JS -->
                    <div id="reviewModal<?= $b['id'] ?>" class="modal-overlay">
                        <div class="bg-white max-w-lg w-full p-10 shadow-2xl relative border border-stone-100">
                            <button type="button" onclick="closeModal('reviewModal<?= $b['id'] ?>')" class="absolute top-4 right-4 text-stone-400 hover:text-stone-900">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                            <h5 class="text-2xl font-serif italic mb-2 text-stone-900">Bagikan Pengalaman Anda</h5>
                            <p class="text-xs text-stone-500 mb-8 pb-4 border-b border-stone-100">Ulasan Anda membantu kami meningkatkan standar pelayanan kemewahan konstan.</p>
                            
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="rate">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                
                                <div class="relative">
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Penilaian Bintang (1-5)</label>
                                    <select name="rating" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm" required>
                                        <option value="5">★★★★★ (Sangat Memuaskan)</option>
                                        <option value="4">★★★★☆ (Memuaskan)</option>
                                        <option value="3">★★★☆☆ (Cukup)</option>
                                        <option value="2">★★☆☆☆ (Kurang)</option>
                                        <option value="1">★☆☆☆☆ (Mengecewakan)</option>
                                    </select>
                                </div>
                                
                                <div class="relative mt-6">
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Pesan Ulasan</label>
                                    <textarea name="comment" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="3" required placeholder="Ceritakan detail tentang kenyamanan kamar atau pelayanan staf..."></textarea>
                                </div>
                                
                                <button type="submit" class="w-full bg-stone-900 text-white py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors mt-8">
                                    Kirim Ulasan Spesial
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginasi Tailwind -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav class="mt-16 flex justify-center pb-10">
            <ul class="flex items-center gap-2 list-none p-0 m-0">
                <li>
                    <a href="<?= ($p <= 1) ? '#' : 'index.php?page=my_bookings&p='.($p-1) ?>" class="px-3 py-2 border <?= ($p <= 1) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li>
                        <a href="index.php?page=my_bookings&p=<?= $i ?>" class="px-4 py-2 border <?= ($i == $p) ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300 text-stone-600 hover:border-stone-900 text-white hover:text-stone-900 bg-transparent' ?> text-sm font-medium transition-colors no-underline" style="<?= ($i != $p) ? 'color:#57534e;' : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li>
                    <a href="<?= ($p >= $totalPages) ? '#' : 'index.php?page=my_bookings&p='.($p+1) ?>" class="px-3 py-2 border <?= ($p >= $totalPages) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
    <?php endif; ?>
</div>
