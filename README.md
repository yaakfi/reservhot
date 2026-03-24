

## 💻 Panduan Instalasi dan Menjalankan Aplikasi

Langkah-langkah menyalakan peladen di komputer (*Localhost*):

### 1. Salin Direktori Proyek
Salin (*Clone / Extract*) keseluruhan folder hasil dari repositori ini, dan pastikan memindahkannya ke dalam sub-folder Root Web Server Anda:
- Bagi pengguna **XAMPP**: Pindahkan folder ke `C:\xampp\htdocs\reservasi_hotel`

### 2. Nyalakan Modul Server Lokal
Buka aplikasi kontrol **XAMPP Control Panel** miliki Anda.
Nyalakan (Klik **"Start"**) modul penggerak utama:
- ✅ **Apache**
- ✅ **MySQL**

### 3. Konfigurasi Database (Tanjakan `.sql` Dump)
1. Buka *browser* baru (Chrome/Edge/Firefox), lalu pergi ke mesin administrasi *database*: `http://localhost/phpmyadmin/`
2. Di barisan kiri, pada menu utama, ciptakan **Database Baru** dan berikan ekstensi penamaan tabel absolut yang diwajibkan oleh kode program:
   `db_reservasi_hotel` *(Tanpa tanda petik)*
3. Pilih *database* kosong yang baru jadi tersebut, lalu klik tabung fitur **"Import"** di bar atas atas antarmuka *phpMyAdmin*.
4. Pada kolom **"Choose File"**, pilih struktur peluncur DDL/DML *(database.sql)* bawaan yang ada di direktori utama `c:\xampp\htdocs\reservasi_hotel\database.sql`.
5. Tabrak perlindungan dengan mengklik **"Import"** / **"Go"** di laman menu paling bawah pijakan phpMyAdmin.
6. Rekayasa baris *dummy* per *table* beserta *password* enkripsinya secara otomatis terinjeksi.

### 4. Luncurkan Website Ke Luar
Konfigurabilitas terpasang, pangkalan data (*database*) pun telah terkoneksi sejati! 
Panggil pranala *(URL)* web berikut ke penelusur komputer layar depan Anda:
👉 `http://localhost/reservasi_hotel/`

---

## 🔑 Data Autentikasi Pengelola & Tamu *(Dummy Accounts)*
Gunakan identitas (*credentials*) rahasia di bawah ini guna membedah sistem dan *login* ke Dasbor:

**Akun Administrator (Manajemen & Laporan Pemasukan):**
*   **Username**: `admin`
*   **Password**: `admin123`

**Akun Resepsionis Utama:**
*   **Username**: `receptionist1`
*   **Password**: `recep123`

**Akun Tamu Simulasi (Guest 1):**
*   **Username**: `guest1`
*   **Password**: `gpass1`

**Akun Tamu Simulasi (Guest 2):**
*   **Username**: `guest2`
*   **Password**: `gpass2`

---

*Proyek Pengembangan Web Ujian Tingkat Akhir. Dibuat dengan presisi tinggi demi arsitektur sistem penginapan yang handal dan murni.*
