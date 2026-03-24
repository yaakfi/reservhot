<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Booking.php';

$auth = new Auth();
if (!$auth->hasRole(['admin', 'receptionist'])) {
    header("Location: index.php?page=home");
    exit;
}

$bookingModel = new Booking();

// Handle Update Status Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {

    // Server-Side Validation Wajib
    $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($_POST['status'], $valid_statuses)) {
        $_SESSION['flash_msg'] = "Gagal: Nilai status pesanan ditolak (Tidak valid).";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=admin_bookings");
        exit;
    }
    if ($bookingModel->updateStatus($_POST['booking_id'], $_POST['status'])) {
        $_SESSION['flash_msg'] = "Status pemesanan berhasil diperbarui.";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_msg'] = "Gagal memperbarui status.";
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: index.php?page=admin_bookings");
    exit;
}

$limit = 10;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($p < 1)
    $p = 1;
$offset = ($p - 1) * $limit;

$bookings = $bookingModel->getAllWithDetailsPaginated($limit, $offset);
$totalData = $bookingModel->countAll();
$totalPages = ceil($totalData / $limit);
?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-10 border-b border-stone-200 pb-6 text-center sm:text-left">
        <h1 class="text-4xl font-serif tracking-tight text-stone-900">Daftar Transaksi Pesanan</h1>
        <p class="text-stone-500 font-light text-sm mt-2">Seluruh pergerakan reservasi masuk, durasi menginap, dan pengawasan status pelunasan.</p>
    </div>

    <!-- Tabel Data Transaksi -->
    <div class="bg-white border border-stone-200 shadow-sm overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-[10px] uppercase tracking-widest text-stone-500">
                        <th class="px-6 py-5 font-bold">No. Tagihan</th>
                        <th class="px-6 py-5 font-bold">Identitas Tamu</th>
                        <th class="px-6 py-5 font-bold">Registrasi Kamar</th>
                        <th class="px-6 py-5 font-bold">Lama Menginap</th>
                        <th class="px-6 py-5 font-bold">Total Pembayaran</th>
                        <th class="px-6 py-5 font-bold text-center">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if(empty($bookings)): ?>
                        <tr><td colspan="6" class="px-6 py-10 text-center text-stone-400 italic font-serif">Belum terdapat aktivitas pemesanan dalam sistem.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($bookings as $b): ?>
                        <tr class="hover:bg-stone-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-stone-900 font-mono">#INV-<?= str_pad($b['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                <div class="mt-2">
                                    <?php 
                                        if($b['status'] == 'pending') echo '<span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-bold uppercase tracking-widest border border-amber-200">Pending</span>'; 
                                        elseif($b['status'] == 'confirmed') echo '<span class="inline-block px-2 py-0.5 bg-sky-100 text-sky-800 text-[9px] font-bold uppercase tracking-widest border border-sky-200">Confirmed</span>'; 
                                        elseif($b['status'] == 'completed') echo '<span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[9px] font-bold uppercase tracking-widest border border-emerald-200">Completed</span>'; 
                                        elseif($b['status'] == 'cancelled') echo '<span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-800 text-[9px] font-bold uppercase tracking-widest border border-rose-200">Cancelled</span>'; 
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-base font-serif text-stone-900"><?= htmlspecialchars($b['guest_name']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-stone-900">No. <?= htmlspecialchars($b['room_number']) ?></span>
                                    <span class="text-xs text-stone-500 mt-1"><?= htmlspecialchars($b['type_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 text-[11px] text-stone-600">
                                    <span class="flex items-center gap-1.5"><i data-lucide="arrow-right-circle" class="w-3 h-3 text-emerald-600"></i> In: <span class="font-medium text-stone-800"><?= date('d M Y', strtotime($b['check_in'])) ?></span></span>
                                    <span class="flex items-center gap-1.5"><i data-lucide="arrow-left-circle" class="w-3 h-3 text-rose-600"></i> Out: <span class="font-medium text-stone-800"><?= date('d M Y', strtotime($b['check_out'])) ?></span></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-stone-900 block">Rp <?= number_format($b['total_price'], 0, ',', '.') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col xl:flex-row items-center justify-center gap-3">
                                    <form method="POST" class="flex flex-col sm:flex-row items-center gap-2" onsubmit="return confirm('Terapkan perubahan status pada pesanan ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        
                                        <div class="relative">
                                            <select name="status" class="appearance-none bg-stone-100 border border-stone-200 text-stone-700 text-[10px] font-bold uppercase tracking-widest py-2 pl-3 pr-8 rounded-none hover:bg-stone-50 focus:outline-none focus:ring-1 focus:ring-stone-400 transition-colors w-28 text-center cursor-pointer">
                                                <option value="pending" <?= ($b['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= ($b['status'] == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="completed" <?= ($b['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= ($b['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <i data-lucide="chevron-down" class="w-3 h-3 absolute right-2 top-2.5 text-stone-500 pointer-events-none"></i>
                                        </div>
                                        
                                        <button type="submit" class="p-2 border border-stone-300 text-stone-600 hover:bg-stone-900 hover:text-white hover:border-stone-900 transition-colors" title="Simpan Status">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                    
                                    <a href="views/auth/print_invoice.php?id=<?= $b['id'] ?>" target="_blank" class="p-2 border border-stone-300 text-stone-600 hover:bg-stone-900 hover:text-white hover:border-stone-900 transition-colors" title="Cetak Surat Tagihan">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginasi Eksternal -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex justify-center pb-10">
        <ul class="flex items-center gap-2 list-none p-0 m-0">
            <li>
                <a href="<?= ($p <= 1) ? '#' : 'index.php?page=admin_bookings&p='.($p-1) ?>" class="px-3 py-2 border <?= ($p <= 1) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="index.php?page=admin_bookings&p=<?= $i ?>" class="px-4 py-2 border <?= ($i == $p) ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300 text-stone-600 hover:border-stone-900 text-white hover:text-stone-900 bg-transparent' ?> text-sm font-medium transition-colors no-underline" style="<?= ($i != $p) ? 'color:#57534e;' : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li>
                <a href="<?= ($p >= $totalPages) ? '#' : 'index.php?page=admin_bookings&p='.($p+1) ?>" class="px-3 py-2 border <?= ($p >= $totalPages) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>