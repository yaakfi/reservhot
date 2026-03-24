</main> <!-- End of main content area -->

<!-- Footer Editorial -->
<footer class="py-24 px-6 border-t border-stone-100 bg-white">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start gap-16">
        <div class="max-w-xs">
            <h1 class="text-3xl font-serif italic font-bold mb-6 m-0 text-stone-900">HotelRes.</h1>
            <p class="text-stone-400 text-sm font-light leading-relaxed m-0 mt-4">
                Sebuah hunian yang menggabungkan kemewahan dengan kehangatan rumah. Terletak strategis namun tetap menawarkan ketenangan absolut.
            </p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-16">
            <div class="space-y-4">
                <h6 class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-900 mb-4 m-0">Menjelajah</h6>
                <ul class="text-sm text-stone-500 space-y-3 font-light list-none p-0 m-0">
                    <li><a href="index.php?page=home" class="hover:text-amber-800 text-stone-500 transition-colors" style="text-decoration:none;">Beranda Kamar</a></li>
                    <li><a href="index.php?page=my_bookings" class="hover:text-amber-800 text-stone-500 transition-colors" style="text-decoration:none;">Pesanan Saya</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h6 class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-900 mb-4 m-0">Kontak Resmi</h6>
                <ul class="text-sm text-stone-500 space-y-3 font-light list-none p-0 m-0">
                    <li>Sudirman St. 14, Jakarta</li>
                    <li>+62 21 555 0123</li>
                    <li>hello@hotelres.com</li>
                </ul>
            </div>
            <div class="space-y-6">
                <h6 class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-900 mb-4 m-0">Berjejaring</h6>
                <div class="flex gap-4 mt-4">
                    <i data-lucide="instagram" class="w-5 h-5 text-stone-400 hover:text-amber-700 cursor-pointer transition-colors"></i>
                    <i data-lucide="facebook" class="w-5 h-5 text-stone-400 hover:text-amber-700 cursor-pointer transition-colors"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto mt-24 pt-8 border-t border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <p class="text-[10px] uppercase font-bold tracking-widest text-stone-400 m-0">© <?= date('Y') ?> HotelRes Collective. Hak Cipta Dilindungi Undang-Undang.</p>
        <div class="flex gap-8 text-[10px] uppercase font-bold tracking-widest text-stone-400">
            <span class="cursor-pointer hover:text-stone-600 transition-colors">Privacy Policy</span>
            <span class="cursor-pointer hover:text-stone-600 transition-colors">Terms of Use</span>
        </div>
    </div>
</footer>

<script>
    // Membangkitkan icon lucide di seluruh halaman akhir DOM
    lucide.createIcons();

    // Utilitas Global untuk Modal Kustom (Pengganti Bootstrap Modal)
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-active'); // Menghentikan scroll pada body
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-active');
        }
    }
    
    // Auto-Close jika Backdrop Modal luar di klik
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = "none";
            document.body.classList.remove('modal-active');
        }
    }
</script>
</body>
</html>
