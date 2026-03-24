<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Room.php';

$auth = new Auth();
if (!$auth->hasRole(['admin', 'receptionist'])) {
    header("Location: index.php?page=home");
    exit;
}

$roomModel = new Room();

// Pengaturan Halaman Limits
$limit = 10; 
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

$rooms = $roomModel->getAllWithTypesPaginated($limit, $offset);
$totalData = $roomModel->countAll();
$totalPages = ceil($totalData / $limit);

// Mengambil list tipe kamar untuk form add/edit
$db = Database::getInstance()->getConnection();
$roomTypes = $db->query("SELECT id, name FROM room_types")->fetchAll();

// Handle Delete (Memenuhi syarat Hapus data via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if ($roomModel->delete($_POST['delete_id'])) {
        $_SESSION['flash_msg'] = "Kamar berhasil dihapus.";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_msg'] = "Gagal menghapus kamar.";
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: index.php?page=admin_rooms");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add', 'edit'])) {
    
    // Validasi Server Side PHP Native
    $floorInt = (int)$_POST['floor'];
    if (empty($_POST['room_number']) || empty($_POST['room_type_id'])) {
        $_SESSION['flash_msg'] = "Gagal: Nomor Kamar dan Tipe Kamar wajib terisi!";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=admin_rooms");
        exit;
    }
    if ($floorInt <= 0) {
         $_SESSION['flash_msg'] = "Gagal: Penempatan Lantai harus wajib berupa Angka dan Positif (> 0).";
         $_SESSION['flash_type'] = "danger";
         header("Location: index.php?page=admin_rooms");
         exit;
    }

    $data = [
        'room_number' => $_POST['room_number'],
        'room_type_id' => $_POST['room_type_id'],
        'floor' => $floorInt,
        'status' => $_POST['status'],
        'notes' => $_POST['notes']
    ];

    if ($_POST['action'] === 'add') {
        if ($roomModel->create($data)) {
            $_SESSION['flash_msg'] = "Kamar baru berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
        }
    } else {
        if ($roomModel->update($_POST['room_id'], $data)) {
            $_SESSION['flash_msg'] = "Kamar berhasil diperbarui.";
            $_SESSION['flash_type'] = "success";
        }
    }
    header("Location: index.php?page=admin_rooms");
    exit;
}
?>
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end border-b border-stone-200 pb-6 gap-4">
        <div>
            <h1 class="text-4xl font-serif tracking-tight text-stone-900">Manajemen Kamar</h1>
            <p class="text-stone-500 font-light text-sm mt-2">Inventarisasi fisik, tipe tarif, dan penentuan status harian kamar hotel.</p>
        </div>
        <button type="button" onclick="openModal('addRoomModal')" class="px-6 py-3 bg-stone-900 text-white text-[11px] font-bold uppercase tracking-widest hover:bg-amber-800 transition-colors flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kamar
        </button>
    </div>

    <!-- Tabel Data Kamar -->
    <div class="bg-white border border-stone-200 shadow-sm overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-[10px] uppercase tracking-widest text-stone-500">
                        <th class="px-6 py-5 font-bold">No. Kamar</th>
                        <th class="px-6 py-5 font-bold">Tipe & Spesifikasi</th>
                        <th class="px-6 py-5 font-bold text-center">Lantai</th>
                        <th class="px-6 py-5 font-bold text-center">Status</th>
                        <th class="px-6 py-5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if(empty($rooms)): ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-stone-400 italic font-serif">Belum ada data kamar terdaftar.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($rooms as $r): ?>
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-lg font-serif text-stone-900"><?= htmlspecialchars($r['room_number']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-stone-900 block"><?= htmlspecialchars($r['type_name']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-stone-600 block">Lantai <?= $r['floor'] ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                    if($r['status'] == 'available') echo '<span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-widest">Available</span>'; 
                                    elseif($r['status'] == 'occupied') echo '<span class="inline-block px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold uppercase tracking-widest">Occupied</span>'; 
                                    elseif($r['status'] == 'maintenance') echo '<span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold uppercase tracking-widest">Maintenance</span>'; 
                                ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="openModal('editRoomModal<?= $r['id'] ?>')" class="p-2 text-amber-700 hover:bg-amber-50 rounded-md transition-colors border border-transparent hover:border-amber-200" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Kamar Nomor <?= $r['room_number'] ?> secara permanen?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-md transition-colors border border-transparent hover:border-rose-200" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal Vanilla JS untuk setiap baris -->
                        <div id="editRoomModal<?= $r['id'] ?>" class="modal-overlay">
                            <div class="bg-white max-w-lg w-full p-8 shadow-2xl relative border border-stone-100 max-h-[90vh] overflow-y-auto">
                                <button type="button" onclick="closeModal('editRoomModal<?= $r['id'] ?>')" class="absolute top-4 right-4 text-stone-400 hover:text-stone-900">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                                <h5 class="text-2xl font-serif italic mb-2 text-stone-900">Perbarui Kamar No. <?= $r['room_number'] ?></h5>
                                <p class="text-xs text-stone-500 mb-6 pb-4 border-b border-stone-100">Sesuaikan spesifikasi atau jadwal perbaikan/status okupansi pada form di bawah ini.</p>
                                
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                    
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Nomor Kamar</label>
                                            <input type="text" name="room_number" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required value="<?= $r['room_number'] ?>">
                                        </div>
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Lantai</label>
                                            <input type="number" name="floor" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required value="<?= $r['floor'] ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Tipe Kamar Utama</label>
                                        <select name="room_type_id" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm" required>
                                            <?php foreach($roomTypes as $rt): ?>
                                                <option value="<?= $rt['id'] ?>" <?= ($rt['id'] == $r['room_type_id']) ? 'selected' : '' ?>><?= $rt['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Status Operasional Harian</label>
                                        <select name="status" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required>
                                            <option value="available" <?= ($r['status']=='available')?'selected':'' ?>>🟢 Siap Huni (Available)</option>
                                            <option value="occupied" <?= ($r['status']=='occupied')?'selected':'' ?>>🔴 Sedang Digunakan (Occupied)</option>
                                            <option value="maintenance" <?= ($r['status']=='maintenance')?'selected':'' ?>>🟡 Perbaikan (Maintenance)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Catatan Tambahan (Logistik/Perbaikan)</label>
                                        <textarea name="notes" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="2" placeholder="Tuliskan catatan opsional..."><?= $r['notes'] ?></textarea>
                                    </div>
                                    
                                    <div class="pt-4 flex gap-4">
                                        <button type="submit" class="flex-1 bg-stone-900 text-white py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors">
                                            Simpan Perubahan
                                        </button>
                                        <button type="button" onclick="closeModal('editRoomModal<?= $r['id'] ?>')" class="px-6 bg-transparent border border-stone-300 text-stone-600 py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:text-stone-900 transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Paginasi -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex justify-center pb-10">
        <ul class="flex items-center gap-2 list-none p-0 m-0">
            <li>
                <a href="<?= ($p <= 1) ? '#' : 'index.php?page=admin_rooms&p='.($p-1) ?>" class="px-3 py-2 border <?= ($p <= 1) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="index.php?page=admin_rooms&p=<?= $i ?>" class="px-4 py-2 border <?= ($i == $p) ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300 text-stone-600 hover:border-stone-900 text-white hover:text-stone-900 bg-transparent' ?> text-sm font-medium transition-colors no-underline" style="<?= ($i != $p) ? 'color:#57534e;' : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li>
                <a href="<?= ($p >= $totalPages) ? '#' : 'index.php?page=admin_rooms&p='.($p+1) ?>" class="px-3 py-2 border <?= ($p >= $totalPages) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Modal Penambahan Kamar -->
<div id="addRoomModal" class="modal-overlay">
    <div class="bg-white max-w-lg w-full p-8 shadow-2xl relative border border-stone-100 max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeModal('addRoomModal')" class="absolute top-4 right-4 text-stone-400 hover:text-stone-900">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h5 class="text-2xl font-serif italic mb-2 text-stone-900">Entri Kamar Baru</h5>
        <p class="text-xs text-stone-500 mb-6 pb-4 border-b border-stone-100">Daftarkan inventaris kamar fisik baru dengan detail yang akurat ke database sistem penginapan.</p>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-2 gap-6">
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Nomor Kamar</label>
                    <input type="text" name="room_number" required class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium">
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Posisi Lantai</label>
                    <input type="number" name="floor" required class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium">
                </div>
            </div>
            
            <div class="relative">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Tipe Kamar (Pengklasifikasian)</label>
                <select name="room_type_id" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm" required>
                    <?php foreach($roomTypes as $rt): ?>
                        <option value="<?= $rt['id'] ?>"><?= $rt['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="relative">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Status Registrasi Awal</label>
                <select name="status" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required>
                    <option value="available">🟢 Siap Huni (Available)</option>
                    <option value="maintenance">🟡 Dalam Persiapan Awal (Maintenance)</option>
                </select>
            </div>
            
            <div class="relative">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Catatan Kelengkapan</label>
                <textarea name="notes" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="2" placeholder="Furnitur belum lengkap, sedang dekorasi..."></textarea>
            </div>
            
            <div class="pt-4 flex gap-4">
                <button type="submit" class="flex-1 bg-stone-900 text-white py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors">
                    Publikasikan Kamar
                </button>
                <button type="button" onclick="closeModal('addRoomModal')" class="px-6 bg-transparent border border-stone-300 text-stone-600 py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:text-stone-900 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
