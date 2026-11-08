<?php
session_start();

// Koneksi ke Database Laragon
$conn = new mysqli("localhost", "root", "", "db_kopinyantuy");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 1. LOGIKA QR CODE: Tangkap parameter meja dari URL (Misal web.com/?meja=30)
$nomor_meja = isset($_GET['meja']) ? htmlspecialchars($_GET['meja']) : null;
if($nomor_meja) {
    // Simpan di sesi agar tidak hilang saat halaman direfresh
    $_SESSION['meja'] = $nomor_meja;
} else {
    // Jika tidak ada di URL, cek apakah sebelumnya sudah ada di sesi
    $nomor_meja = isset($_SESSION['meja']) ? $_SESSION['meja'] : null;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth opacity-0 transition-opacity duration-700" id="htmlRoot">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>KopiNyantuy | Premium Self Service & Order</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#3d5a32',
            primaryLight: '#527744',
            primaryDark: '#23331b',
            accent: '#a7d970',
            bgLight: '#f6f8f4',
            textMain: '#1a2318',
            textMuted: '#5d6e58'
          },
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
            heading: ['Space Grotesk', 'sans-serif']
          }
        }
      }
    }
  </script>
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.6);
      box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }
    .dark .glass-card {
      background: rgba(24, 31, 22, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3);
    }

    .loader { border-top-color: #3d5a32; animation: spin 0.8s cubic-bezier(0.6, 0.2, 0.4, 0.8) infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .reveal { opacity: 0; transform: translateY(20px); transition: all 0.6s ease-out; }
    .reveal.active { opacity: 1; transform: translateY(0); }
    
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #3d5a32; border-radius: 4px; }

    @media print {
      body * { visibility: hidden; }
      #modalStruk, #modalStruk * { visibility: visible; }
      #modalStruk { position: absolute; left: 0; top: 0; width: 100%; background: white !important; color: black !important; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body class="bg-bgLight dark:bg-[#111810] text-textMain dark:text-gray-100 font-sans transition-colors duration-300 pb-28">

  <!-- PRELOADER -->
  <div id="preloader" class="fixed inset-0 bg-bgLight dark:bg-[#111810] z-[10000] flex justify-center items-center transition-opacity duration-500">
    <div class="flex flex-col items-center gap-3">
      <div class="loader border-4 border-gray-200 dark:border-gray-800 rounded-full w-10 h-10"></div>
      <span class="font-heading text-xs font-bold tracking-widest text-primary dark:text-accent animate-pulse">KOPINYANTUY</span>
    </div>
  </div>
  
  <!-- SCROLL PROGRESS BAR -->
  <div id="scroll-progress" class="fixed top-0 left-0 h-1 bg-accent z-[10001] w-0 transition-all duration-150"></div>

  <!-- TOAST NOTIFICATION -->
  <div id="toast" class="fixed -bottom-20 left-1/2 -translate-x-1/2 w-[90%] max-w-xs bg-primaryDark text-white dark:bg-accent dark:text-primaryDark px-5 py-3 rounded-full font-bold shadow-2xl z-[9999] transition-all duration-300 text-center text-xs flex items-center justify-center gap-2 backdrop-blur-md">
    <span>🛒</span> <span id="toastMsg">Menu ditambahkan ke keranjang!</span>
  </div>

  <!-- FLOATING THEME SWITCHER -->
  <div class="fixed bottom-24 right-4 z-[9000] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-2 rounded-full border border-gray-200 dark:border-gray-700 shadow-xl">
    <button id="theme-btn" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold">
      <span id="theme-icon">🌙</span>
      <span id="theme-text" class="text-[11px]">Dark</span>
    </button>
  </div>
  
  <a href="https://wa.me/62895320078768" target="_blank" class="fixed bottom-6 right-4 bg-emerald-600 hover:bg-emerald-500 text-white p-3.5 rounded-full font-bold shadow-xl z-[9000] flex items-center justify-center transition-all">
    <span class="text-lg">💬</span>
  </a>

  <!-- NAVBAR HEADER -->
  <header class="sticky top-0 z-50 px-4 py-3 bg-primaryDark text-white shadow-lg">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-3">
      <div class="flex justify-between items-center w-full sm:w-auto gap-4">
        <a href="#" class="text-xl font-heading font-black tracking-tight flex items-center gap-2">
          <span>☕</span> <span>Kopi<span class="text-accent">Nyantuy</span></span>
        </a>
        
        <!-- 2. UI NOMOR MEJA DARI QR -->
        <?php if($nomor_meja): ?>
        <div class="bg-white/10 border border-white/20 px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1 shadow-inner">
           <span>📍</span> Meja <?= $nomor_meja ?>
        </div>
        <?php endif; ?>
      </div>
      
      <div class="w-full sm:w-auto">
        <input type="search" id="menuSearch" placeholder="🔍 Cari menu favorit..." class="w-full sm:w-64 px-4 py-2 rounded-full text-gray-900 bg-white focus:outline-none text-xs font-medium shadow-inner">
      </div>
    </div>
  </header>

  <!-- MAIN CONTAINER -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col gap-8">
    
    <!-- HERO SECTION -->
    <section id="hero" class="reveal glass-card rounded-2xl p-5 md:p-8 relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
      <div class="w-full md:w-3/5 z-10 text-left">
        <span class="inline-block bg-primary/10 dark:bg-accent/10 border border-primary/20 text-primary dark:text-accent text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-2">
          ☕ CAFE & COFFEE
        </span>
        <h1 class="font-heading text-2xl sm:text-4xl font-black my-2 text-primaryDark dark:text-white leading-tight">
          Ngopi Santai di <span class="text-primary dark:text-accent">KopiNyantuy</span>
        </h1>
        <p class="text-textMuted dark:text-gray-300 text-xs sm:text-sm mb-5 leading-relaxed">
          Tempat nyaman untuk menikmati kopi, ngobrol bersama teman, mengerjakan tugas, atau sekadar santai.
        </p>
        <div class="flex gap-3">
          <button onclick="document.getElementById('menuGrid').scrollIntoView();" class="bg-primary hover:bg-primaryDark text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md">
            Lihat Menu →
          </button>
          <a href="#kontak" class="border border-primary/40 text-primary dark:border-accent dark:text-accent px-5 py-2.5 rounded-xl font-bold text-xs">
            Kontak
          </a>
        </div>
      </div>

      <!-- Promo Card -->
      <div class="w-full md:w-2/5 z-10">
        <div class="glass-card p-3.5 rounded-xl flex items-center gap-3 shadow-md cursor-pointer" onclick="tambahKeKeranjang('Kopi Susu Nyantuy', 18000)">
          <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=150&q=80" alt="Promo" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
          <div class="flex-1">
            <span class="text-[9px] text-primary dark:text-accent font-black uppercase tracking-wider block mb-0.5">⭐ FAVORIT</span>
            <h4 class="font-heading font-bold text-sm leading-snug">Kopi Susu Nyantuy</h4>
            <p class="text-[11px] text-textMuted dark:text-gray-400 mb-1">Manis, creamy, nikmat.</p>
            <div class="flex items-center justify-between">
              <span class="text-primary dark:text-accent font-black text-sm">Rp18.000</span>
              <span class="text-[10px] bg-primary text-white px-2.5 py-1 rounded-md font-bold">+ Pesan</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- WIDGET QUICK INFO -->
    <section class="reveal grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="glass-card p-4 rounded-xl flex items-center gap-3 border-l-4 border-l-amber-500">
        <div class="text-2xl bg-amber-500/10 p-2.5 rounded-xl text-amber-500">🔥</div>
        <div>
          <span class="text-[9px] font-black text-gray-400 uppercase">PROMO HARI INI</span>
          <h4 class="font-heading font-bold text-sm">Beli 2 Kopi Susu</h4>
          <p class="text-[11px] text-textMuted dark:text-gray-400">Dua cup hanya Rp30.000</p>
        </div>
      </div>
      <div class="glass-card p-4 rounded-xl flex items-center gap-3 border-l-4 border-l-emerald-500">
        <div class="text-2xl bg-emerald-500/10 p-2.5 rounded-xl text-emerald-500">🕒</div>
        <div>
          <span class="text-[9px] font-black text-gray-400 uppercase">JAM OPERASIONAL</span>
          <h4 class="font-heading font-bold text-sm">Setiap Hari</h4>
          <p class="text-[11px] text-textMuted dark:text-gray-400">08.00 - 22.00 WIB</p>
        </div>
      </div>
    </section>

    <!-- TENTANG KAMI -->
    <section id="tentang" class="reveal flex flex-col md:flex-row items-center gap-6 py-2">
      <div class="w-full md:w-1/2">
        <span class="text-[10px] font-black text-primary dark:text-accent uppercase tracking-widest block mb-1">TENTANG KAMI</span>
        <h2 class="font-heading text-xl sm:text-2xl font-extrabold mb-3">Kenalan dengan KopiNyantuy</h2>
        <p class="text-textMuted dark:text-gray-300 text-xs leading-relaxed mb-3">
          KopiNyantuy hadir sebagai tempat nongkrong yang nyaman untuk semua kalangan. Kami menyajikan berbagai pilihan kopi, minuman non-kopi, dan makanan ringan dengan harga ramah di kantong.
        </p>
      </div>
      <div class="w-full md:w-1/2">
        <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80" alt="Cafe" class="w-full h-48 object-cover rounded-xl shadow-md">
      </div>
    </section>

    <!-- LAYOUT MENU UTAMA -->
    <div class="flex flex-col lg:flex-row gap-6" id="menuGrid">
      
      <!-- SIDEBAR KATEGORI -->
      <aside class="w-full lg:w-1/4">
        <div class="glass-card p-4 rounded-xl sticky top-20 z-20">
          <h3 class="font-heading text-sm font-extrabold mb-3">Kategori Menu</h3>
          <ul class="flex lg:flex-col gap-2 overflow-x-auto pb-1 no-scrollbar" id="categoryList">
            <li onclick="filterKategori('semua', this)" class="category-btn whitespace-nowrap bg-primary text-white font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0">
              ☕ Semua
            </li>
            <li onclick="filterKategori('kopi', this)" class="category-btn whitespace-nowrap bg-gray-100 dark:bg-gray-800 text-textMuted dark:text-gray-300 font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0">
              ☕ Kopi
            </li>
            <li onclick="filterKategori('non-kopi', this)" class="category-btn whitespace-nowrap bg-gray-100 dark:bg-gray-800 text-textMuted dark:text-gray-300 font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0">
              🍵 Non-Kopi
            </li>
            <li onclick="filterKategori('snack', this)" class="category-btn whitespace-nowrap bg-gray-100 dark:bg-gray-800 text-textMuted dark:text-gray-300 font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0">
              🥐 Snack
            </li>
          </ul>
        </div>
      </aside>

      <!-- DAFTAR MENU CARDS -->
      <section class="w-full lg:w-3/4 flex flex-col gap-3">
        <!-- KOPI -->
        <article class="reveal menu-list-card glass-card rounded-xl p-3 flex items-center gap-3.5" data-name="Americano" data-category="kopi">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQmkkXsrVqRu40WBZ62zmAwcmm_LGw8fjF5EKbOlhG3JQ&s=10" class="w-20 h-20 rounded-lg object-cover flex-shrink-0 cursor-pointer" onclick="bukaQuickView('Americano', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQmkkXsrVqRu40WBZ62zmAwcmm_LGw8fjF5EKbOlhG3JQ&s=10', 'Kopi hitam dengan rasa kuat dan menyegarkan.')">
          <div class="flex-1 min-w-0">
            <h3 class="font-heading font-bold text-sm truncate">Americano</h3>
            <p class="text-[11px] text-textMuted dark:text-gray-400 line-clamp-1 mb-1">Kopi hitam rasa kuat dan menyegarkan.</p>
            <div class="flex items-center justify-between mt-1">
              <span class="text-primary dark:text-accent font-extrabold text-sm">Rp10.000</span>
              <button onclick="tambahKeKeranjang('Americano', 10000)" class="bg-primary hover:bg-primaryDark text-white px-3 py-1 rounded-md font-bold text-xs">+ Pesan</button>
            </div>
          </div>
        </article>

        <article class="reveal menu-list-card glass-card rounded-xl p-3 flex items-center gap-3.5 border border-primary/30" data-name="Kopi Susu Nyantuy" data-category="kopi">
          <img src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=200&q=80" class="w-20 h-20 rounded-lg object-cover flex-shrink-0 cursor-pointer" onclick="bukaQuickView('Kopi Susu Nyantuy', 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=400&q=80', 'Kopi susu creamy dengan rasa manis yang pas.')">
          <div class="flex-1 min-w-0">
            <h3 class="font-heading font-bold text-sm truncate flex items-center gap-1">Kopi Susu Nyantuy ⭐</h3>
            <p class="text-[11px] text-textMuted dark:text-gray-400 line-clamp-1 mb-1">Kopi susu creamy rasa manis pas.</p>
            <div class="flex items-center justify-between mt-1">
              <span class="text-primary dark:text-accent font-extrabold text-sm">Rp18.000</span>
              <button onclick="tambahKeKeranjang('Kopi Susu Nyantuy', 18000)" class="bg-primary hover:bg-primaryDark text-white px-3 py-1 rounded-md font-bold text-xs">+ Pesan</button>
            </div>
          </div>
        </article>

        <!-- NON KOPI -->
        <article class="reveal menu-list-card glass-card rounded-xl p-3 flex items-center gap-3.5" data-name="Matcha Latte" data-category="non-kopi">
          <img src="https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=200&q=80" class="w-20 h-20 rounded-lg object-cover flex-shrink-0 cursor-pointer" onclick="bukaQuickView('Matcha Latte', 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=400&q=80', 'Matcha lembut dengan rasa manis yang seimbang.')">
          <div class="flex-1 min-w-0">
            <h3 class="font-heading font-bold text-sm truncate">Matcha Latte</h3>
            <p class="text-[11px] text-textMuted dark:text-gray-400 line-clamp-1 mb-1">Matcha lembut manis seimbang.</p>
            <div class="flex items-center justify-between mt-1">
              <span class="text-primary dark:text-accent font-extrabold text-sm">Rp20.000</span>
              <button onclick="tambahKeKeranjang('Matcha Latte', 20000)" class="bg-primary hover:bg-primaryDark text-white px-3 py-1 rounded-md font-bold text-xs">+ Pesan</button>
            </div>
          </div>
        </article>

        <!-- SNACK -->
        <article class="reveal menu-list-card glass-card rounded-xl p-3 flex items-center gap-3.5" data-name="French Fries" data-category="snack">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ6JZcOy2ayNqRZRoWOihdJd805djPrL9-qoD1tbf4Sh-L-wWaicjwIHFY&s=10" class="w-20 h-20 rounded-lg object-cover flex-shrink-0 cursor-pointer" onclick="bukaQuickView('French Fries', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ6JZcOy2ayNqRZRoWOihdJd805djPrL9-qoD1tbf4Sh-L-wWaicjwIHFY&s=10', 'Kentang goreng renyah dan gurih.')">
          <div class="flex-1 min-w-0">
            <h3 class="font-heading font-bold text-sm truncate">French Fries</h3>
            <p class="text-[11px] text-textMuted dark:text-gray-400 line-clamp-1 mb-1">Kentang goreng renyah gurih.</p>
            <div class="flex items-center justify-between mt-1">
              <span class="text-primary dark:text-accent font-extrabold text-sm">Rp15.000</span>
              <button onclick="tambahKeKeranjang('French Fries', 15000)" class="bg-primary hover:bg-primaryDark text-white px-3 py-1 rounded-md font-bold text-xs">+ Pesan</button>
            </div>
          </div>
        </article>

        <p id="pesanKosong" class="hidden text-center py-8 text-gray-400 font-bold text-xs">🔍 Menu tidak ditemukan.</p>
      </section>
    </div>

    <!-- KONTAK SECTION -->
    <section id="kontak" class="reveal py-4">
      <span class="text-[10px] font-black text-primary dark:text-accent uppercase tracking-widest block mb-1">INFORMASI</span>
      <h2 class="font-heading text-xl font-extrabold mb-4">Kontak KopiNyantuy</h2>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
        <div class="glass-card p-3.5 rounded-xl flex items-center gap-3">
          <div class="text-lg bg-red-500/10 p-2 rounded-lg text-red-500">📍</div>
          <div>
            <p class="font-bold text-[10px] text-gray-400 uppercase">Alamat</p>
            <p class="font-bold">Jl. Nyantuy No. 10</p>
          </div>
        </div>
        <div class="glass-card p-3.5 rounded-xl flex items-center gap-3">
          <div class="text-lg bg-emerald-500/10 p-2 rounded-lg text-emerald-500">📞</div>
          <div>
            <p class="font-bold text-[10px] text-gray-400 uppercase">Telepon</p>
            <p class="font-bold">0895-3200-78768</p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer class="bg-primaryDark text-white py-6 border-t border-white/10 mt-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-xs text-gray-400">
      <p class="font-heading font-black text-white text-sm mb-1">☕ KopiNyantuy</p>
      <p>© 2026 KopiNyantuy. All Rights Reserved.</p>
    </div>
  </footer>

  <!-- STICKY CART BAR FLOATING -->
  <div id="stickyCart" class="fixed bottom-0 left-0 right-0 bg-primaryDark/95 backdrop-blur-xl text-white py-3 px-4 z-[8000] flex justify-between items-center transform translate-y-full transition-transform duration-300 border-t border-white/10 shadow-2xl">
    <div class="flex items-center gap-3">
      <div class="relative bg-primary p-2.5 rounded-full text-lg">
        🛒 <span id="cartCount" class="absolute -top-1 -right-1 bg-accent text-primaryDark font-black text-[10px] w-4 h-4 rounded-full flex items-center justify-center">0</span>
      </div>
      <div>
        <p class="text-[9px] text-gray-400 font-bold uppercase">Total</p>
        <p id="cartTotalHeader" class="text-base font-black text-accent">Rp0</p>
      </div>
    </div>
    <button onclick="bukaModalPembayaran()" class="bg-accent text-primaryDark font-black px-5 py-2.5 rounded-xl text-xs shadow-md hover:bg-white transition">
      Bayar →
    </button>
  </div>

  <!-- PRODUCT QUICK VIEW MODAL -->
  <div id="quickViewModal" class="fixed inset-0 bg-black/70 z-[9999] hidden justify-center items-center p-4 backdrop-blur-sm opacity-0 transition-all duration-300">
    <div class="bg-white dark:bg-gray-800 w-full max-w-xs rounded-2xl overflow-hidden relative shadow-2xl" id="qvContent">
      <button onclick="tutupQuickView()" class="absolute top-3 right-3 bg-black/50 text-white w-7 h-7 rounded-full font-bold text-xs z-10">✕</button>
      <img id="qvImage" src="" class="w-full h-48 object-cover">
      <div class="p-4 text-center">
        <h3 id="qvTitle" class="font-heading font-bold text-base mb-1 text-gray-900 dark:text-gray-100">Title</h3>
        <p id="qvDesc" class="text-xs text-textMuted dark:text-gray-400 leading-relaxed mb-3">Desc</p>
        <button onclick="tutupQuickView()" class="w-full bg-primary text-white py-2 rounded-lg font-bold text-xs">Tutup</button>
      </div>
    </div>
  </div>

  <!-- MODAL PEMBAYARAN -->
  <div id="modalBayar" class="fixed inset-0 bg-black/70 z-[9999] hidden justify-center items-center p-4 backdrop-blur-md">
    <div class="bg-white dark:bg-gray-800 w-full max-w-sm p-5 rounded-2xl relative shadow-2xl max-h-[90vh] overflow-y-auto">
      <button onclick="tutupModalPembayaran()" class="absolute top-3 right-3 text-gray-400 text-lg font-bold">✕</button>
      <h2 class="font-heading text-xl font-black mb-1">Rincian Pesanan</h2>

      <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50 dark:bg-gray-700/50 mb-4 mt-3">
        <div id="daftarPesananModal" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>
        <div class="border-t border-dashed border-gray-300 dark:border-gray-600 mt-3 pt-2 flex justify-between items-center font-black text-sm">
          <span>TOTAL:</span>
          <span id="totalAkhirModal" class="text-primary dark:text-accent">Rp0</span>
        </div>
      </div>

      <form id="formCheckout" onsubmit="prosesBayar(event)" class="space-y-3">
        <div>
          <label class="block text-[11px] font-bold mb-1">Nomor Meja / Lokasi</label>
          
          <!-- 3. KUNCI INPUT JIKA DARI QR CODE -->
          <?php if($nomor_meja): ?>
            <input type="text" id="inputMeja" value="Meja <?= $nomor_meja ?>" readonly class="w-full p-2.5 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-bold focus:outline-none cursor-not-allowed">
            <p class="text-[9px] text-emerald-600 mt-1 font-bold">✓ Nomor meja otomatis terdeteksi dari QR Code.</p>
          <?php else: ?>
            <select id="inputMeja" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs focus:outline-none">
              <option value="">-- Pilih Lokasi --</option>
              <option value="Meja 1">Meja 1</option>
              <option value="Meja 2">Meja 2</option>
              <option value="Meja 3">Meja 3</option>
              <option value="Bungkus / Takeaway">Bungkus / Takeaway</option>
            </select>
          <?php endif; ?>

        </div>
        <div>
          <label class="block text-[11px] font-bold mb-1">Metode Pembayaran</label>
          <select id="inputBayar" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs focus:outline-none">
            <option value="">-- Pilih Pembayaran --</option>
            <option value="Tunai">Bayar di Kasir (Tunai)</option>
            <option value="QRIS">QRIS / E-Wallet</option>
          </select>
        </div>
        <button type="submit" id="btnSubmitCheckout" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition shadow-md mt-2 text-xs">
          Kirim Pesanan & Buat Struk →
        </button>
      </form>
    </div>
  </div>

  <!-- MODAL STRUK PEMBAYARAN DIGITAL (INVOICE) -->
  <div id="modalStruk" class="fixed inset-0 bg-black/80 z-[10000] hidden justify-center items-center p-4 backdrop-blur-md">
    <div class="bg-white text-gray-800 w-full max-w-sm p-6 rounded-2xl relative shadow-2xl border border-gray-100">
      
      <!-- Header Struk -->
      <div class="text-center border-b border-dashed border-gray-300 pb-4 mb-4">
        <h2 class="font-heading font-black text-xl text-emerald-900 tracking-wider">☕ KopiNyantuy</h2>
        <p class="text-[10px] text-gray-500">Jl. Nyantuy No. 10 | Telp: 0895-3200-78768</p>
        <div class="mt-3 bg-emerald-50 text-emerald-800 py-1 rounded-full text-[10px] font-bold inline-block px-4">
          ✓ PESANAN BERHASIL DISIMPAN
        </div>
      </div>

      <!-- Detail Info Transaksi -->
      <div class="text-xs space-y-1 mb-4 border-b border-dashed border-gray-300 pb-3">
        <div class="flex justify-between"><span class="text-gray-500">No. Order:</span><span id="strukId" class="font-bold">ORD-0000</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Waktu:</span><span id="strukWaktu" class="font-medium">-</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Lokasi / Meja:</span><span id="strukMeja" class="font-bold">-</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Metode Bayar:</span><span id="strukBayar" class="font-bold">-</span></div>
      </div>

      <!-- Item Pesanan -->
      <div class="mb-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Rincian Menu:</p>
        <div id="strukItems" class="space-y-1.5 text-xs max-h-36 overflow-y-auto"></div>
      </div>

      <!-- Total Harga -->
      <div class="border-t border-b border-gray-800 py-2.5 mb-5 flex justify-between items-center font-black text-sm">
        <span>TOTAL BAYAR</span>
        <span id="strukTotal" class="text-emerald-700 text-base">Rp0</span>
      </div>

      <!-- Tombol Aksi Struk -->
      <div class="flex gap-2 no-print">
        <button onclick="window.print()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 rounded-xl font-bold text-xs transition">
          🖨️ Cetak Struk
        </button>
        <button onclick="tutupStruk()" class="flex-1 bg-emerald-800 hover:bg-emerald-900 text-white py-2.5 rounded-xl font-bold text-xs transition">
          Selesai
        </button>
      </div>

    </div>
  </div>

  <!-- JAVASCRIPT LOGIC -->
  <script>
    // 1. PRELOADER
    window.onload = () => {
      document.getElementById('htmlRoot').classList.remove('opacity-0');
      setTimeout(() => {
        document.getElementById('preloader').classList.add('opacity-0');
        setTimeout(() => document.getElementById('preloader').style.display = 'none', 500);
      }, 300);
    };

    // 2. REVEAL ANIMATION ON SCROLL
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('active'); });
    }, { threshold: 0.05 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // 3. SCROLL PROGRESS
    window.onscroll = () => {
      let winScroll = document.documentElement.scrollTop;
      let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      document.getElementById("scroll-progress").style.width = (winScroll / height) * 100 + "%";
    };

    // 4. QUICK VIEW MODAL
    function bukaQuickView(title, imgUrl, desc) {
      const modal = document.getElementById('quickViewModal');
      document.getElementById('qvTitle').innerText = title;
      document.getElementById('qvImage').src = imgUrl;
      document.getElementById('qvDesc').innerText = desc;
      modal.style.display = 'flex';
      setTimeout(() => { modal.classList.remove('opacity-0'); }, 10);
    }
    function tutupQuickView() {
      const modal = document.getElementById('quickViewModal');
      modal.classList.add('opacity-0');
      setTimeout(() => modal.style.display = 'none', 300);
    }

    // 5. DARK MODE TOGGLE
    const themeBtn = document.getElementById('theme-btn');
    const themeIcon = document.getElementById('theme-icon');
    const themeText = document.getElementById('theme-text');

    themeBtn.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      themeIcon.innerText = isDark ? '☀️' : '🌙';
      themeText.innerText = isDark ? 'Light' : 'Dark';
    });

    // 6. TOAST NOTIFICATION
    function showToast(pesan) {
      const toast = document.getElementById("toast");
      if(pesan) document.getElementById("toastMsg").innerText = pesan;
      toast.style.bottom = "80px";
      setTimeout(() => { toast.style.bottom = "-80px"; }, 2500);
    }

    const formatRp = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    // 7. FILTER KATEGORI MENU
    let katAktif = 'semua';
    function filterKategori(kat, el) {
      katAktif = kat;
      document.querySelectorAll('.category-btn').forEach(btn => {
        btn.className = "category-btn whitespace-nowrap bg-gray-100 dark:bg-gray-800 text-textMuted dark:text-gray-300 font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0";
      });
      el.className = "category-btn whitespace-nowrap bg-primary text-white font-bold px-4 py-2 rounded-lg cursor-pointer text-xs flex items-center gap-1.5 flex-shrink-0";
      terapkanFilter();
    }

    function terapkanFilter() {
      const keyword = document.getElementById('menuSearch').value.toLowerCase();
      let ada = false;
      document.querySelectorAll('.menu-list-card').forEach(card => {
        const cocokNama = card.dataset.name.toLowerCase().includes(keyword);
        const cocokKat = (katAktif === 'semua') || (card.dataset.category === katAktif);
        if (cocokNama && cocokKat) { card.style.display = 'flex'; ada = true; } 
        else { card.style.display = 'none'; }
      });
      document.getElementById('pesanKosong').classList.toggle('hidden', ada);
    }
    document.getElementById('menuSearch').addEventListener('input', terapkanFilter);

    // 8. SYSTEM KERANJANG
    let keranjang = [];

    function tambahKeKeranjang(nama, harga) {
      const item = keranjang.find(i => i.nama === nama);
      if (item) { item.qty++; } else { keranjang.push({ nama, harga, qty: 1 }); }
      updateCart();
      showToast(`${nama} ditambahkan!`);
    }

    function ubahQty(nama, delta) {
      const item = keranjang.find(i => i.nama === nama);
      if (item) { 
        item.qty += delta; 
        if (item.qty <= 0) keranjang = keranjang.filter(i => i.nama !== nama); 
      }
      updateCart();
      renderModal();
    }

    function hapusItemKeranjang(nama) {
      keranjang = keranjang.filter(i => i.nama !== nama);
      updateCart();
      renderModal();
      showToast(`${nama} dihapus dari keranjang`);
      if (keranjang.length === 0) {
        tutupModalPembayaran();
      }
    }

    function updateCart() {
      const totalItem = keranjang.reduce((s, i) => s + i.qty, 0);
      const totalHarga = keranjang.reduce((s, i) => s + (i.harga * i.qty), 0);
      document.getElementById('cartCount').innerText = totalItem;
      document.getElementById('cartTotalHeader').innerText = formatRp(totalHarga);
      document.getElementById('stickyCart').classList.toggle('translate-y-full', totalItem === 0);
    }

    function renderModal() {
      const box = document.getElementById('daftarPesananModal'); 
      box.innerHTML = '';
      let sum = 0;
      if (keranjang.length === 0) {
        box.innerHTML = `<p class="text-center text-gray-400 py-3 text-xs font-bold">Keranjang kosong.</p>`;
        document.getElementById('totalAkhirModal').innerText = formatRp(0);
        return;
      }
      keranjang.forEach(i => {
        const sub = i.harga * i.qty; 
        sum += sub;
        box.innerHTML += `
          <div class="flex justify-between items-center text-xs border-b border-gray-100 dark:border-gray-600 pb-2 pt-1">
            <div class="flex-1 pr-2">
              <p class="font-bold text-[11px] leading-tight">${i.nama}</p>
              <p class="text-[10px] text-gray-400">${formatRp(i.harga)} x ${i.qty}</p>
            </div>
            <div class="flex items-center gap-2">
              <div class="flex items-center border rounded-lg bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <button type="button" onclick="ubahQty('${i.nama}', -1)" class="px-2 py-0.5 font-bold text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">-</button>
                <span class="px-2 font-bold text-[11px]">${i.qty}</span>
                <button type="button" onclick="ubahQty('${i.nama}', 1)" class="px-2 py-0.5 font-bold text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">+</button>
              </div>
              <span class="font-bold text-primary dark:text-accent text-[11px] min-w-[60px] text-right">${formatRp(sub)}</span>
              <button type="button" onclick="hapusItemKeranjang('${i.nama}')" class="text-red-500 p-1 text-xs" title="Hapus">🗑️</button>
            </div>
          </div>`;
      });
      document.getElementById('totalAkhirModal').innerText = formatRp(sum);
    }

    function bukaModalPembayaran() { if(keranjang.length > 0) { renderModal(); document.getElementById('modalBayar').style.display = 'flex'; } }
    function tutupModalPembayaran() { document.getElementById('modalBayar').style.display = 'none'; }
    function tutupStruk() { document.getElementById('modalStruk').style.display = 'none'; }

    async function prosesBayar(e) {
      e.preventDefault();
      if (keranjang.length === 0) return;

      const btnSubmit = document.getElementById('btnSubmitCheckout');
      btnSubmit.innerText = "Menyimpan Pesanan...";
      btnSubmit.disabled = true;

      const meja = document.getElementById('inputMeja').value;
      const bayar = document.getElementById('inputBayar').value;
      const total = keranjang.reduce((s, i) => s + (i.harga * i.qty), 0);
      const id = "ORD-" + Math.floor(Math.random() * 9000 + 1000);
      const rincian = keranjang.map(i => `• ${i.nama} x${i.qty} = ${formatRp(i.harga * i.qty)}`).join("\n");

      try {
        await fetch('simpan_pesanan.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id, meja, bayar, rincian, total })
        });

        document.getElementById('strukId').innerText = id;
        document.getElementById('strukWaktu').innerText = new Date().toLocaleString('id-ID');
        document.getElementById('strukMeja').innerText = meja;
        document.getElementById('strukBayar').innerText = bayar;
        document.getElementById('strukTotal').innerText = formatRp(total);

        const boxStrukItems = document.getElementById('strukItems');
        boxStrukItems.innerHTML = '';
        keranjang.forEach(item => {
          boxStrukItems.innerHTML += `
            <div class="flex justify-between items-center text-xs border-b border-gray-100 pb-1">
              <div><span class="font-bold">${item.nama}</span> <span class="text-gray-400 text-[10px]">x${item.qty}</span></div>
              <span class="font-medium">${formatRp(item.harga * item.qty)}</span>
            </div>
          `;
        });

        keranjang = [];
        updateCart();
        tutupModalPembayaran();
        document.getElementById('formCheckout').reset();
        document.getElementById('modalStruk').style.display = 'flex';

      } catch (err) {
        alert("Gagal menyimpan ke database! Periksa koneksi server Laragon.");
      } finally {
        btnSubmit.innerText = "Kirim Pesanan & Buat Struk →";
        btnSubmit.disabled = false;
      }
    }
  </script>
</body>
</html>