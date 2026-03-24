<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Room.php';
require_once __DIR__ . '/../classes/Booking.php';

$auth = new Auth();
// Proteksi halaman, hanya admin dan receptionist
if (!$auth->hasRole(['admin', 'receptionist'])) {
    $_SESSION['flash_msg'] = "Akses Ditolak! Anda tidak memiliki izin ke halaman ini.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php?page=home");
    exit;
}

$roomModel = new Room();
$bookingModel = new Booking();

$rooms = $roomModel->getAllWithTypes();

// Logika Kalender (Fitur Khusus 1: Kalender ketersediaan kamar - color coded grid)
// Tampilkan 7 hari ke depan mulai hari ini
$days_to_show = 7;
$dates = [];
$current_date = time();

for ($i = 0; $i < $days_to_show; $i++) {
    $dates[] = date('Y-m-d', strtotime("+$i days", $current_date));
}

// Ambil semua booking yg aktif agar tidak query per sel (Optimization)
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT room_id, check_in, check_out FROM bookings WHERE status != 'cancelled'");
$activeBookings = $stmt->fetchAll();

// Fungsi helper untuk mengecek apakah kamar dibooking di tanggal tertentu
function isRoomBookedOnDate($roomId, $dateStr, $bookings) {
    $targetDate = strtotime($dateStr);
    foreach ($bookings as $b) {
        if ($b['room_id'] == $roomId) {
            $in = strtotime($b['check_in']);
            $out = strtotime($b['check_out']);
            // Kamar terhitung dibooking dari check_in sampai H-1 check_out
            if ($targetDate >= $in && $targetDate < $out) {
                return true;
            }
        }
    }
    return false;
}

// --- FUNGSI BONUS E: Revenue Analytics Dashboard ---
// Hitung data bulan ini
$currentMonth = date('m');
$currentYear = date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

$queryAnalytics = "
    SELECT 
        rt.name as tipe_kamar,
        COUNT(r.id) as total_kamar,
        SUM(CASE WHEN b.status = 'completed' OR b.status = 'confirmed' THEN b.total_price ELSE 0 END) as total_pendapatan,
        SUM(CASE WHEN b.status = 'completed' OR b.status = 'confirmed' THEN DATEDIFF(b.check_out, b.check_in) ELSE 0 END) as total_malam_terjual
    FROM room_types rt
    LEFT JOIN rooms r ON rt.id = r.room_type_id
    LEFT JOIN bookings b ON r.id = b.room_id 
        AND MONTH(b.check_in) = ? 
        AND YEAR(b.check_in) = ?
    GROUP BY rt.id, rt.name
";
$stmtAnalytics = $db->prepare($queryAnalytics);
$stmtAnalytics->execute([$currentMonth, $currentYear]);
$analyticsData = $stmtAnalytics->fetchAll();

?>

<div class="max-w-7xl mx-auto py-8">
    <div class="mb-10 flex justify-between items-end border-b border-stone-200 pb-6">
        <div>
            <h1 class="text-4xl font-serif tracking-tight text-stone-900">Dashboard Pengelola</h1>
            <p class="text-stone-500 font-light text-sm mt-2">Ringkasan operasional dan analitik pendapatan hotel Anda.</p>
        </div>
        <div class="flex gap-4">
            <a href="index.php?page=admin_rooms" class="px-6 py-2.5 bg-stone-900 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-amber-800 transition-colors no-underline">
                Manajemen Kamar
            </a>
            <a href="index.php?page=admin_bookings" class="px-6 py-2.5 border border-stone-300 text-stone-700 text-[10px] font-bold uppercase tracking-widest hover:border-stone-900 hover:text-stone-900 transition-colors no-underline">
                Kelola Pesanan
            </a>
        </div>
    </div>

    <!-- TANTANGAN BONUS E: Revenue Analytics Dashboard -->
    <div class="mb-12 bg-white border border-stone-200 shadow-sm">
        <div class="px-8 py-6 border-b border-stone-200 bg-stone-50 flex items-center justify-between">
            <h5 class="text-lg font-serif italic text-stone-900 m-0 flex items-center gap-3">
                <i data-lucide="trending-up" class="w-5 h-5 text-amber-700"></i>
                Revenue Analytics (Bulan <?= date('F Y') ?>)
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-stone-200 text-[10px] uppercase tracking-widest text-stone-400">
                        <th class="px-8 py-4 font-bold">Tipe Kamar</th>
                        <th class="px-8 py-4 font-bold text-right">Pendapatan</th>
                        <th class="px-8 py-4 font-bold text-right">ADR (Avg Daily Rate)</th>
                        <th class="px-8 py-4 font-bold text-center">Occupancy Rate (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach($analyticsData as $ad): 
                        // ADR = Total Pendapatan / Total Malam Terjual
                        $adr = ($ad['total_malam_terjual'] > 0) ? ($ad['total_pendapatan'] / $ad['total_malam_terjual']) : 0;
                        
                        // Occupancy = (Total Malam Terjual / (Total Kamar * Hari dalam sebulan)) * 100
                        $capacityMalam = $ad['total_kamar'] * $daysInMonth;
                        $occupancy = ($capacityMalam > 0) ? round(($ad['total_malam_terjual'] / $capacityMalam) * 100, 2) : 0;
                        
                        // Conditional Formatting Tailwind
                        $occClass = 'text-stone-600';
                        if ($occupancy < 50) $occClass = 'text-rose-600 font-bold'; 
                        elseif ($occupancy > 80) $occClass = 'text-emerald-600 font-bold'; 
                    ?>
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-8 py-4 text-sm font-medium text-stone-900"><?= $ad['tipe_kamar'] ?></td>
                        <td class="px-8 py-4 text-sm text-stone-600 text-right">Rp <?= number_format($ad['total_pendapatan'], 0, ',', '.') ?></td>
                        <td class="px-8 py-4 text-sm text-stone-600 text-right">Rp <?= number_format($adr, 0, ',', '.') ?></td>
                        <td class="px-8 py-4 text-sm text-center <?= $occClass ?>"><?= $occupancy ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FITUR KHUSUS 1: Kalender Ketersediaan -->
    <div class="bg-white border border-stone-200 shadow-sm">
        <div class="px-8 py-6 border-b border-stone-200 bg-stone-900 flex justify-between items-center">
            <h5 class="text-lg font-serif italic text-white m-0 flex items-center gap-3">
                <i data-lucide="calendar" class="w-5 h-5 text-amber-500"></i>
                Kalender Ketersediaan Kamar (<?= $days_to_show ?> Hari Kedepan)
            </h5>
            <div class="flex gap-4 text-[10px] uppercase tracking-widest font-bold">
                <span class="flex items-center gap-2 text-emerald-400"><div class="w-2 h-2 rounded-full bg-emerald-400"></div> Tersedia</span>
                <span class="flex items-center gap-2 text-rose-400"><div class="w-2 h-2 rounded-full bg-rose-400"></div> Booked</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-center border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-[10px] uppercase tracking-widest text-stone-500">
                        <th class="px-6 py-4 font-bold text-left border-r border-stone-200">Kamar</th>
                        <?php foreach($dates as $d): ?>
                            <th class="px-4 py-4 font-bold min-w-[80px] border-r border-stone-100 last:border-r-0"><?= date('d M', strtotime($d)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach($rooms as $r): ?>
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-6 py-4 text-left border-r border-stone-200 bg-white">
                                <span class="block text-sm font-bold text-stone-900"><?= $r['room_number'] ?></span>
                                <span class="block text-[10px] uppercase tracking-widest text-stone-400 mt-1"><?= $r['type_name'] ?></span>
                            </td>
                            <?php foreach($dates as $d): ?>
                                <?php 
                                    // Cek status statis (misal maintenance)
                                    if ($r['status'] == 'maintenance') {
                                        echo '<td class="px-2 py-2 border-r border-stone-100 last:border-r-0 bg-amber-50">';
                                        echo '<span class="text-[10px] uppercase tracking-widest font-bold text-amber-700">Maint</span></td>';
                                    } else {
                                        // Cek status booking grid
                                        $is_booked = isRoomBookedOnDate($r['id'], $d, $activeBookings);
                                        if ($is_booked) {
                                            echo '<td class="px-2 py-2 border-r border-stone-100 last:border-r-0 bg-rose-50">';
                                            echo '<i data-lucide="x" class="w-4 h-4 mx-auto text-rose-500 opacity-50" title="Sudah Dipesan"></i></td>';
                                        } else {
                                            echo '<td class="px-2 py-2 border-r border-stone-100 last:border-r-0 bg-emerald-50/30">';
                                            echo '<i data-lucide="check" class="w-4 h-4 mx-auto text-emerald-500 opacity-50" title="Tersedia"></i></td>';
                                        }
                                    }
                                ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
