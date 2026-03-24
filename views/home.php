<?php
require_once __DIR__ . '/../classes/Room.php';
$roomModel = new Room();
$rooms = $roomModel->getAllWithTypes();
?>

<!-- Kontainer Utama Beranda Tailwind -->
<div class="bg-[#FDFCF8] text-[#1A1A1A] selection:bg-amber-200 font-sans" style="margin-top: -80px;">

    <!-- Hero Section -->
    <section id="discover" class="relative pt-32 lg:pt-48 pb-20 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 relative z-10">
                <div class="inline-flex items-center gap-3 mb-6">
                    <span class="h-[1px] w-12 bg-amber-700"></span>
                    <span class="text-[12px] font-bold tracking-[0.3em] uppercase text-amber-800">A New Standard of Luxury</span>
                </div>
                <h2 class="text-6xl md:text-8xl font-serif leading-[0.9] mb-8 tracking-tighter">
                    Escape to a <br/>
                    <span class="italic text-stone-400">Perfect</span> Sanctuary
                </h2>
                <p class="text-lg text-stone-500 max-w-lg leading-relaxed mb-10 font-light">
                    Rasakan harmoni antara desain kontemporer dan kenyamanan abadi di jantung kota. Setiap sudut dirancang untuk menginspirasi jiwa Anda.
                </p>
                
                <!-- Minimalist Actions -->
                <div class="bg-white p-2 shadow-2xl shadow-stone-200 border border-stone-100 rounded-sm flex flex-col md:flex-row gap-4">
                    <div class="flex-1 flex items-center px-6 py-4 border-r border-stone-100">
                        <i data-lucide="calendar" class="w-4 h-4 text-amber-700 mr-4"></i>
                        <div class="text-left">
                            <span class="block text-[10px] uppercase font-bold text-stone-400 tracking-wider">Plan Visit</span>
                            <span class="text-sm font-medium">Buka Kapan Saja</span>
                        </div>
                    </div>
                    <div class="flex-1 flex items-center px-6 py-4">
                        <i data-lucide="users" class="w-4 h-4 text-amber-700 mr-4"></i>
                        <div class="text-left">
                            <span class="block text-[10px] uppercase font-bold text-stone-400 tracking-wider">Explore</span>
                            <span class="text-sm font-medium">Eksklusif & Private</span>
                        </div>
                    </div>
                    <a href="#suites" class="bg-stone-900 text-white px-10 py-4 font-bold text-[13px] uppercase tracking-widest hover:bg-amber-800 transition-all flex items-center justify-center gap-3 no-underline">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Explore
                    </a>
                </div>
            </div>
            
            <div class="lg:col-span-5 relative">
                <div class="aspect-[4/5] rounded-[4rem] overflow-hidden shadow-2xl rotate-2 hover:rotate-0 transition-transform duration-700">
                    <img 
                        src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=1000" 
                        class="w-full h-full object-cover"
                        alt="Main Hotel View"
                    />
                </div>
                <!-- Floating Info -->
                <div class="absolute -bottom-10 -left-10 bg-amber-50 p-8 border border-amber-100 hidden md:block">
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <div class="flex">
                            <i data-lucide="star" class="w-3 h-3 fill-amber-600 text-amber-600"></i>
                            <i data-lucide="star" class="w-3 h-3 fill-amber-600 text-amber-600"></i>
                            <i data-lucide="star" class="w-3 h-3 fill-amber-600 text-amber-600"></i>
                            <i data-lucide="star" class="w-3 h-3 fill-amber-600 text-amber-600"></i>
                            <i data-lucide="star" class="w-3 h-3 fill-amber-600 text-amber-600"></i>
                        </div>
                    </div>
                    <p class="text-2xl font-serif italic m-0 text-stone-900">"Best Boutique Hotel"</p>
                    <p class="text-[10px] uppercase tracking-widest mt-4 text-stone-400 m-0">— Travelers' Choice</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section id="suites" class="max-w-7xl mx-auto px-6 py-32 border-t border-stone-100">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8">
            <div>
                <h3 class="text-4xl font-serif mb-4 tracking-tight">Koleksi Kamar Pilihan</h3>
                <div class="flex gap-8 text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
                    <button class="text-amber-800 border-b border-amber-800 pb-1 bg-transparent border-0 cursor-pointer">All Collections</button>
                    <button class="hover:text-stone-800 bg-transparent border-0 px-0 cursor-pointer">Filter (Opsional)</button>
                </div>
            </div>
            <p class="text-stone-400 text-[13px] max-w-xs leading-relaxed italic m-0">
                "Seni menginap adalah tentang detail yang tidak terlihat namun sangat dirasakan."
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-16">
            <?php $idx = 0; foreach ($rooms as $room): $idx++; 
                $photoFile = !empty($room['image']) ? htmlspecialchars($room['image']) : 'default.jpg';
                $img_url = "/reservasi_hotel/public/assets/img/" . $photoFile;
            ?>
            <div class="group <?= $idx % 3 === 2 ? 'md:mt-16' : '' ?>">
                <div class="relative overflow-hidden mb-8 aspect-[3/4]">
                    <img 
                        src="<?= $img_url ?>" 
                        alt="<?= htmlspecialchars($room['type_name']) ?>"
                        class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                        onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Kamar';"
                    />
                    <div class="absolute top-6 left-6">
                        <span class="bg-white/30 backdrop-blur-md text-white text-[10px] font-bold uppercase tracking-widest px-4 py-2 border border-white/20">
                            <?= htmlspecialchars($room['type_name']) ?>
                        </span>
                    </div>
                    <?php if ($room['status'] != 'available'): ?>
                    <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-[2px] flex items-center justify-center">
                        <span class="text-white text-[11px] font-bold uppercase tracking-[0.4em] border border-white/50 px-8 py-3">Fully Booked</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-baseline mb-3">
                        <h4 class="text-2xl font-serif group-hover:italic transition-all duration-300 m-0"><?= htmlspecialchars($room['type_name']) ?></h4>
                        <span class="text-[10px] uppercase font-bold text-stone-400 tracking-tighter">Room <?= $room['room_number'] ?> | Lt <?= $room['floor'] ?></span>
                    </div>
                    
                    <div class="flex items-center gap-1 mb-2">
                        <!-- Rating Bintang -->
                        <div class="flex">
                        <?php if ($room['avg_rating'] > 0): ?>
                            <?php 
                            $rounded_rating = round($room['avg_rating']); 
                            for($i=1; $i<=5; $i++) {
                                if($i <= $rounded_rating) {
                                    echo '<i data-lucide="star" class="w-3 h-3 fill-amber-500 text-amber-500"></i>';
                                } else {
                                    echo '<i data-lucide="star" class="w-3 h-3 text-stone-300"></i>';
                                }
                            }
                            ?>
                            <span class="text-xs text-stone-400 ml-2">(<?= $room['avg_rating'] ?>)</span>
                        <?php else: ?>
                            <span class="text-xs text-stone-400 italic font-light">Belum ada ulasan</span>
                        <?php endif; ?>
                        </div>
                    </div>
                    
                    <p class="text-stone-500 font-light text-sm leading-relaxed mb-4" style="min-height: 3em;">
                        Fasilitas: <?= htmlspecialchars($room['amenities']) ?><br>
                        Max: <span class="font-medium text-stone-800"><?= $room['max_occupancy'] ?> Orang</span>
                    </p>
                    <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                        <div>
                            <span class="text-[11px] text-stone-400 uppercase tracking-widest block mb-1 font-bold">Starts from</span>
                            <span class="text-xl font-medium tracking-tight">Rp <?= number_format($room['base_price'], 0, ',', '.') ?></span>
                        </div>
                        
                        <?php if ($room['status'] == 'available'): ?>
                        <a href="index.php?page=booking&room_id=<?= $room['id'] ?>" class="h-12 w-12 rounded-full border border-stone-200 flex items-center justify-center group-hover:bg-stone-900 group-hover:border-stone-900 transition-all duration-500 hover:text-white no-underline text-stone-900">
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:text-white transition-colors"></i>
                        </a>
                        <?php else: ?>
                        <button disabled class="h-12 w-12 rounded-full border border-stone-200 bg-stone-100 flex items-center justify-center opacity-50 cursor-not-allowed">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-stone-400"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Experience Section -->
    <section id="experiences" class="bg-stone-900 text-stone-100 py-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-20 items-center">
            <div class="space-y-12">
                <h3 class="text-5xl font-serif leading-tight m-0 mb-8">Beyond just a <br/> <span class="italic text-amber-200">Room</span></h3>
                <div class="grid grid-cols-2 gap-8 mb-10">
                    <div class="space-y-4">
                        <div class="w-12 h-[1px] bg-amber-600 mb-4"></div>
                        <h5 class="font-bold text-[12px] uppercase tracking-widest m-0">Le Spa De Luxe</h5>
                        <p class="text-stone-400 text-sm font-light leading-relaxed m-0 mt-3">Terapi tradisional yang dipadukan dengan teknik modern.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="w-12 h-[1px] bg-amber-600 mb-4"></div>
                        <h5 class="font-bold text-[12px] uppercase tracking-widest m-0">Sky Bar & Grill</h5>
                        <p class="text-stone-400 text-sm font-light leading-relaxed m-0 mt-3">Cocktail buatan tangan dengan pemandangan 360 derajat.</p>
                    </div>
                </div>
                <button class="px-10 py-4 border border-stone-700 hover:border-amber-200 hover:text-amber-200 text-[11px] font-bold uppercase tracking-[0.3em] transition-all bg-transparent text-white cursor-pointer">
                    Discover Lifestyle
                </button>
            </div>
            <div class="relative">
                <div class="aspect-video bg-stone-800 rounded-sm overflow-hidden border-0">
                    <img 
                        src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&q=80&w=1000" 
                        class="w-full h-full object-cover opacity-60"
                        alt="Pool Experience"
                    />
                </div>
                <div class="absolute -bottom-8 -right-8 w-48 h-48 bg-stone-100 p-2 rounded-full flex items-center justify-center text-stone-900 text-center border-8 border-stone-900 shadow-2xl">
                    <span class="text-[10px] font-bold uppercase tracking-widest leading-tight">Poolside <br/> Serenity <br/> 2026</span>
                </div>
            </div>
        </div>
    </section>

</div>