<?php
// Koneksi ke Database Laragon
$conn = new mysqli("localhost", "root", "", "db_kopinyantuy");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Update Status Pesanan
if (isset($_POST['update_status'])) {
    $id = $_POST['order_id'];
    $status_baru = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// Ambil Statistik
$total_pesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan")->fetch_assoc()['total'];
$total_pendapatan = $conn->query("SELECT SUM(total_harga) as total FROM pesanan WHERE status = 'Selesai'")->fetch_assoc()['total'] ?? 0;
$pesanan_hari_ini = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'];

// Ambil Data Pesanan
$search = $_GET['search'] ?? '';
if ($search != '') {
    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE kode_order LIKE ? OR nomor_meja LIKE ? ORDER BY id DESC");
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM pesanan ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth" id="htmlRoot">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KopiNyantuy | Dashboard Admin & Kasir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#3d5a32',
            primaryDark: '#23331b',
            accent: '#a7d970',
            bgLight: '#f6f8f4',
            textMain: '#1a2318'
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
      border: 1px solid rgba(255, 255, 255, 0.6);
    }
    .dark .glass-card {
      background: rgba(24, 31, 22, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
</head>
<body class="bg-bgLight dark:bg-[#111810] text-textMain dark:text-gray-100 font-sans transition-colors duration-300 min-h-screen">

  <!-- NAVBAR ADMIN -->
  <header class="sticky top-0 z-50 px-4 sm:px-8 py-3.5 bg-primaryDark text-white shadow-lg">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <div class="flex items-center gap-3">
        <span class="text-2xl">☕</span>
        <div>
          <h1 class="font-heading font-black text-lg sm:text-xl">Dashboard Kasir</h1>
          <p class="text-[10px] text-accent">KopiNyantuy Management System</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="index.php" target="_blank" class="bg-primary hover:bg-primaryLight text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
          🌐 Lihat Website Menu
        </a>
        <button id="theme-btn" class="bg-white/10 hover:bg-white/20 p-2 rounded-lg text-sm">🌙</button>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-8 py-6 flex flex-col gap-6">

    <!-- STATISTIK KARTU -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="glass-card p-5 rounded-2xl border-l-4 border-l-blue-500">
        <p class="text-[10px] font-bold text-gray-400 uppercase">Total Pesanan</p>
        <h3 class="font-heading text-2xl font-black mt-1"><?= number_format($total_pesanan); ?></h3>
        <p class="text-[11px] text-gray-500 mt-0.5">Keseluruhan order masuk</p>
      </div>
      <div class="glass-card p-5 rounded-2xl border-l-4 border-l-emerald-500">
        <p class="text-[10px] font-bold text-gray-400 uppercase">Pesanan Hari Ini</p>
        <h3 class="font-heading text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1"><?= number_format($pesanan_hari_ini); ?></h3>
        <p class="text-[11px] text-gray-500 mt-0.5">Order tanggal <?= date('d M Y'); ?></p>
      </div>
      <div class="glass-card p-5 rounded-2xl border-l-4 border-l-amber-500">
        <p class="text-[10px] font-bold text-gray-400 uppercase">Total Pendapatan (Selesai)</p>
        <h3 class="font-heading text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">Rp<?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
        <p class="text-[11px] text-gray-500 mt-0.5">Dari transaksi yang selesai</p>
      </div>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div class="glass-card p-4 rounded-2xl flex flex-col sm:flex-row justify-between items-center gap-4">
      <h2 class="font-heading font-extrabold text-lg">Daftar Transaksi Masuk</h2>
      <form method="GET" class="w-full sm:w-auto flex items-center gap-2">
        <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="🔍 Cari ID / Nomor Meja..." class="w-full sm:w-64 px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:outline-none">
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold">Cari</button>
        <?php if ($search != ''): ?>
          <a href="admin.php" class="bg-gray-200 dark:bg-gray-700 text-xs px-3 py-2 rounded-xl font-bold">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- TABEL TRANSAKSI -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-primaryDark text-white uppercase font-bold text-[10px] tracking-wider">
            <tr>
              <th class="p-4">ID Order</th>
              <th class="p-4">Waktu</th>
              <th class="p-4">Lokasi / Meja</th>
              <th class="p-4">Rincian Pesanan</th>
              <th class="p-4">Total</th>
              <th class="p-4">Pembayaran</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                  <td class="p-4 font-extrabold text-primary dark:text-accent"><?= $row['kode_order']; ?></td>
                  <td class="p-4 text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                  <td class="p-4 font-bold whitespace-nowrap"><?= htmlspecialchars($row['nomor_meja']); ?></td>
                  <td class="p-4 whitespace-pre-line leading-relaxed"><?= htmlspecialchars($row['rincian_menu']); ?></td>
                  <td class="p-4 font-extrabold whitespace-nowrap">Rp<?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                  <td class="p-4"><span class="bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md font-bold text-[10px]"><?= $row['metode_bayar']; ?></span></td>
                  <td class="p-4">
                    <?php
                    $badge = [
                      'Pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                      'Diproses' => 'bg-blue-100 text-blue-800 border-blue-300',
                      'Selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                      'Dibatalkan' => 'bg-red-100 text-red-800 border-red-300'
                    ];
                    ?>
                    <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold <?= $badge[$row['status']]; ?>">
                      <?= $row['status']; ?>
                    </span>
                  </td>
                  <td class="p-4">
                    <form method="POST" class="flex items-center justify-center gap-1">
                      <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                      <select name="status" onchange="this.form.submit()" class="p-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-[11px] font-bold focus:outline-none">
                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Diproses" <?= $row['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= $row['status'] == 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                      </select>
                      <input type="hidden" name="update_status" value="1">
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="p-8 text-center text-gray-400 font-bold">Belum ada data transaksi pesanan.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <script>
    const themeBtn = document.getElementById('theme-btn');
    themeBtn.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      themeBtn.innerText = isDark ? '☀️' : '🌙';
    });
  </script>
</body>
</html>