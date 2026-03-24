<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die("Akses Ditolak.");
}

$booking_id = $_GET['id'] ?? null;
if (!$booking_id) die("ID Booking Tidak Valid");

$db = Database::getInstance()->getConnection();
// Mengambil detail lengkap Invoice (Join 4 tabel)
$query = "
    SELECT b.*, u.username, u.email, u.phone, r.room_number, r.floor,
           rt.name as room_type, rt.base_price
    FROM bookings b
    JOIN users u ON b.guest_id = u.id
    JOIN rooms r ON b.room_id = r.id
    JOIN room_types rt ON r.room_type_id = rt.id
    WHERE b.id = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$booking_id]);
$invoice = $stmt->fetch();

if (!$invoice) die("Data invoice tidak ditemukan.");

// Menghitung lama menginap
$datetime1 = new DateTime($invoice['check_in']);
$datetime2 = new DateTime($invoice['check_out']);
$interval = $datetime1->diff($datetime2);
$nights = $interval->format('%a');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #INV-<?= str_pad($invoice['id'], 6, '0', STR_PAD_LEFT) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap');
        
        body { font-family: 'Inter', sans-serif; background-color: #f5f5f4; }
        .font-serif { font-family: 'Playfair Display', serif; }
        
        @media print {
            body { background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-border-black { border-color: black !important; }
            .print-text-black { color: black !important; }
            .print-bg-gray { background-color: #f3f4f6 !important; }
        }
    </style>
</head>
<body class="antialiased text-stone-900 py-10 print:py-0 print:bg-white">

<div class="max-w-4xl mx-auto mb-8 text-center no-print">
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-stone-900 text-white text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Cetak Dokumen Invoice
    </button>
</div>

<div class="max-w-4xl mx-auto bg-white p-12 shadow-xl print:shadow-none print:p-0">
    <!-- Header -->
    <div class="flex justify-between items-start border-b-2 border-stone-900 pb-8 mb-10 print:border-black">
        <div>
            <h1 class="text-4xl font-serif tracking-tight text-stone-900 font-bold print-text-black">HotelRes Official</h1>
            <p class="text-xs text-stone-500 mt-3 font-medium tracking-wide uppercase print-text-black">Sistem Reservasi Hotel & Homestay</p>
            <p class="text-[11px] text-stone-500 mt-1 print-text-black">Jl. Teknologi Cerdas No. 404, Purwokerto<br>Telp: (0281) 123456</p>
        </div>
        <div class="text-right">
            <h2 class="text-4xl font-serif text-amber-800 italic pr-2 print-text-black">Invoice</h2>
            <div class="mt-4 text-[10px] uppercase tracking-widest text-stone-500 print-text-black">
                <p class="mb-1"><span class="font-bold text-stone-900 print-text-black">No. Referensi:</span> #INV-<?= str_pad($invoice['id'], 6, '0', STR_PAD_LEFT) ?></p>
                <p class="mb-1"><span class="font-bold text-stone-900 print-text-black">Tanggal Cetak:</span> <?= date('d M Y') ?></p>
                <p><span class="font-bold text-stone-900 print-text-black">Status:</span> <?= strtoupper($invoice['status']) ?></p>
            </div>
        </div>
    </div>

    <!-- Info Tamu -->
    <div class="bg-stone-50 p-6 mb-10 border-l-4 border-amber-800 print-bg-gray print:border-black">
        <h3 class="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-4 print-text-black">Ditagihkan Kepada:</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-lg font-serif text-stone-900 print-text-black"><?= htmlspecialchars($invoice['username']) ?></p>
            </div>
            <div class="text-right text-xs text-stone-600 print-text-black">
                <p><span class="font-medium text-stone-400 print-text-black">Email:</span> <?= htmlspecialchars($invoice['email']) ?></p>
                <p class="mt-1"><span class="font-medium text-stone-400 print-text-black">Telepon:</span> <?= htmlspecialchars($invoice['phone']) ?></p>
            </div>
        </div>
    </div>

    <!-- Rincian Pesanan -->
    <div class="mb-12">
        <h3 class="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-4 print-text-black">Rincian Pemesanan</h3>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-stone-200 print-border-black text-[10px] uppercase tracking-widest text-stone-400 print-text-black">
                    <th class="py-3 font-bold">Deskripsi Kamar</th>
                    <th class="py-3 font-bold text-center">Jadwal Menginap</th>
                    <th class="py-3 font-bold text-center">Durasi</th>
                    <th class="py-3 font-bold text-right">Kapasitas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 print:divide-stone-300">
                <tr>
                    <td class="py-5">
                        <span class="block text-sm font-bold text-stone-900 print-text-black"><?= htmlspecialchars($invoice['room_type']) ?></span>
                        <span class="block text-xs text-stone-500 mt-1 print-text-black">No. Kamar: <?= htmlspecialchars($invoice['room_number']) ?> (Lantai <?= $invoice['floor'] ?>)</span>
                    </td>
                    <td class="py-5 text-center text-xs text-stone-600 print-text-black">
                        <?= date('d M Y', strtotime($invoice['check_in'])) ?> <br> s/d <br> <?= date('d M Y', strtotime($invoice['check_out'])) ?>
                    </td>
                    <td class="py-5 text-center text-sm font-medium text-stone-900 print-text-black"><?= $nights ?> Malam</td>
                    <td class="py-5 text-right text-sm text-stone-600 print-text-black"><?= $invoice['guests_count'] ?> Orang</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Kalkulasi -->
    <div class="flex justify-end mb-16">
        <div class="w-full sm:w-1/2">
            <table class="w-full text-right text-sm">
                <tbody>
                    <tr class="border-b border-stone-100 print:border-stone-300">
                        <td class="py-3 text-stone-500 print-text-black">Subtotal / Malam:</td>
                        <td class="py-3 font-medium text-stone-900 print-text-black">Rp <?= number_format($invoice['base_price'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="py-4 text-[10px] font-bold uppercase tracking-widest text-stone-900 print-text-black">Grand Total:</td>
                        <td class="py-4 text-2xl font-serif font-bold text-amber-800 print-text-black">Rp <?= number_format($invoice['total_price'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Catatan -->
    <div class="pt-8 border-t border-stone-200 text-center print:border-stone-300 print:pt-4">
        <p class="text-[10px] text-stone-400 uppercase tracking-widest print-text-black">Catatan Administratif</p>
        <p class="text-xs text-stone-500 mt-2 italic font-serif print-text-black">Invoice ini sah diterbitkan secara otomatis oleh sistem komputerisasi HotelRes dan tidak memerlukan tanda tangan basah. Apabila terdapat pertanyaan, silakan hubungi bagian Resepsionis kami.</p>
    </div>
</div>

</body>
</html>
