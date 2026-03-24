<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Room.php';
require_once __DIR__ . '/../classes/Booking.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    $_SESSION['flash_msg'] = "Silahkan login terlebih dahulu untuk melakukan pemesanan.";
    $_SESSION['flash_type'] = "warning";
    header("Location: index.php?page=login");
    exit;
}

$room_id = $_GET['room_id'] ?? null;
if (!$room_id) {
    header("Location: index.php?page=home");
    exit;
}

$roomModel = new Room();
$bookingModel = new Booking();

$room = $roomModel->getById($room_id);
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT base_price, name, max_occupancy FROM room_types WHERE id = ?");
$stmt->execute([$room['room_type_id']]);
$roomType = $stmt->fetch();

if (!$room || !$roomType) {
    die("Kamar tidak valid.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests_count = $_POST['guests_count'];
    $special_request = $_POST['special_request'];

    if (strtotime($check_in) >= strtotime($check_out)) {
        $error = "Tanggal Check-out harus lebih besar dari Check-in!";
    }
    elseif (!$bookingModel->isRoomAvailable($room_id, $check_in, $check_out)) {
        $error = "Maaf, kamar sudah dibooking pada tanggal tersebut. Silahkan pilih tanggal lain.";
    } 
    else {
        $total_price = 0;
        $currentDate = strtotime($check_in);
        $endDate = strtotime($check_out);
        
        while ($currentDate < $endDate) {
            $dayOfWeek = date('N', $currentDate); 
            $dailyPrice = $roomType['base_price'];
            
            if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                $dailyPrice = $dailyPrice * 1.20;
            }
            
            $total_price += $dailyPrice;
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        $today = strtotime(date('Y-m-d'));
        $checkInDate = strtotime($check_in);
        $daysDifference = ($checkInDate - $today) / (60 * 60 * 24);
        
        if ($daysDifference > 30) {
            $discount = $total_price * 0.15;
            $total_price = $total_price - $discount;
        }

        $data = [
            'guest_id' => $_SESSION['hotel_res_user_id'],
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests_count' => $guests_count,
            'total_price' => $total_price,
            'status' => 'pending',
            'special_request' => $special_request
        ];

        if ($bookingModel->create($data)) {
            $_SESSION['flash_msg'] = "Pemesanan berhasil! Tagihan Anda adalah Rp " . number_format($total_price, 0, ',', '.');
            $_SESSION['flash_type'] = "success";
            header("Location: index.php?page=home");
            exit;
        } else {
            $error = "Terjadi kesalahan sistem saat menyimpan data.";
        }
    }
}
?>
<div class="max-w-5xl mx-auto py-10">
    <div class="mb-12">
        <h2 class="text-4xl font-serif tracking-tight mb-4 text-stone-900">Pemesanan Suite</h2>
        <div class="w-16 h-[1px] bg-amber-700 mb-6"></div>
        <p class="text-stone-500 font-light text-sm max-w-2xl">
            Lengkapi rincian masa menginap Anda di bawah ini. Pastikan tanggal dan jumlah tamu sesuai dengan rencana perjalanan eksklusif Anda.
        </p>
    </div>

    <?php if(isset($error)): ?>
        <div class="mb-8 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm font-medium flex items-start">
            <i data-lucide="alert-circle" class="w-5 h-5 mr-3 shrink-0"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-3 gap-10 items-start">
        
        <!-- Summary Panel -->
        <div class="md:col-span-1 bg-stone-900 text-stone-100 p-8 shadow-2xl">
            <h4 class="text-xl font-serif italic mb-6">Detail Kamar</h4>
            <div class="space-y-6">
                <div>
                    <span class="block text-[10px] uppercase tracking-widest text-stone-400 mb-1">Tipe Suite</span>
                    <span class="text-sm font-medium text-white"><?= htmlspecialchars($roomType['name']) ?> (No. <?= htmlspecialchars($room['room_number']) ?>)</span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase tracking-widest text-stone-400 mb-1">Harga Dasar</span>
                    <span class="text-lg font-medium text-amber-500">Rp <?= number_format($roomType['base_price'], 0, ',', '.') ?> <span class="text-[10px] text-stone-500 font-normal">/ malam</span></span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase tracking-widest text-stone-400 mb-1">Kapasitas Maksimal</span>
                    <span class="text-sm font-medium text-white"><?= $roomType['max_occupancy'] ?> Orang</span>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-stone-800">
                <div class="flex items-start">
                    <i data-lucide="info" class="w-4 h-4 text-amber-500 mr-3 shrink-0 mt-0.5"></i>
                    <p class="text-[11px] text-stone-400 leading-relaxed m-0 pb-4">
                        <strong class="text-amber-500">Dynamic Pricing Aktif:</strong><br> Tambahan tarif +20% otomatis diterapkan untuk menginap di hari Sabtu & Minggu (Weekend/Libur).
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Form Panel -->
        <div class="md:col-span-2 bg-white p-8 sm:p-12 border border-stone-100 shadow-xl shadow-stone-200/50">
            <form method="POST" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="grid grid-cols-2 gap-8">
                    <div class="relative">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Check-In</label>
                        <input type="date" name="check_in" required min="<?= date('Y-m-d') ?>"
                            class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium text-sm">
                    </div>
                    
                    <div class="relative">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Check-Out</label>
                        <input type="date" name="check_out" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                            class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium text-sm">
                    </div>
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Jumlah Tamu</label>
                    <input type="number" name="guests_count" required min="1" max="<?= $roomType['max_occupancy'] ?>"
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium text-sm"
                        placeholder="Masukkan jumlah tamu menginap">
                </div>
                
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Catatan Tambahan (Opsional)</label>
                    <textarea name="special_request" rows="2" 
                        class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-amber-700 transition-colors text-stone-900 font-medium text-sm resize-none"
                        placeholder="Contoh: Minta lantai bawah, extra bantal..."></textarea>
                </div>
                
                <div class="pt-6 flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 bg-stone-900 text-white py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors flex justify-center items-center">
                        Konfirmasi & Hitung Total Tagihan
                    </button>
                    <a href="index.php?page=home" class="px-8 bg-transparent border border-stone-300 text-stone-600 py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:border-stone-900 hover:text-stone-900 transition-colors flex justify-center items-center no-underline text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
        
    </div>
</div>
