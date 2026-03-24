<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/RoomType.php';

$auth = new Auth();
if (!$auth->hasRole(['admin'])) { // Sengaja hanya admin utama
    header("Location: index.php?page=home");
    exit;
}

$roomTypeModel = new RoomType();

// Setup Pagination (Syarat Wajib CRUD)
$limit = 10; // Minimal 10 data per halaman
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

$types = $roomTypeModel->getAllPaginated($limit, $offset);
$totalData = $roomTypeModel->countAll();
$totalPages = ceil($totalData / $limit);

// Handle Delete (Memenuhi syarat Hapus data via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Karena ini project simple, kita tidak memusingkan unlink foto server (Cuma DB)
    if ($roomTypeModel->delete($_POST['delete_id'])) {
        $_SESSION['flash_msg'] = "Tipe Kamar berhasil dihapus.";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_msg'] = "Gagal (Mungkin masih terikat dengan Kamar Aktif).";
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: index.php?page=admin_room_types");
    exit;
}

// Handle Add / Edit form upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add', 'edit'])) {
    
    // VALIDASI SERVER-SIDE (SYARAT WAJIB)
    // Field wajib, angka positif dll
    $basePrice = (float)$_POST['base_price'];
    $maxOccupancy = (int)$_POST['max_occupancy'];
    if (empty($_POST['name']) || empty($_POST['amenities'])) {
        $_SESSION['flash_msg'] = "Gagal: Nama dan Fasilitas wajib diisi!";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=admin_room_types");
        exit;
    }
    if ($basePrice <= 0 || $maxOccupancy <= 0) {
        $_SESSION['flash_msg'] = "Gagal: Harga dan Kapasitas harus berupa angka positif!";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=admin_room_types");
        exit;
    }

    // PHP File Upload Logic
    $fileName = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($_FILES['photo']['name']);
        $ext = strtolower($fileInfo['extension']);
        
        if (in_array($ext, $allowed)) {
            $fileName = uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../public/assets/img/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
        } else {
             $_SESSION['flash_msg'] = "Gagal Upload: Tipe file tidak didukung.";
             $_SESSION['flash_type'] = "danger";
             header("Location: index.php?page=admin_room_types");
             exit;
        }
    }

    if ($_POST['action'] === 'add') {
        if ($fileName === "") $fileName = "default.jpg";
        
        if ($roomTypeModel->createType($_POST['name'], $_POST['description'], $_POST['base_price'], $_POST['max_occupancy'], $_POST['amenities'], $fileName)) {
            $_SESSION['flash_msg'] = "Tipe Kamar (beserta foto) berhasil ditambahkan.";
            $_SESSION['flash_type'] = "success";
        }
    } else {
        if ($roomTypeModel->updateType($_POST['type_id'], $_POST['name'], $_POST['description'], $_POST['base_price'], $_POST['max_occupancy'], $_POST['amenities'], $fileName)) {
            $_SESSION['flash_msg'] = "Tipe Kamar berhasil diperbarui.";
            $_SESSION['flash_type'] = "success";
        }
    }
    header("Location: index.php?page=admin_room_types");
    exit;
}
?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end border-b border-stone-200 pb-6 gap-4">
        <div>
            <h1 class="text-4xl font-serif tracking-tight text-stone-900">Katalog Tipe Kamar</h1>
            <p class="text-stone-500 font-light text-sm mt-2">Atur klasifikasi tarif dasar, nama kamar, relasi fasilitas, hingga sampul media interaktif.</p>
        </div>
        <button type="button" onclick="openModal('addTypeModal')" class="px-6 py-3 bg-stone-900 text-white text-[11px] font-bold uppercase tracking-widest hover:bg-amber-800 transition-colors flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Master Tipe
        </button>
    </div>

    <!-- Tabel Master -->
    <div class="bg-white border border-stone-200 shadow-sm overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-[10px] uppercase tracking-widest text-stone-500">
                        <th class="px-6 py-5 font-bold">Potret Sampul</th>
                        <th class="px-6 py-5 font-bold">Kategori Kelas</th>
                        <th class="px-6 py-5 font-bold">Harga Rata-Rata Minimum</th>
                        <th class="px-6 py-5 font-bold">Ketersediaan Fitur</th>
                        <th class="px-6 py-5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if(empty($types)): ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-stone-400 italic font-serif">Katalog masih kosong.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($types as $t): ?>
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <?php $imgFile = !empty($t['image']) ? $t['image'] : 'default.jpg'; ?>
                                <img src="/reservasi_hotel/public/assets/img/<?= $imgFile ?>" class="w-24 h-16 object-cover border border-stone-200" alt="Room Cover" onerror="this.onerror=null; this.src='https://placehold.co/120x80/292524/ffffff?text=X';">
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-lg font-serif text-stone-900"><?= htmlspecialchars($t['name']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-amber-700 block">Rp <?= number_format($t['base_price'], 0, ',', '.') ?> <span class="text-[10px] text-stone-500 font-normal">/ malam</span></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-stone-600 block max-w-xs truncate"><?= htmlspecialchars($t['amenities']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="openModal('editTypeModal<?= $t['id'] ?>')" class="p-2 text-amber-700 hover:bg-amber-50 rounded-md transition-colors border border-transparent hover:border-amber-200" title="Edit & Ganti Foto">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Status Hapus Permanen. Yakin ingin menghapus ini? (Hati-hati jika relasi data sedang dipakai)');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-md transition-colors border border-transparent hover:border-rose-200" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Layar Dialog Tailwind -->
                        <div id="editTypeModal<?= $t['id'] ?>" class="modal-overlay">
                            <div class="bg-white max-w-2xl w-full p-8 shadow-2xl relative border border-stone-100 max-h-[90vh] overflow-y-auto">
                                <button type="button" onclick="closeModal('editTypeModal<?= $t['id'] ?>')" class="absolute top-4 right-4 text-stone-400 hover:text-stone-900">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                                <h5 class="text-2xl font-serif italic mb-2 text-stone-900">Panel Modifikasi: <?= htmlspecialchars($t['name']) ?></h5>
                                <p class="text-xs text-stone-500 mb-6 pb-4 border-b border-stone-100">Segala bentuk perubahan informasi disini (termasuk foto) akan mengubah tampilan di Katalog Beranda seketika saat disimpan.</p>
                                
                                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="type_id" value="<?= $t['id'] ?>">
                                    
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Penamaan Produk (e.g. Deluxe Room)</label>
                                            <input type="text" name="name" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required value="<?= htmlspecialchars($t['name']) ?>">
                                        </div>
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Nilai Investasi Rata-rata (Rp Dasar)</label>
                                            <input type="number" name="base_price" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required value="<?= $t['base_price'] ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Batas Okupansi (Tamu Max)</label>
                                            <input type="number" name="max_occupancy" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required value="<?= $t['max_occupancy'] ?>">
                                        </div>
                                        <div class="relative">
                                            <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Pembaruan Resolusi Media (Abaikan bila tetap)</label>
                                            <input type="file" name="photo" class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 mt-1 cursor-pointer" accept="image/png, image/jpeg, image/webp">
                                            <small class="text-[10px] text-stone-400 mt-1 block">Dimensi file .JPG/.PNG rasio 16:9 direkomendasikan. Max 2MB.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="relative">
                                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Daftar Ekstraksi Fasilitas Utama (Pisahkan via koma)</label>
                                        <textarea name="amenities" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="2" required><?= htmlspecialchars($t['amenities']) ?></textarea>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Deskripsi Detail Lingkungan Interior Kamar</label>
                                        <textarea name="description" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="3"><?= htmlspecialchars($t['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="pt-4 flex gap-4">
                                        <button type="submit" class="flex-1 bg-stone-900 text-white py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors">
                                            Sinkronisasi Ulang Data & Media
                                        </button>
                                        <button type="button" onclick="closeModal('editTypeModal<?= $t['id'] ?>')" class="px-8 bg-transparent border border-stone-300 text-stone-600 py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:text-stone-900 transition-colors">
                                            Tarik Diri
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
                <a href="<?= ($p <= 1) ? '#' : 'index.php?page=admin_room_types&p='.($p-1) ?>" class="px-3 py-2 border <?= ($p <= 1) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="index.php?page=admin_room_types&p=<?= $i ?>" class="px-4 py-2 border <?= ($i == $p) ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300 text-stone-600 hover:border-stone-900 text-white hover:text-stone-900 bg-transparent' ?> text-sm font-medium transition-colors no-underline" style="<?= ($i != $p) ? 'color:#57534e;' : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li>
                <a href="<?= ($p >= $totalPages) ? '#' : 'index.php?page=admin_room_types&p='.($p+1) ?>" class="px-3 py-2 border <?= ($p >= $totalPages) ? 'border-stone-200 text-stone-300 cursor-not-allowed' : 'border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900' ?> transition-colors no-underline">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Modal Penambahan Tipe Utama -->
<div id="addTypeModal" class="modal-overlay">
    <div class="bg-white max-w-2xl w-full p-8 shadow-2xl relative border border-stone-100 max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeModal('addTypeModal')" class="absolute top-4 right-4 text-stone-400 hover:text-stone-900">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h5 class="text-2xl font-serif italic mb-2 text-stone-900">Tambah Seri / Kelas Kamar Baru</h5>
        <p class="text-xs text-stone-500 mb-6 pb-4 border-b border-stone-100">Katalogkan identitas, kelengkapan pendamping, dan rupa ekspektasi visual dari seri baru properti Anda.</p>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-2 gap-8">
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Kategori Produk (e.g. Presidential Suite)</label>
                    <input type="text" name="name" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required>
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Nilai Harga Pembukaan (Rp Minimum)</label>
                    <input type="number" name="base_price" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-8">
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Kapasitas Penuh (Batas Orang)</label>
                    <input type="number" name="max_occupancy" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm font-medium" required>
                </div>
                <div class="relative">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Unggah Profil Visual Induk (Zorba Form)</label>
                    <input type="file" name="photo" class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 mt-1 cursor-pointer" accept="image/png, image/jpeg, image/webp" required>
                    <small class="text-[10px] text-stone-400 mt-1 block">Wajib (Max 2MB format standar gambar Web).</small>
                </div>
            </div>
            
            <div class="relative">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Rincian Fasilitas Kamar (Pemisahan titik koma/koma standar)</label>
                <textarea name="amenities" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="2" required placeholder="Luxury Bath, King-Size Bed, Minibar, Smart TV"></textarea>
            </div>
            <div class="relative">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Kalimat Ringkasan Deskriptif (Opsional)</label>
                <textarea name="description" class="w-full bg-transparent border-0 border-b border-stone-300 py-2 focus:ring-0 focus:border-stone-900 transition-colors text-stone-900 text-sm resize-none" rows="3" placeholder="Sentuhan klasik membaur asimilasi estetika arsitektur moderen..."></textarea>
            </div>
            
            <div class="pt-4 flex gap-4">
                <button type="submit" class="flex-1 bg-stone-900 text-white py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-amber-800 transition-colors">
                    Validasi & Publikasikan
                </button>
                <button type="button" onclick="closeModal('addTypeModal')" class="px-8 bg-transparent border border-stone-300 text-stone-600 py-4 text-[11px] font-bold uppercase tracking-[0.2em] hover:text-stone-900 transition-colors">
                    Singkirkan Form
                </button>
            </div>
        </form>
    </div>
</div>
